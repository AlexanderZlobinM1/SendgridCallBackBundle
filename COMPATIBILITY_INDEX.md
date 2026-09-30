# Compatibility index

Last synchronized from confirmed workspace evidence: 30 September 2026.

Current release: `1.5.11` (`bb116171c9cf`). Declared support: Mautic 5.x, 6.x, 7.x; PHP >=8.1; Symfony SendGrid Mailer ^5.4 || ^6.0 || ^7.0.

| Mautic | Status | Confirmed scope |
| --- | --- | --- |
| 5.2.10 | ✓ | Fresh kernel and real MariaDB callback tests, 6 Sep 2026 |
| 6.0.9 | ✓ | Fresh kernel and real MariaDB callback tests, 6 Sep 2026 |
| 7.1.3 | ✓ | Fresh kernel and real MariaDB callback tests, 6 Sep 2026 |
| 7.2.0 | ✓ | Fresh kernel and real MariaDB callback tests, 6 Sep 2026 |

Tests cover attribution, duplicate events and DNC updates, not live SendGrid delivery or historical DNC backfill. The 1.5.11 change adds a Composer dependency and inherits the prior runtime compatibility evidence; dependency resolution still needs to be checked in each Mautic major installation. Update this file and the workspace `../../COMPATIBILITY_INDEX.md` row with every plugin-related change or review.
