<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

class Blog extends CategoryTypeListPage
{
    protected const CATEGORY_TYPE  = 'blog';
    protected const FALLBACK_TITLE = 'ブログ';
    protected const LIST_BASE_URL  = '/blog';
}
