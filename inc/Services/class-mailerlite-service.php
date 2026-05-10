<?php
/**
 * MailerLite API Service.
 *
 * Wraps the MailerLite REST API (connect.mailerlite.com/api) using
 * WordPress's built-in HTTP functions so the plugin has no external
 * Composer dependencies at runtime.
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

namespace MacrosBySara\PMProMailerLite\Services;

use MacrosBySara\PMProMailerLite\WP\Notifier;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles all communication with the MailerLite API.
 */
class MailerLite_Service {
	/**
	 * The MailerLite API base URL.
	 *
	 * @var string
	 */
	private const BASE_URL = 'https://connect.mailerlite.com/api';

	/**
	 * The MailerLite API key used for authentication.
	 *
	 * @var string $api_key
	 */
	private string $api_key;

	/**
	 * The Notifier instance for sending notifications.
	 *
	 * @var Notifier $notifier
	 */
	private Notifier $notifier;

	/**
	 * Constructor.
	 *
	 * @param string   $api_key The MailerLite API key.
	 * @param Notifier $notifier The Notifier instance for sending notifications.
	 */
	public function __construct( string $api_key, Notifier $notifier ) {
		$this->api_key  = $api_key;
		$this->notifier = $notifier;
	}

	/**
	 * Build the Authorization and content-type headers required by the API.
	 *
	 * @return array
	 */
	private function get_headers(): array {
		return array(
			'Authorization' => 'Bearer ' . $this->api_key,
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
		);
	}

	/**
	 * Create or update a subscriber and assign them to the given group.
	 *
	 * Uses the MailerLite upsert endpoint so existing subscribers are updated
	 * rather than duplicated.
	 *
	 * @param string $email      The subscriber's email address.
	 * @param string $first_name The subscriber's first name.
	 * @param string $last_name  The subscriber's last name.
	 * @param string $group_id   The MailerLite group ID to assign the subscriber to.
	 * @return bool True on success, false on failure.
	 */
	public function upsert_subscriber( string $email, string $first_name, string $last_name, string $group_id ): bool {
		$body = array(
			'email'  => $email,
			'fields' => array(
				'name'      => $first_name,
				'last_name' => $last_name,
			),
			'groups' => array( $group_id ),
		);
		try {
			$response = wp_remote_post(
				self::BASE_URL . '/subscribers',
				array(
					'headers' => $this->get_headers(),
					'body'    => wp_json_encode( $body ),
					'timeout' => 15,
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->notifier->send(
					'MailerLite API Error',
					sprintf(
						'An error occurred while communicating with the MailerLite API: %s',
						$response->get_error_code() . ' - ' . $response->get_error_message()
					)
				);
				return false;
			}

			$status_code = wp_remote_retrieve_response_code( $response );

			// MailerLite returns 200 (updated) or 201 (created) on success.
			return in_array( $status_code, array( 200, 201 ), true );
		} catch ( \Exception $e ) {
			$this->notifier->send(
				'MailerLite API Exception',
				sprintf(
					'An exception occurred while communicating with the MailerLite API: %s',
					esc_textarea( $e->getMessage() )
				)
			);
			return false;
		}
	}

	/**
	 * Retrieve all groups from the MailerLite account.
	 *
	 * Returns an array of group objects, each containing at minimum an `id`
	 * and a `name` key.  Returns an empty array on failure.
	 *
	 * @return array
	 */
	public function get_groups(): array {
		$response = wp_remote_get(
			self::BASE_URL . '/groups',
			array(
				'headers' => $this->get_headers(),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			return array();
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		return $body['data'] ?? array();
	}
}
