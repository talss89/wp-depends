<?php 

namespace WpDepends\Providers;

class Plugins {

    private static $cache;

    private static function get_plugins() {
        if(!isset(self::$cache)) {
            $plugins = get_plugins();
            $mu_plugins = get_mu_plugins();

            $active = get_option('active_plugins');

            foreach($plugins as $slug => $plugin) {
                if(in_array($slug, $active)) {
                    $plugins[$slug]['Active'] = true;
                } else {
                    $plugins[$slug]['Active'] = false;
                }

                $plugins[$slug]['MU'] = false;
            }

            foreach($mu_plugins as $slug => $plugin) {
                $mu_plugins[$slug]['Active'] = true;
                $mu_plugins[$slug]['MU'] = true;
            }

            $plugins = array_merge($plugins, $mu_plugins);

            $plugin_names = array_keys($plugins);
            $plugin_names = array_map(function ($path) { return str_ireplace(".php", "", explode("/", $path)[0]); }, $plugin_names);
            self::$cache = array_combine($plugin_names, $plugins);
        }

        return self::$cache;
    }

    public static function is_installed($plugin): bool {
        $plugins = self::get_plugins();
        return in_array($plugin, array_keys($plugins));
    }

    public static function is_activated($plugin): bool {
        $plugins = self::get_plugins();
        return self::is_installed($plugin) && $plugins[$plugin]['Active'];
    }

    public static function get_version($plugin): false|string {
        $plugins = self::get_plugins();
        return (self::is_activated($plugin)) ? $plugins[$plugin]['Version'] : false;
    }
}
