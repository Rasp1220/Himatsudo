<?php
declare(strict_types=1);

namespace Himatsudo\Api\Annotation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class RequireAuth
{
}
