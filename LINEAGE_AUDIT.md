# SES → SendGrid → Mailganer audit (2026-09-06)

Reference: Sales Snap managed SES 1.0.38.1–1.0.38.3, based on upstream 1.0.38. The local SES documentation changes were not edited or included in this release.

| Concern | Finding and result |
| --- | --- |
| Route-unsafe `soft bounce` channel | Neither callback plugin creates this channel; they use Mautic's email-channel TransportCallback. No copied channel bug or migration is needed. Existing SES data migration remains owned by SES. |
| Token-bucket fopen/flock/ftruncate/fwrite failures | The old callback plugins contain no token bucket. Mailganer's new receipt storage checks open/lock/write/sync/rename, uses atomic snapshots and fails before continuing on errors. |
| Lost per-email attribution | Present: SendGrid missed top-level custom arguments; Mailganer confused numeric provider IDs with Mautic IDs. Both now validate explicit positive IDs, reject overflow/zero and preserve outgoing attribution. Missing metadata produces contact-level DNC plus a warning. |
| Silent send failures / duplicate retries | Callback database exceptions were swallowed. They now propagate to HTTP 503. Full Mailganer rejects failed/malformed send responses and retains receipts so accepted chunks are skipped on retry; uncertain outcomes are not blindly resent. |
| RAW inline filename behavior | SendGrid Callback has no MIME transport and cannot inherit this SES path. Full Mailganer preserves ordinary attachment filenames/binary content; unsupported inline CID/CC/BCC payloads are rejected explicitly, never silently truncated. |
| Provider event differences | SendGrid `bounce` with `type=blocked` now follows the existing blocked switch. Its configured DNC policy is otherwise retained. Mailganer package `external_id` is accepted as explicit attribution; provider IDs alone are not. |
| Repeated callback / DNC reason upgrade | Real database tests reproduced duplicate rows during repeated feedback. A plugin-owned per-contact transaction/lock checks persisted DNC state before invoking the native model, preserves unsubscribes and uses a string channel when upgrading an existing reason. |
| Mautic 5/6/7 | Version-aware DI and Mautic 5 attachment access preserved. Test doubles alone preload final core mock targets; runtime/database tests use the real core. |

SendGrid source: https://www.twilio.com/docs/sendgrid/for-developers/tracking-events/event
Mailganer source: https://documentation.samotpravil.ru/
