<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MobileJwtService
{
    public function __construct(
        #[Autowire('%kernel.secret%')]
        private readonly string $secret,
    ) {
    }

    public function createToken(User $user, int $ttl = 86400): string
    {
        $issuedAt = time();
        $payload = [
            'sub' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'iat' => $issuedAt,
            'exp' => $issuedAt + $ttl,
        ];

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        $encodedHeader = $this->base64UrlEncode((string) json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode((string) json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->secret, true);

        return $encodedHeader . '.' . $encodedPayload . '.' . $this->base64UrlEncode($signature);
    }

    public function decodeToken(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        $signature = $this->base64UrlDecode($encodedSignature);
        $expectedSignature = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $this->secret, true);

        if (!hash_equals($expectedSignature, $signature)) {
            return null;
        }

        $headerJson = $this->base64UrlDecode($encodedHeader);
        $payloadJson = $this->base64UrlDecode($encodedPayload);

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header) || !is_array($payload)) {
            return null;
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            return null;
        }

        if (!isset($payload['exp']) || (int) $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }
}
