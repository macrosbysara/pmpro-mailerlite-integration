<?php
/**
 * Plugin Settings management for PMPro MailerLite Integration.
 *
 * Provides a single structured WordPress option that stores the MailerLite
 * API key and the target group ID for subscriber management.
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

namespace MacrosBySara\PMProMailerLite\WP;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages plugin settings stored as a single WordPress option.
 */
class Plugin_Settings {
	/**
	 * The WordPress option key used to store all plugin settings.
	 *
	 * @var string
	 */
	public const OPTION_KEY = 'mbs_options';

	/**
	 * The setting field keys stored in the option.
	 *
	 * @var array
	 */
	public const SETTING_FIELDS = array(
		'apiKey',
		'groupId',
		'groupName',
	);

	public const REST_NAMESPACE = 'mbs/pmpro-mailerlite/v1';

	/**
	 * Returns the default settings structure.
	 *
	 * @return array
	 */
	public function get_defaults(): array {
		return array_fill_keys( self::SETTING_FIELDS, '' );
	}

	/**
	 * Initialize the default option on plugin activation if it does not already exist.
	 *
	 * @return void
	 */
	public function initialize_defaults(): void {
		if ( false === get_option( self::OPTION_KEY ) ) {
			add_option( self::OPTION_KEY, $this->get_defaults() );
		}
	}

	/**
	 * Register the setting with WordPress so it can be read and validated.
	 *
	 * @return void
	 */
	public function register(): void {
		register_setting(
			self::OPTION_KEY . '_group',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize' ),
				'default'           => $this->get_defaults(),
			)
		);
	}

	/**
	 * Return the current settings, with defaults applied for any missing keys.
	 *
	 * @return array
	 */
	public function get_settings(): array {
		$saved = get_option( self::OPTION_KEY, $this->get_defaults() );
		return $this->merge_with_defaults( $saved );
	}

	/**
	 * Return the WordPress option key.
	 *
	 * @return string
	 */
	public function get_option_key(): string {
		return self::OPTION_KEY;
	}

	/**
	 * Sanitize the settings array before it is stored.
	 *
	 * Any key not present in SETTING_FIELDS is dropped silently.
	 *
	 * @param mixed $input The raw input to sanitize.
	 * @return array The sanitized settings.
	 */
	public function sanitize( mixed $input ): array {
		$defaults  = $this->get_defaults();
		$sanitized = $defaults;

		if ( ! is_array( $input ) ) {
			return $defaults;
		}

		foreach ( self::SETTING_FIELDS as $field ) {
			if ( array_key_exists( $field, $input ) ) {
				$sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		return $sanitized;
	}

	/**
	 * Merge a saved settings array with the defaults so every expected key exists.
	 *
	 * @param mixed $settings The saved settings to merge.
	 * @return array The complete settings array.
	 */
	private function merge_with_defaults( mixed $settings ): array {
		if ( ! is_array( $settings ) ) {
			return $this->get_defaults();
		}

		return array_merge( $this->get_defaults(), $settings );
	}
}
