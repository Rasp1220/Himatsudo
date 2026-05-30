<?php
declare(strict_types=1);

namespace Himatsudo\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Resource\RenderInterface;
use Himatsudo\Renderer\QiqRenderer;

class HtmlModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(new AppModule());
        $this->bind(RenderInterface::class)->to(QiqRenderer::class);
    }
}
