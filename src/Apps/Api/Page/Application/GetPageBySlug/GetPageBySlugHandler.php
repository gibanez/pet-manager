<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\GetPageBySlug;

use Gaby\Apps\Api\Page\Domain\Exception\PageNotFoundException;
use Gaby\Apps\Api\Page\Domain\Page;
use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;

final class GetPageBySlugHandler
{
    /** @var PageRepositoryInterface */
    private $pages;

    public function __construct(PageRepositoryInterface $pages)
    {
        $this->pages = $pages;
    }

    public function handle(GetPageBySlugQuery $query): Page
    {
        $page = $this->pages->findBySlug($query->slug);

        if ($page === null) {
            throw PageNotFoundException::withSlug($query->slug);
        }

        return $page;
    }
}
