<?php
declare(strict_types=1);

namespace Himatsudo\Resource\Page;

class Youtube extends CategoryTypeListPage
{
    protected const CATEGORY_TYPE  = 'youtube';
    protected const FALLBACK_TITLE = 'YouTube';
    protected const LIST_BASE_URL  = '/youtube';
}
