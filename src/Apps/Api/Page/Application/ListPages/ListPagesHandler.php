<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\ListPages;

use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;

final class ListPagesHandler
{
    /** @var PageRepositoryInterface */
    private $pages;

    public function __construct(PageRepositoryInterface $pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return \Gaby\Apps\Api\Page\Domain\Page[]
     */
    public function handle(ListPagesQuery $query): array
    {
        return $this->pages->findAll();
    }
}
