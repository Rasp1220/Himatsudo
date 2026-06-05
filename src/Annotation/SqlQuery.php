<?php

declare(strict_types=1);

namespace Himatsudo\Annotation;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class SqlQuery
{
    /**
     * @param string   $file   SQL ファイルパス (src/sql/ からの相対パス)
     * @param string[] $params SQL に渡すバインドパラメータ名の一覧
     */
    public function __construct(
        public readonly string $file,
        public readonly array  $params = [],
    ) {
    }
}
