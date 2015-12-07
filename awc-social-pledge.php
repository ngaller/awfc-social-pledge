<?php
/**
 * Plugin Name: AWC Social Pledge
 * Description: Define pledges, adds a Pledge button to Visual Composer, and enable sharing of those on social networks
 * Version: 1.0
 * Author: F1 Code
 * Author URI: http://f1code.com/
 * License: ISC
 *
 * Copyright (C) 2015 F1 Code
 */

spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    $prefix = 'AWC\\SocialPledge\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});


define( 'AWC_SOCIAL_PLEDGE_PLUGIN', __FILE__ );
define( 'AWC_SOCIAL_PLEDGE_PLUGIN_BASENAME', plugin_basename( AWC_SOCIAL_PLEDGE_PLUGIN ) );

add_action('init', [new \AWC\SocialPledge\Init(), 'initialize']);
add_action('wp_loaded', [new \AWC\SocialPledge\Init(), 'onWpLoaded']);
