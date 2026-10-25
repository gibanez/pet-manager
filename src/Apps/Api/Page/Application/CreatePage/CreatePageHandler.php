<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\CreatePage;

use Gaby\Apps\Api\Page\Domain\Exception\SlugAlreadyInUseException;
use Gaby\Apps\Api\Page\Domain\Page;
use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;

final class CreatePageHandler
{
    /** @var PageRepositoryInterface */
    private $pages;

    public function __construct(PageRepositoryInterface $pages)
    {
        $this->pages = $pages;
    }

    public function handle(CreatePageCommand $command): Page
    {
        if ($this->pages->existsBySlug($command->slug)) {
            throw SlugAlreadyInUseException::withSlug($command->slug);
        }

        $page = Page::create($command->slug, $command->title, $command->body, $command->status);
        $this->pages->save($page);

        return $page;
    }
}
