<?php
/**
 * Plugin Name: PMPro MailerLite Integration
 * Plugin URI: https://github.com/macrosbysara/pmpro-mailerlite-integration
 * Description: Integrates Paid Memberships Pro with MailerLite to subscribe members to a group on checkout.
 * Version: 1.0.0
 * Author: KJ Roelke
 * Author URI: https://www.kjroelke.online
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 8.2
 * Requires at least: 6.7.0
 * Tested up to: 6.9.0
 * Requires Plugins: paid-memberships-pro
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

use MacrosBySara\PMProMailerLite\Plugin_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

$mbs_autoload_path = __DIR__ . '/vendor/autoload.php';

if ( ! file_exists( $mbs_autoload_path ) ) {
	add_action(
		'admin_notices',
		static function () {
			echo '<div class="notice notice-error"><p>PMPro MailerLite Integration is missing required dependencies. Please run Composer install or deploy the plugin with its vendor directory included.</p></div>';
		}
	);

	return;
}

require_once $mbs_autoload_path;
$mbs_plugin = new Plugin_Loader( __DIR__ );

// Plugin Lifecycle Hooks
register_activation_hook( __FILE__, array( $mbs_plugin, 'activate' ) );

// Static method for uninstall since the plugin can't rely on instance methods.
register_uninstall_hook( __FILE__, array( 'MacrosBySara\PMProMailerLite\Plugin_Loader', 'uninstall' ) );

// Load the Plugin
add_action( 'plugins_loaded', array( $mbs_plugin, 'load_plugin' ) );
