# Changelog

## 1.2.0 - 2026-02-24

- Added proper plugin tile/modal icon support via `Assets/img/icon.png` using SendGrid branding.
- Added master plugin enable switch (`sendgrid_callback_enabled`) in settings.
- Removed logo from config form tab; left settings-only layout with tooltips.
- Added localized, human-readable labels and tooltips for:
  - English (`en_US`)
  - Russian (`ru_RU`)
  - Serbian (`sr_RS`)
- Added translation for config tab title (`mautic.config.tab.sendgridcallback_config`).
- Added plugin version display on settings page and kept footer copyright block.

## 1.1.0 - 2026-02-24

- Added plugin settings UI with safe selectable options (checkboxes/choice) for SendGrid event handling.
- Added SendGrid settings page in Mautic menu with plugin branding block.
- Added SendGrid primary logo asset for plugin UI.
- Added author/company section:
  - Company: Sales Snap
  - Author: Alexander Zlobin
- Added strict runtime normalization/fallback for settings values.
- Added configurable dropped-event policy: `auto`, `bounced`, `unsubscribed`.

## 1.0.0 - 2026-02-24

- Initial SendGrid callback plugin for Mautic 5/6/7.
- Webhook processing for bounce/unsubscribe-related events.
