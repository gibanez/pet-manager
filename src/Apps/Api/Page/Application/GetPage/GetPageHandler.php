<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\GetPage;

use Gaby\Apps\Api\Page\Domain\Exception\PageNotFoundException;
use Gaby\Apps\Api\Page\Domain\Page;
use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;

final class GetPageHandler
{
    /** @var PageRepositoryInterface */
    private $pages;

    public function __construct(PageRepositoryInterface $pages)
    {
        $this->pages = $pages;
    }

    public function handle(GetPageQuery $query): Page
    {
        $page = $this->pages->findById($query->id);

        if ($page === null) {
            throw PageNotFoundException::withId($query->id);
        }

        return $page;
    }
}
