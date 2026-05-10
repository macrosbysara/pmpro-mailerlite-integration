<?php
/**
 * Checkout Handler for PMPro MailerLite Integration.
 *
 * Listens to the `pmpro_after_checkout` action and subscribes the
 * purchasing member to the configured MailerLite group.
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

namespace MacrosBySara\PMProMailerLite\WP;

use MacrosBySara\PMProMailerLite\Services\MailerLite_Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles member subscription to MailerLite on PMPro checkout completion.
 */
class Checkout_Handler {
	/**
	 * The Plugin_Settings instance.
	 *
	 * @var Plugin_Settings $plugin_settings
	 */
	private readonly Plugin_Settings $plugin_settings;

	/**
	 * The MailerLite service instance.
	 *
	 * @var MailerLite_Service $mailerlite
	 */
	private readonly MailerLite_Service $mailerlite;

	/**
	 * Constructor.
	 *
	 * @param Plugin_Settings    $plugin_settings The plugin settings instance.
	 * @param MailerLite_Service $mailerlite      The MailerLite service instance.
	 */
	public function __construct( Plugin_Settings $plugin_settings, MailerLite_Service $mailerlite ) {
		$this->plugin_settings = $plugin_settings;
		$this->mailerlite      = $mailerlite;
	}

	/**
	 * Process a completed PMPro checkout.
	 *
	 * Creates or updates the member's MailerLite subscriber record and adds
	 * them to the configured group.  Silently exits if the plugin is not yet
	 * configured (no API key or group ID).
	 *
	 * @param int    $user_id The WordPress user ID of the member who checked out.
	 * @param object $order   The MemberOrder object from Paid Memberships Pro.
	 * @return void
	 */
	public function handle_after_checkout( int $user_id, object $order ): void {
		$settings = $this->plugin_settings->get_settings();
		$api_key  = $settings['apiKey'] ?? '';
		$group_id = $settings['groupId'] ?? '';

		// Do nothing if the plugin has not been configured yet.
		if ( empty( $api_key ) || empty( $group_id ) ) {
			return;
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}

		$this->mailerlite->upsert_subscriber(
			$user->user_email,
			$user->first_name,
			$user->last_name,
			$group_id,
			$order->membership_id
		);
	}
}
