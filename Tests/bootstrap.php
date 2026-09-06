<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

// Test doubles only. The separate runtime compatibility test uses unmodified core classes.
DG\BypassFinals::enable();
