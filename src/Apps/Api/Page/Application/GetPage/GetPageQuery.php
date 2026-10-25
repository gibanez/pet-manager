<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\GetPage;

final class GetPageQuery
{
    /** @var string */
    public $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }
}
