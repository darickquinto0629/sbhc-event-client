<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information.
 * It includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://summitbhc.com/
 * @since             1.0.0
 * @package           Event_Client
 *
 * @wordpress-plugin
 * Plugin Name:       Event Client
 * Plugin URI:        https://jollity.io
 * Description:       This plugin is a client side of the events controller. it opens endpoints so the Event controller can communicate and send events remotely.
 * Version:           1.0.0
 * Author:            Summit BHC
 * Author URI:        https://summitbhc.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       event-client
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'EVENT_CLIENT_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-event-client-activator.php
 */
function activate_event_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-client-activator.php';
	Event_Client_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-event-client-deactivator.php
 */
function deactivate_event_client() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-event-client-deactivator.php';
	Event_Client_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_event_client' );
register_deactivation_hook( __FILE__, 'deactivate_event_client' );

/**
 * The core plugin class that is used to define internationalization
 * and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-event-client.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_event_client() {

	$plugin = new Event_Client();
	$plugin->run();

}
run_event_client();
