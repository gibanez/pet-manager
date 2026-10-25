<?php

declare(strict_types=1);

namespace Gaby\Apps\Api\Page\Infrastructure\Http;

use Gaby\Apps\Api\Page\Application\CreatePage\CreatePageCommand;
use Gaby\Apps\Api\Page\Application\CreatePage\CreatePageHandler;
use Gaby\Apps\Api\Page\Application\DeletePage\DeletePageCommand;
use Gaby\Apps\Api\Page\Application\DeletePage\DeletePageHandler;
use Gaby\Apps\Api\Page\Application\GetPage\GetPageHandler;
use Gaby\Apps\Api\Page\Application\GetPage\GetPageQuery;
use Gaby\Apps\Api\Page\Application\GetPageBySlug\GetPageBySlugHandler;
use Gaby\Apps\Api\Page\Application\GetPageBySlug\GetPageBySlugQuery;
use Gaby\Apps\Api\Page\Application\ListPages\ListPagesHandler;
use Gaby\Apps\Api\Page\Application\ListPages\ListPagesQuery;
use Gaby\Apps\Api\Page\Application\UpdatePage\UpdatePageCommand;
use Gaby\Apps\Api\Page\Application\UpdatePage\UpdatePageHandler;
use Gaby\Apps\Api\Page\Domain\Exception\PageNotFoundException;
use Gaby\Apps\Api\Page\Domain\Exception\SlugAlreadyInUseException;
use Gaby\Apps\Api\Page\Domain\Page;
use Gaby\Shared\Http\JsonResponseTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class PageController
{
    use JsonResponseTrait;

    /** @var CreatePageHandler */
    private $createPage;

    /** @var ListPagesHandler */
    private $listPages;

    /** @var GetPageHandler */
    private $getPage;

    /** @var GetPageBySlugHandler */
    private $getPageBySlug;

    /** @var UpdatePageHandler */
    private $updatePage;

    /** @var DeletePageHandler */
    private $deletePage;

    public function __construct(
        CreatePageHandler $createPage,
        ListPagesHandler $listPages,
        GetPageHandler $getPage,
        GetPageBySlugHandler $getPageBySlug,
        UpdatePageHandler $updatePage,
        DeletePageHandler $deletePage
    ) {
        $this->createPage = $createPage;
        $this->listPages = $listPages;
        $this->getPage = $getPage;
        $this->getPageBySlug = $getPageBySlug;
        $this->updatePage = $updatePage;
        $this->deletePage = $deletePage;
    }

    public function list(Request $request, Response $response): Response
    {
        $pages = $this->listPages->handle(new ListPagesQuery());

        return $this->json($response, [
            'pages' => array_map(function (Page $page): array {
                return $page->toArray();
            }, $pages),
        ]);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $page = $this->getPage->handle(new GetPageQuery((string) $args['id']));
        } catch (PageNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $this->json($response, $page->toArray());
    }

    public function showBySlug(Request $request, Response $response, array $args): Response
    {
        try {
            $page = $this->getPageBySlug->handle(new GetPageBySlugQuery((string) $args['slug']));
        } catch (PageNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        if (!$page->isActive()) {
            return $this->json($response, ['error' => 'Page not found.'], 404);
        }

        return $this->json($response, $page->toArray());
    }

    public function create(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $slug = $this->normalizeSlug(isset($data['slug']) ? (string) $data['slug'] : '');
        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        $body = isset($data['body']) ? (string) $data['body'] : '';
        $status = $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Page::STATUS_ACTIVE);

        if ($slug === '' || $title === '') {
            return $this->json($response, ['error' => 'Slug and title are required.'], 422);
        }

        try {
            $page = $this->createPage->handle(new CreatePageCommand($slug, $title, $body, $status));
        } catch (SlugAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $page->toArray(), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $data = (array) $request->getParsedBody();
        $slug = $this->normalizeSlug(isset($data['slug']) ? (string) $data['slug'] : '');
        $title = isset($data['title']) ? trim((string) $data['title']) : '';
        $body = isset($data['body']) ? (string) $data['body'] : '';
        $status = $this->normalizeStatus(isset($data['status']) ? (string) $data['status'] : Page::STATUS_ACTIVE);

        if ($slug === '' || $title === '') {
            return $this->json($response, ['error' => 'Slug and title are required.'], 422);
        }

        try {
            $page = $this->updatePage->handle(
                new UpdatePageCommand((string) $args['id'], $slug, $title, $body, $status)
            );
        } catch (PageNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        } catch (SlugAlreadyInUseException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 409);
        }

        return $this->json($response, $page->toArray());
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $this->deletePage->handle(new DeletePageCommand((string) $args['id']));
        } catch (PageNotFoundException $error) {
            return $this->json($response, ['error' => $error->getMessage()], 404);
        }

        return $response->withStatus(204);
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = (string) preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim($slug, '-');
    }

    private function normalizeStatus(string $status): string
    {
        return $status === Page::STATUS_INACTIVE ? Page::STATUS_INACTIVE : Page::STATUS_ACTIVE;
    }
}
