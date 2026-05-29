<?php
declare(strict_types=1);

namespace Himatsudo\Frontend\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Resource\RenderInterface;
use Himatsudo\Frontend\Renderer\QiqRenderer;
use Ray\AuraSqlModule\AuraSqlModule;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(new PackageModule());

        // Database (shared with API)
        $dsn      = (string) ($_ENV['DB_DSN']      ?? 'mysql:host=localhost;dbname=himatsudo;charset=utf8mb4');
        $user     = (string) ($_ENV['DB_USER']     ?? 'root');
        $password = (string) ($_ENV['DB_PASSWORD'] ?? '');
        $this->install(new AuraSqlModule($dsn, $user, $password));

        // Qiq template renderer
        $this->bind(RenderInterface::class)->to(QiqRenderer::class);
    }
}
