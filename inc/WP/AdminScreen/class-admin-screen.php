<?php
/**
 * Admin screen and settings registration for PMPro MailerLite Integration.
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

namespace MacrosBySara\PMProMailerLite\WP\AdminScreen;

use MacrosBySara\PMProMailerLite\WP\Plugin_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the admin menu and asset enqueuing for the settings page.
 */
class Admin_Screen {
	/**
	 * The Plugin_Settings instance.
	 *
	 * @var Plugin_Settings $plugin_settings
	 */
	private readonly Plugin_Settings $plugin_settings;

	/**
	 * The absolute directory path of the plugin, used for loading assets.
	 *
	 * @var string $plugin_dir_path
	 */
	private string $plugin_dir_path;

	/**
	 * Constructor.
	 *
	 * @param Plugin_Settings $plugin_settings The plugin settings instance.
	 * @param string          $plugin_dir_path The absolute directory path of the plugin.
	 */
	public function __construct( Plugin_Settings $plugin_settings, string $plugin_dir_path ) {
		$this->plugin_settings = $plugin_settings;
		$this->plugin_dir_path = $plugin_dir_path;
	}

	/**
	 * Register the admin menu page for the plugin settings.
	 *
	 * @return void
	 */
	public function register_menus(): void {
		add_options_page(
			'PMPro MailerLite Integration',
			'PMPro MailerLite',
			'manage_options',
			'mbsml-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Enqueue admin page assets, but only on our plugin's settings page.
	 *
	 * @param string $hook_suffix The current admin page hook suffix.
	 * @return void
	 */
	public function load_required_assets( string $hook_suffix ): void {
		if ( 'settings_page_mbsml-settings' !== $hook_suffix ) {
			return;
		}

		$asset_file        = require $this->plugin_dir_path . '/build/index.asset.php';
		$plugin_assets_url = plugin_dir_url( $this->plugin_dir_path . '/pmpro-mailerlite-integration.php' );
		$asset_name        = 'mbsml-admin';

		wp_enqueue_script(
			$asset_name,
			$plugin_assets_url . 'build/index.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			array( 'strategy' => 'defer' )
		);

		wp_add_inline_script(
			$asset_name,
			'const mbsmlSettings = ' . wp_json_encode(
				array(
					'restBase' => rest_url( 'mbsml/v1' ),
					'nonce'    => wp_create_nonce( 'wp_rest' ),
				)
			),
			'before'
		);
	}

	/**
	 * Render the settings page content.
	 *
	 * @return void
	 */
	public function render_settings_page(): void {
		require_once __DIR__ . '/settings-page-render-callback.php';
	}
}
