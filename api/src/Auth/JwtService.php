<?php
declare(strict_types=1);

namespace Himatsudo\Api\Auth;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use RuntimeException;

final class JwtService
{
    private readonly Configuration $config;
    private const ACCESS_TOKEN_TTL  = '+1 hour';
    private const REFRESH_TOKEN_TTL = '+30 days';

    public function __construct()
    {
        $secret = $_ENV['JWT_SECRET'] ?? 'himatsudo-default-jwt-secret-key-change-in-production-32chars';
        $this->config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText($secret)
        );
    }

    public function issueAccessToken(int $userId, string $role): string
    {
        $now = new DateTimeImmutable();
        return $this->config->builder()
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify(self::ACCESS_TOKEN_TTL))
            ->withClaim('uid', $userId)
            ->withClaim('role', $role)
            ->withClaim('type', 'access')
            ->getToken($this->config->signer(), $this->config->signingKey())
            ->toString();
    }

    public function issueRefreshToken(int $userId): string
    {
        $now = new DateTimeImmutable();
        return $this->config->builder()
            ->issuedAt($now)
            ->canOnlyBeUsedAfter($now)
            ->expiresAt($now->modify(self::REFRESH_TOKEN_TTL))
            ->withClaim('uid', $userId)
            ->withClaim('type', 'refresh')
            ->getToken($this->config->signer(), $this->config->signingKey())
            ->toString();
    }

    /** @return array{uid: int, role: string} */
    public function validateAccessToken(string $tokenString): array
    {
        $token = $this->parseAndValidate($tokenString);
        $claims = $token->claims();
        if ($claims->get('type') !== 'access') {
            throw new RuntimeException('Token type is not access');
        }
        return [
            'uid'  => (int) $claims->get('uid'),
            'role' => (string) $claims->get('role'),
        ];
    }

    public function validateRefreshToken(string $tokenString): int
    {
        $token = $this->parseAndValidate($tokenString);
        $claims = $token->claims();
        if ($claims->get('type') !== 'refresh') {
            throw new RuntimeException('Token type is not refresh');
        }
        return (int) $claims->get('uid');
    }

    private function parseAndValidate(string $tokenString): Plain
    {
        $token = $this->config->parser()->parse($tokenString);
        assert($token instanceof Plain);

        $clock = new SystemClock(new DateTimeZone('UTC'));
        $this->config->validator()->assert(
            $token,
            new SignedWith($this->config->signer(), $this->config->signingKey()),
            new StrictValidAt($clock)
        );

        return $token;
    }
}
