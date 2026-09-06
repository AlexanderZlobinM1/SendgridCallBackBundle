# Changelog

## [1.5.10] - 2026-09-06

- Serialize callback DNC updates per contact, ignore repeated feedback and preserve unsubscribe state and original email attribution.

- Preserve outgoing SendGrid custom arguments, accept top-level webhook attribution, reject invalid IDs and surface callback database failures. Route blocked bounce types through the existing switch.
- Audit and regression coverage against managed SES 1.0.38.x; see `LINEAGE_AUDIT.md`.

## 1.5.9 — 2026-09-06

- Support Mautic 7.2 while retaining the declared older Mautic versions.
- Use a plugin-scoped EncryptionHelper service alias; keep legacy argument parsing and the global core container unchanged.
- Add a fresh-kernel regression check that instantiates integration services and resolves form types.
- Preserve the Mautic 5 session constructor argument and correctly recognize two-digit patch versions such as 5.2.10.


## 1.5.8 - 2026-08-30

- Added a defensive publication-state check inside payload processing.
- Ensured an inactive plugin cannot create or update Do Not Contact records,
  including when payload processing is invoked outside the normal webhook entry
  point.
- Confirmed the inactive-plugin behavior with the existing regression test.
- Declared PHPUnit as a development dependency so the regression suite can be
  installed and run without modifying the package manifest locally.

## 1.5.7 - 2026-07-07

- Added Sales Snap footer link to `sales-snap.com` in the integration modal.

## 1.5.6 - 2026-02-24

- Prepared package for Packagist/Composer publication.
- Removed `minimum-stability: dev` and `prefer-stable` from `composer.json` for stable tagged installs.
- Added `homepage` in `composer.json`.
- Added Composer install command to README:
  - `composer require azlobin/mautic-sendgrid-callback`

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
