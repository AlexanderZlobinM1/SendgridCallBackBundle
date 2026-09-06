# Mautic SendGrid Callback

Plugin for Mautic 5/6/7 to process SendGrid Event Webhook callbacks and mark contacts as Do Not Contact for email channel.

This plugin does not send email. Email sending is handled by Symfony's standard SendGrid mailer transport configured in Mautic.

Company: Sales Snap  
Author: Alexander Zlobin

## Supported mailer schemes

- `sendgrid`
- `sendgrid+smtp`
- `sendgrid+api`

## Processed events

The plugin handles these SendGrid event types:

- `bounce` -> `DoNotContact::BOUNCED`
- `blocked` -> `DoNotContact::BOUNCED`
- `dropped` -> `DoNotContact::BOUNCED` (or `UNSUBSCRIBED` when reason indicates unsubscribe/spam)
- `spamreport` -> `DoNotContact::UNSUBSCRIBED`
- `unsubscribe` -> `DoNotContact::UNSUBSCRIBED`
- `group_unsubscribe` -> `DoNotContact::UNSUBSCRIBED`

All other events are ignored.

## Installation

1. Install via Composer:

```bash
composer require azlobin/mautic-sendgrid-callback
```

Or copy plugin directory to your Mautic installation:

```bash
cp -R SendgridCallbackBundle /path/to/mautic/docroot/plugins/
```

Or install from ZIP by extracting `SendgridCallbackBundle` into:

```text
/path/to/mautic/docroot/plugins/SendgridCallbackBundle
```

2. Reload plugins and clear cache:

```bash
php bin/console mautic:plugins:reload
php bin/console cache:clear
```

3. Configure SendGrid Event Webhook endpoint:

```text
https://mautic.example.com/mailer/callback
```

4. Enable at least these events in SendGrid Event Webhook settings:

- Bounce
- Blocked
- Dropped
- Spam Reports
- Unsubscribes

5. Open plugin card in Mautic Plugins and configure settings directly in plugin modal.

Use the available safe controls (switches/select) to enable/disable event processing and choose dropped-event mapping policy (`auto`, `bounced`, `unsubscribed`).

When the plugin is inactive, SendGrid webhook payloads are ignored and no Do
Not Contact records are created or changed by this plugin.

## Notes

- The plugin tries to extract `X-EMAIL-ID` from `custom_args` or `unique_args` in webhook payload to link callback to email ID.
- If `X-EMAIL-ID` is missing, callback still marks contact by email address.

## Release 1.5.10 attribution and retries

The plugin preserves Mautic email IDs in outgoing SendGrid API metadata and SMTP `X-SMTPAPI.unique_args`, merging existing metadata. It accepts top-level, `custom_args` and `unique_args` webhook formats, validates positive non-overflowing IDs, and warns when only contact-level DNC can be updated. `bounce` with `type=blocked` follows the existing blocked-event switch. Database failures now return HTTP 503 rather than silently acknowledging the event. See `LINEAGE_AUDIT.md`.
