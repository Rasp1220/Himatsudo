<?php
declare(strict_types=1);

namespace Himatsudo\Module;

use BEAR\Package\AbstractAppModule;

class ApiModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(new AppModule());
    }
}
