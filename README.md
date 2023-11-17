# wp-depends

Mark your WordPress action / filter hooks with semver plugin version constraints. Reduce the chance of bugs making it out into the wild by throwing errors in development when plugin dependencies don't match up.

Although we now have composer based plugin dependency management via Bedrock, it can sometimes be hard to keep track of action / filter code. Was that filter written for Woocommerce 6 or 8? Rather than rely on documentation / comments, we can now mark our code with attributes to lock filters to specific plugin versions.

Check it out:

```php
add_action('wp_head', #[DependsOnPlugin('woocommerce', '^6.2')] function() {
        // This code should work with Woocommerce ^6.2.
        // Throw an error if we aren't running the right version.
});
```

## Getting started

This isn't packaged yet. Include it in your `composer.json` directly from GitHub:

```
"repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/talss89/wp-depends.git"
    }
],
"require": {
    "talss89/wp-depends": "dev-main"
}
```

Then, early on (in your `functions.php` file or similar), enforce the version constraints.

```php
use WpDepends\Enforce;

Enforce::init();
```

By default, you must have `WP_DEBUG` set in order for errors to be thrown when version constraints can't be fulfilled. In production, when `WP_DEBUG` isn't set, this package will do nothing. Which is probably what you want. *If it isn't, you can force-enforce by passing true to `Enforce::init()`.*

### Applying version constraints to actions and filters

To apply a version constraint, simply mark your handler with the `DependsOnPlugin` attribute. Pass in the plugin name as the first argument, and the semver version constraint as the second:

```php
use WpDepends\Attributes\DependsOnPlugin;

add_action('wp_head', #[DependsOnPlugin('woocommerce', '^6.2')] function() {
        // This code should work with Woocommerce ^6.2.
        // Throw an error if we aren't running the right version.
});
```

You can also annotate standalone functions:

```php
use WpDepends\Attributes\DependsOnPlugin;

#[DependsOnPlugin('woocommerce', '^6.2')]
function my_woocommerce_action_for_62() {
    // Do things.
}

add_action('init', 'my_woocommerce_action_for_62');
```

Specify multiple dependencies in one block:

```php
use WpDepends\Attributes\DependsOnPlugin;

#[
    DependsOnPlugin('woocommerce', '^6.2'),
    DependsOnPlugin('otherplugin', '^4')
]
function my_woocommerce_and_otherplugin_action() {
    // Do things.
}

add_action('init', 'my_woocommerce_and_otherplugin_action');
```

## Status

This is an early prototype, proof of concept.
