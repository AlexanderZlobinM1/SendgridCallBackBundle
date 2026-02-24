# Mautic SendGrid Callback

Plugin for Mautic 5/6/7 to process SendGrid Event Webhook callbacks and mark contacts as Do Not Contact for email channel.

This plugin does not send email. Email sending is handled by Symfony's standard SendGrid mailer transport configured in Mautic.

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

1. Copy plugin directory to your Mautic installation:

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

## Notes

- The plugin tries to extract `X-EMAIL-ID` from `custom_args` or `unique_args` in webhook payload to link callback to email ID.
- If `X-EMAIL-ID` is missing, callback still marks contact by email address.
