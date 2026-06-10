<?php
declare(strict_types=1);

namespace Himatsudo\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Auth\AuthContext;
use Himatsudo\Auth\JwtService;
use Himatsudo\Interceptor\AuthInterceptor;
use Himatsudo\Interfaces\ArticleInterface;
use Himatsudo\Interfaces\CategoryInterface;
use Himatsudo\Interfaces\UserInterface;
use Himatsudo\Service\ArticleService;
use Himatsudo\Service\CategoryService;
use Himatsudo\Service\RefreshTokenService;
use Himatsudo\Service\UserService;
use Himatsudo\Service\YoutubeService;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\Di\Scope;

class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        $this->install(new PackageModule());

        // Database
        $dsn      = (string) ($_ENV['DB_DSN']      ?? 'mysql:host=localhost;dbname=himatsudo;charset=utf8mb4');
        $user     = (string) ($_ENV['DB_USER']     ?? 'root');
        $password = (string) ($_ENV['DB_PASSWORD'] ?? '');
        $this->install(new AuraSqlModule($dsn, $user, $password));

        // Standalone services
        $this->bind(JwtService::class);
        $this->bind(YoutubeService::class);
        $this->bind(RefreshTokenService::class);

        // Request-scoped auth claims (written by AuthInterceptor, read by resources)
        $this->bind(AuthContext::class)->in(Scope::SINGLETON);

        // Interface bindings
        $this->bind(ArticleInterface::class)->to(ArticleService::class);
        $this->bind(CategoryInterface::class)->to(CategoryService::class);
        $this->bind(UserInterface::class)->to(UserService::class);

        // Auth interceptor on methods annotated with #[RequireAuth]
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(RequireAuth::class),
            [AuthInterceptor::class]
        );
    }
}
