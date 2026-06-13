<?php

declare(strict_types=1);

namespace Himatsudo\Service;

trait PaginationTrait
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @return array{items: array<int, array<string, mixed>>, total: int, page: int, per_page: int, last_page: int}
     */
    private function paginate(array $items, int $total, int $page, int $perPage): array
    {
        return [
            'items'     => $items,
            'total'     => $total,
            'page'      => $page,
            'per_page'  => $perPage,
            'last_page' => (int) ceil($total / max(1, $perPage)),
        ];
    }
}
