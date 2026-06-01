<?php
declare(strict_types=1);

namespace Himatsudo\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use Himatsudo\Annotation\RequireAuth;
use Himatsudo\Interceptor\AuthInterceptor;
use Himatsudo\Auth\JwtService;
use Himatsudo\Contract\Repository\ArticleRepositoryInterface;
use Himatsudo\Contract\Repository\CategoryRepositoryInterface;
use Himatsudo\Contract\Repository\UserRepositoryInterface;
use Himatsudo\Contract\Service\ArticleServiceInterface;
use Himatsudo\Contract\Service\CategoryServiceInterface;
use Himatsudo\Contract\Service\UserServiceInterface;
use Himatsudo\Repository\ArticleRepository;
use Himatsudo\Repository\CategoryRepository;
use Himatsudo\Repository\RefreshTokenRepository;
use Himatsudo\Repository\UserRepository;
use Himatsudo\Service\ArticleService;
use Himatsudo\Service\CategoryService;
use Himatsudo\Service\UserService;
use Himatsudo\Service\YoutubeService;
use Ray\AuraSqlModule\AuraSqlModule;

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
        $this->bind(YoutubeService::class);

        // Repository interface bindings
        $this->bind(ArticleRepositoryInterface::class)->to(ArticleRepository::class);
        $this->bind(CategoryRepositoryInterface::class)->to(CategoryRepository::class);
        $this->bind(UserRepositoryInterface::class)->to(UserRepository::class);

        // Service interface bindings
        $this->bind(ArticleServiceInterface::class)->to(ArticleService::class);
        $this->bind(CategoryServiceInterface::class)->to(CategoryService::class);
        $this->bind(UserServiceInterface::class)->to(UserService::class);

        // Concrete repository bindings (for direct injection if needed)
        $this->bind(RefreshTokenRepository::class);

        // Auth interceptor on methods annotated with #[RequireAuth]
        $this->bindInterceptor(
            $this->matcher->any(),
            $this->matcher->annotatedWith(RequireAuth::class),
            [AuthInterceptor::class]
        );
    }
}
