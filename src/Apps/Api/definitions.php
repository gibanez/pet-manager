<?php

declare(strict_types=1);

return array_merge(
    require __DIR__ . '/Admin/Infrastructure/definitions.php',
    require __DIR__ . '/Auth/Infrastructure/definitions.php',
    require __DIR__ . '/Page/Infrastructure/definitions.php',
    require __DIR__ . '/Inquiry/Infrastructure/definitions.php'
);
