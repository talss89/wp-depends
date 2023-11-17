<?php

namespace WpDepends;

class Analyzer {
    const VALIDATOR_TRAIT = 'WpDepends\Traits\Validatable';

    private $results;
    public $immediate_error;
    
    function __construct() {

        $this->analyzed_hooks = [];
        $this->immediate_error = false;
    }

    public function register() {
        if($this->immediate_error) {
            ob_start();
        }
        add_action('all', [$this, 'analyze_hook']);
    }

    public function analyze_hook(string $hook, ...$args) {
        global $wp_filter;

        if(!isset($wp_filter[$hook]))
            return;

        foreach($wp_filter[$hook]->callbacks as $priority => $callbacks) {
            foreach($callbacks as $id => $callback) {
                $this->analyze_callback($hook, $id, $callback);
            }
        }
    }

    /**
     * Modified from: https://github.com/technically-php/callable-reflection
     */

    public static function reflect_callable(callable $callable): \ReflectionFunction|\ReflectionMethod
    {
        try {
            if ($callable instanceof Closure) {
                return new \ReflectionFunction($callable);
            }

            if (is_string($callable) && function_exists($callable)) {
                return new \ReflectionFunction($callable);
            }

            if (is_string($callable) && strpos($callable, '::') !== false) {
                return new \ReflectionMethod($callable);
            }

            if (is_object($callable) && method_exists($callable, '__invoke')) {
                return new \ReflectionMethod($callable, '__invoke');
            }

            if (is_array($callable)) {
                return new \ReflectionMethod($callable[0], $callable[1]);
            }
        } catch (\ReflectionException $exception) {
            $type = is_object($callable) ? get_class($callable) : gettype($callable);
            throw new \RuntimeException("Failed reflecting the given callable: `{$type}`.", 0, $exception);
        }

        $type = is_object($callable) ? get_class($callable) : gettype($callable);
        throw new \InvalidArgumentException("Cannot reflect the given callable: `{$type}`.");
    }

    private function analyze_callback($hook, $id, $callback) {

        $reflected = self::reflect_callable($callback['function']);
        $attrs = $reflected->getAttributes();

        foreach ($attrs as $attribute) {

            try {
                $validator = $attribute->newInstance();
            } catch (\Exception $e) {
                continue; // Soak up any errors if we can't create an instance
            }

            if(!in_array(self::VALIDATOR_TRAIT, class_uses($validator))) 
                continue;

            $validator->hook = $hook;

            $result = $validator->validate();
        
            $this->results[] = $result;

            if($this->immediate_error && !$result->result) {
                ob_end_clean();
                trigger_error($result->describe(), E_USER_ERROR);
            }
        }
    }
}