<?php

namespace WpDepends\Attributes;

use WpDepends\Traits\Validatable;
use WpDepends\Traits\Describable;
use WpDepends\Providers\Plugins;
use WpDepends\ValidatorResult;
use Composer\Semver\Semver;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION | \Attribute::TARGET_CLASS)]
class DependsOnPlugin extends GenericAttribute
{
    use Validatable;
    use Describable;

    public string $plugin;
    public string $constraint;

    public function __construct(string $plugin, string $constraint)
    {
        $this->plugin = $plugin;
        $this->constraint = $constraint;
    }

    public function describe(): string {
        $current_version = Plugins::get_version($this->plugin);
        
        if($current_version === false) {
            return "The plugin \"{$this->plugin}\" must satisfy the constraint \"{$this->constraint}\". The plugin is not active. Constraint set by the handler for \"{$this->hook}\".";
        }

        return "The plugin \"{$this->plugin}\" must satisfy the constraint \"{$this->constraint}\". You're running \"{$current_version}\". Constraint set by the handler for \"{$this->hook}\".";
    }

    public function validate(): ValidatorResult {

        if($this->hook === 'muplugins_loaded' || $this->hook === 'plugins_loaded') {
            throw new \ErrorException("The #[DependsOnPlugin] attribute cannot be used on \"plugins_loaded\" or \"muplugins_loaded\" hooks.");
        }

        return new ValidatorResult($this, Plugins::is_activated($this->plugin) && Semver::satisfies(Plugins::get_version($this->plugin), $this->constraint));
    }
}