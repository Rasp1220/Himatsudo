<?php
declare(strict_types=1);

namespace Himatsudo\Api\Resource\App\Admin;

use BEAR\Resource\ResourceObject;
use Himatsudo\Api\Annotation\RequireAuth;
use Himatsudo\Api\Repository\ArticleRepository;

class Articles extends ResourceObject
{
    public function __construct(private readonly ArticleRepository $articleRepository)
    {
    }

    #[RequireAuth]
    public function onGet(
        int     $page       = 1,
        int     $per_page   = 20,
        ?int    $category_id = null,
        ?string $status     = null,
        ?string $keyword    = null
    ): static {
        $result = $this->articleRepository->findAllAdmin($page, $per_page, $category_id, $status, $keyword);
        $this->body = [
            'items'     => $result['items'],
            'total'     => $result['total'],
            'page'      => $page,
            'per_page'  => $per_page,
            'last_page' => (int) ceil($result['total'] / max(1, $per_page)),
        ];
        return $this;
    }
}
