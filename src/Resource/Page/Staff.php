<?php

declare(strict_types=1);

namespace Himatsudo\Resource\Page;

use BEAR\Resource\ResourceObject;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Interfaces\UserInterface;

class Staff extends ResourceObject
{
    public function __construct(
        private readonly UserInterface     $userService,
        private readonly CategoryInterface $categoryService,
    ) {
    }

    public function onGet(): static
    {
        $this->body = [
            'staff'      => $this->userService->getPublicList(),
            'categories' => $this->categoryService->getAll(),
            'page_title' => '運営一覧',
            '_template'  => 'staff/index',
        ];

        return $this;
    }
}
