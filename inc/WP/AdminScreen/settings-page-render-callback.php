<?php
/**
 * Settings page for PMPro MailerLite Integration.
 * Called via Admin_Screen::render_settings_page() callback.
 *
 * @package MacrosBySara
 * @subpackage PMProMailerLite
 */

?>
<div class="wrap">
	<h1>PMPro MailerLite Integration</h1>

	<div
		id="mbs-settings"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>"
		data-rest-url="<?php echo esc_attr( rest_url( 'mbs/v1' ) ); ?>"
	></div>

	<noscript>
		This plugin relies on JavaScript to function properly. Please enable JavaScript in your browser settings and refresh the page.
	</noscript>
</div>
<?php
