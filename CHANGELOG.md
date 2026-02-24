# Changelog

## 1.5.5 - 2026-02-24

- Replaced copyright footer input with plain static text in integration modal.
- Removed `Alexander Zlobin` from footer line.
- Kept spacing after `Active` toggle and patch-only version bump.

## 1.5.4 - 2026-02-24

- Fixed spacing under `Active` toggle in integration modal (row-level margin for stable rendering).
- Kept footer as a single plain text line at the bottom of the form:
  - `Copyright 2026 Sales Snap. Alexander Zlobin All Rights Reserved.`
- Patch-only version bump.

## 1.5.3 - 2026-02-24

- Adjusted integration modal layout to match requested spacing after Active switch.
- Replaced footer with one plain text line:
  - `Copyright 2026 Sales Snap. Alexander Zlobin All Rights Reserved.`
- Patch-only version bump.

## 1.5.2 - 2026-02-24

- Updated plugin tile icon to transparent/light style based on provided `pngegg.png`.
- Patch-only version bump.

## 1.5.1 - 2026-02-24

- Added explicit integration icon override via `getIcon()` to force tile icon path.
- Added fallback integration icon file `Assets/img/sendgridcallback.png`.
- Bumped patch version only.

## 1.5.0 - 2026-02-24

- Updated plugin tile icon to user-provided `pngegg.png` branding asset.
- Replaced metadata block in integration modal with plain footer text line:
  - `Sales Snap, Alexander Zlobin, 2026`
- Kept integration-modal settings workflow (no standalone settings menu page).

## 1.4.0 - 2026-02-24

- Reworked configuration to integration modal flow (same pattern as MultiCaptcha), no standalone Settings menu page.
- Removed Config tab subscriber/form registration and moved runtime settings source to Integration keys.
- Added integration modal metadata fields:
  - Company
  - Author
  - Plugin version
  - Copyright
- Kept event toggles and dropped policy as selectable controls in the plugin modal.

## 1.3.0 - 2026-02-24

- Moved all plugin settings to the plugin tile/modal form (MultiCaptcha-like flow).
- Removed separate SendGrid settings menu and standalone settings page.
- Added plugin meta footer directly in settings form:
  - Company
  - Author
  - Plugin version
  - Copyright
- Kept safe selectable controls only (switches/checkboxes/select), no free-form risky fields.

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
