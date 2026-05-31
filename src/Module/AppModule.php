<?php
declare(strict_types=1);

namespace Himatsudo\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interceptor\AuthInterceptor;
use Himatsudo\Auth\JwtService;
use Himatsudo\Repository\ArticleRepository;
use Himatsudo\Repository\CategoryRepository;
use Himatsudo\Repository\RefreshTokenRepository;
use Himatsudo\Repository\UserRepository;
use Himatsudo\Service\YoutubeService;
use Ray\AuraSqlModule\AuraSqlModule;
use Ray\Di\AbstractModule;

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

        // Services
        $this->bind(JwtService::class);

        // Repositories
        $this->bind(UserRepository::class);
        $this->bind(CategoryRepository::class);
        $this->bind(ArticleRepository::class);
        $this->bind(RefreshTokenRepository::class);
        $this->bind(YoutubeService::class);

        // Auth interceptor on methods annotated with #[RequireAuth]
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(RequireAuth::class),
            [AuthInterceptor::class]
        );
    }
}
