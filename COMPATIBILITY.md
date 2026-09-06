# Mautic compatibility

Supported Mautic range remains 5.x, 6.x, 7.x.

Run against each supported Mautic installation with this plugin installed:

```sh
MAUTIC_ROOT=/path/to/mautic php -d memory_limit=1G Tests/runtime-compatibility.php
```

The test compiles an isolated container, instantiates this plugin’s integrations
and form types, then removes its temporary cache. Production cache and provider
settings are not changed. Live external-provider delivery requires a separate
configured acceptance environment; a successful kernel test does not prove delivery.

## Verified on 6 September 2026

Mautic 7.2.0 and 7.1.3 with PHP 8.4.25; Mautic 6.0.9 and 5.2.10 with PHP 8.2.33.
All 13 PHPUnit tests passed on each version. Fresh production kernel service/form checks
and real MariaDB callback tests passed on all four versions. Database tests verify
email attribution, repeated-event deduplication and bounce-to-unsubscribe updates.
Test doubles may bypass final core classes only in the unit-test bootstrap; native
kernel and database tests never bypass them.

Run the database check on an isolated installed Mautic test environment with the
plugin directory installed:

```sh
MAUTIC_ROOT=/path/to/mautic php Tests/database-callback.php
```

It creates uniquely named contact/email fixtures and removes them in a finally block.
Do not use production contacts for this test. See LINEAGE_AUDIT.md for inherited-risk
coverage and limits; the tests do not backfill old DNC records.
