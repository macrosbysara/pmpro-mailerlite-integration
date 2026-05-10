<?php
/**
 * REST Controller for PMPro MailerLite Integration Settings.
 *
 * Provides GET and POST endpoints at `mbsml/v1/settings` so the React
 * admin app can read and persist plugin settings, plus a GET endpoint at
 * `mbsml/v1/groups` to fetch the available MailerLite groups.
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

namespace MacrosBySara\PMProMailerLite\WP\AdminScreen;

use MacrosBySara\PMProMailerLite\Services\MailerLite_Service;
use MacrosBySara\PMProMailerLite\WP\Plugin_Settings;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles REST API endpoints for plugin settings and MailerLite group lookup.
 */
class Rest_Router extends WP_REST_Controller {
	/**
	 * The Plugin_Settings instance.
	 *
	 * @var Plugin_Settings $plugin_settings
	 */
	private Plugin_Settings $plugin_settings;

	/**
	 * Constructor.
	 *
	 * @param Plugin_Settings $plugin_settings The settings manager instance.
	 */
	public function __construct( Plugin_Settings $plugin_settings ) {
		$this->namespace       = 'mbsml/v1';
		$this->plugin_settings = $plugin_settings;
	}

	/**
	 * Register REST API routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/groups',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_groups' ),
				'permission_callback' => array( $this, 'permissions_check' ),
			)
		);
	}

	/**
	 * Verify the current user has the `manage_options` capability.
	 *
	 * @return bool|WP_Error True if allowed, WP_Error otherwise.
	 */
	public function permissions_check(): bool|WP_Error {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'rest_forbidden',
				'You do not have permission to manage these settings.',
				array( 'status' => rest_authorization_required_code() )
			);
		}
		return true;
	}

	/**
	 * Return the current plugin settings.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response
	 */
	public function get_settings( WP_REST_Request $request ): WP_REST_Response {
		return new WP_REST_Response( $this->plugin_settings->get_settings(), 200 );
	}

	/**
	 * Persist updated plugin settings.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_settings( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$body = $request->get_json_params();

		if ( ! is_array( $body ) || empty( $body ) ) {
			return new WP_Error(
				'invalid_request',
				'Request body is empty or not valid JSON.',
				array( 'status' => 400 )
			);
		}

		$sanitized = $this->plugin_settings->sanitize( $body );
		$updated   = update_option( $this->plugin_settings->get_option_key(), $sanitized );

		// update_option returns false both on DB error AND when the value has
		// not changed; treat an unchanged value as success.
		if ( false === $updated ) {
			$current = $this->plugin_settings->get_settings();
			if ( $current !== $sanitized ) {
				return new WP_Error(
					'update_failed',
					'Failed to update settings.',
					array( 'status' => 500 )
				);
			}
		}

		return new WP_REST_Response(
			array(
				'success'  => true,
				'message'  => 'Settings updated successfully.',
				'settings' => $sanitized,
			),
			200
		);
	}

	/**
	 * Fetch available groups from the MailerLite account using the saved API key.
	 *
	 * Returns a flat array of objects with `id` and `name` keys, suitable for
	 * rendering a select control in the admin UI.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_groups( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		$settings = $this->plugin_settings->get_settings();
		$api_key  = $settings['apiKey'] ?? '';

		if ( empty( $api_key ) ) {
			return new WP_Error(
				'missing_api_key',
				'Save your MailerLite API key before fetching groups.',
				array( 'status' => 400 )
			);
		}

		$service = new MailerLite_Service( $api_key );
		$groups  = $service->get_groups();

		// Return a simplified shape for the frontend.
		$simplified = array_map(
			static function ( array $group ): array {
				return array(
					'id'   => (string) $group['id'],
					'name' => $group['name'],
				);
			},
			$groups
		);

		return new WP_REST_Response( $simplified, 200 );
	}
}
