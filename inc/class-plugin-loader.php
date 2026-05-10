<?php
/**
 * Plugin Loader
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

namespace MacrosBySara\PMProMailerLite;

use MacrosBySara\PMProMailerLite\Services\MailerLite_Service;
use MacrosBySara\PMProMailerLite\WP\AdminScreen\Admin_Screen;
use MacrosBySara\PMProMailerLite\WP\AdminScreen\Rest_Router;
use MacrosBySara\PMProMailerLite\WP\Checkout_Handler;
use MacrosBySara\PMProMailerLite\WP\Plugin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Inits the Plugin */
class Plugin_Loader {
	/**
	 * The directory path of the plugin.
	 *
	 * @var string $dir_path
	 */
	private string $dir_path;

	/**
	 * The Plugin Settings instance for managing plugin options and settings.
	 *
	 * @var Plugin_Settings $plugin_settings
	 */
	private Plugin_Settings $plugin_settings;

	/**
	 * Constructor
	 *
	 * @param string $dir_path The directory path of the plugin.
	 */
	public function __construct( string $dir_path ) {
		$this->dir_path        = $dir_path;
		$this->plugin_settings = new Plugin_Settings();
	}

	/**
	 * Initializes the Plugin on activation.
	 *
	 * @return void
	 */
	public function activate(): void {
		$this->plugin_settings->initialize_defaults();
		flush_rewrite_rules();
	}

	/**
	 * Handles Plugin Uninstallation.
	 * (this is a callback function for the `register_uninstall_hook` function)
	 *
	 * @return void
	 */
	public static function uninstall(): void {
		delete_option( Plugin_Settings::OPTION_KEY );
	}

	/**
	 * Loads the Plugin by wiring up all hooks and services.
	 *
	 * @return void
	 */
	public function load_plugin(): void {
		// Register plugin settings with WordPress.
		add_action( 'admin_init', array( $this->plugin_settings, 'register' ) );

		// Register REST API routes for the admin settings page.
		$rest_router = new Rest_Router( $this->plugin_settings );
		add_action( 'rest_api_init', array( $rest_router, 'register_routes' ) );

		// Register the admin menu and enqueue assets.
		$admin_screen = new Admin_Screen( $this->plugin_settings, $this->dir_path );
		add_action( 'admin_menu', array( $admin_screen, 'register_menus' ) );
		add_action( 'admin_enqueue_scripts', array( $admin_screen, 'load_required_assets' ) );

		// Wire up the PMPro after-checkout hook.
		$this->wire_checkout_handler();
	}

	/**
	 * Wires the pmpro_after_checkout hook to the Checkout_Handler.
	 *
	 * Separated from load_plugin to keep each concern focused and testable.
	 *
	 * @return void
	 */
	private function wire_checkout_handler(): void {
		$settings        = $this->plugin_settings->get_settings();
		$api_key         = $settings['apiKey'] ?? '';
		$mailerlite      = new MailerLite_Service( $api_key );
		$checkout_handler = new Checkout_Handler( $this->plugin_settings, $mailerlite );
		add_action( 'pmpro_after_checkout', array( $checkout_handler, 'handle_after_checkout' ), 10, 2 );
	}
}
