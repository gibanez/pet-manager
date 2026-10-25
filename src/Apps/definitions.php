<?php

declare(strict_types=1);

return array_merge(
    require __DIR__ . '/../Shared/Infrastructure/Container/definitions.php',
    require __DIR__ . '/Api/definitions.php',
    require __DIR__ . '/Site/definitions.php'
);
