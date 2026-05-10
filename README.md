# PMPro MailerLite Integration

A WordPress plugin that integrates [Paid Memberships Pro](https://www.paidmembershipspro.com/) with [MailerLite](https://www.mailerlite.com/) to automatically subscribe members to a MailerLite group when they complete checkout.

## Requirements

- WordPress 6.7+
- PHP 8.2+
- [Paid Memberships Pro](https://wordpress.org/plugins/paid-memberships-pro/) plugin
- A MailerLite account with an API key

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Run `composer install` inside the plugin directory to install PHP dependencies.
3. Run `npm install && npm run build` to compile the admin page assets.
4. Activate the plugin through the **Plugins** menu in WordPress.
5. Go to **Settings → PMPro MailerLite** to configure the plugin.

## Configuration

1. Enter your **MailerLite API key** (found under *Integrations → API* in your MailerLite account).
2. Save the settings.
3. Click **Fetch Groups** to load the available groups from your account.
4. Select the group that members should be added to on checkout.
5. Save again.

## Development

### PHP

```bash
composer install
composer run phpcs   # lint
composer run phpcbf  # auto-fix
```

### JavaScript / TypeScript

```bash
npm install
npm run build   # production build
npm run start   # development watch mode
npm run lint:js # lint
```

## License

GPLv3 or later. See [LICENSE](LICENSE) for details.
