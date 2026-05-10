<?php
/**
 * Notifier class.
 *
 * Handles admin notices for the plugin.
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

namespace MacrosBySara\PMProMailerLite\WP;

/**
 * Notifier class.
 */
class Notifier {
	/**
	 * The email addresses to send notifications to.
	 *
	 * @var array $emails
	 */
	private array $emails;

	/**
	 * Constructor.
	 *
	 * @param string|string[] $emails One or more email addresses to send notifications to, in addition to the WordPress admin email.
	 */
	public function __construct( string|array $emails ) {
		$additional_emails = is_string( $emails ) ? array( $emails ) : $emails;
		$this->emails      = array_unique( array( get_option( 'admin_email' ), ...$additional_emails ) );
	}

	/**
	 * Send a notification email.
	 *
	 * @param string $subject The email subject.
	 * @param string $message The email message.
	 */
	public function send( string $subject, string $message ): void {
		wp_mail( $this->emails, $subject, $message );
	}
}
