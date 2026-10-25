<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\UpdatePage;

use Gaby\Apps\Api\Page\Domain\Exception\PageNotFoundException;
use Gaby\Apps\Api\Page\Domain\Exception\SlugAlreadyInUseException;
use Gaby\Apps\Api\Page\Domain\Page;
use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;

final class UpdatePageHandler
{
    /** @var PageRepositoryInterface */
    private $pages;

    public function __construct(PageRepositoryInterface $pages)
    {
        $this->pages = $pages;
    }

    public function handle(UpdatePageCommand $command): Page
    {
        $page = $this->pages->findById($command->id);

        if ($page === null) {
            throw PageNotFoundException::withId($command->id);
        }

        if ($this->pages->existsBySlug($command->slug, $command->id)) {
            throw SlugAlreadyInUseException::withSlug($command->slug);
        }

        $page->updateDetails($command->slug, $command->title, $command->body, $command->status);
        $this->pages->save($page);

        return $page;
    }
}
