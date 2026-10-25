<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Application\DeletePage;

use Gaby\Apps\Api\Page\Domain\Exception\PageNotFoundException;
use Gaby\Apps\Api\Page\Domain\PageRepositoryInterface;

final class DeletePageHandler
{
    /** @var PageRepositoryInterface */
    private $pages;

    public function __construct(PageRepositoryInterface $pages)
    {
        $this->pages = $pages;
    }

    public function handle(DeletePageCommand $command): void
    {
        $page = $this->pages->findById($command->id);

        if ($page === null) {
            throw PageNotFoundException::withId($command->id);
        }

        $this->pages->delete($command->id);
    }
}
