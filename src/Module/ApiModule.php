<?php
declare(strict_types=1);

namespace Himatsudo\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Resource\Annotation\ContextScheme;
use BEAR\Sunday\Annotation\DefaultSchemeHost;

class ApiModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->bind()->annotatedWith(DefaultSchemeHost::class)->toInstance('app://self');
        $this->bind()->annotatedWith(ContextScheme::class)->toInstance('app://self');
    }
}
