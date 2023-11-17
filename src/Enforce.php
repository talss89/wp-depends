<?php

namespace WpDepends;

class Enforce {
    private static $analyzer;
    
    public static function init(bool $force = false) {
        if((defined('WP_DEBUG') && WP_DEBUG || $force)) {
            self::$analyzer = new Analyzer();
            self::$analyzer->immediate_error = true;
            self::register_analyzer();
        }
    }

    private static function register_analyzer() {
        self::$analyzer->register();
    }

}