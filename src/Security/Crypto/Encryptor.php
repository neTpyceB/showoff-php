<?php

declare(strict_types=1);

namespace App\Security\Crypto;

use Showoff\Core\Config\AppConfig;

final readonly class Encryptor
{
    public function __construct(
        private AppConfig $config,
    ) {}

    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $this->key());

        return base64_encode($nonce . $ciphertext);
    }

    public function decrypt(string $encodedCiphertext): string
    {
        $decoded = base64_decode($encodedCiphertext, true);
        if (!is_string($decoded) || strlen($decoded) <= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES) {
            throw new \RuntimeException('Invalid ciphertext payload.');
        }

        $nonce = substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $ciphertext = substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $plaintext = sodium_crypto_secretbox_open($ciphertext, $nonce, $this->key());
        if (!is_string($plaintext)) {
            throw new \RuntimeException('Unable to decrypt ciphertext.');
        }

        return $plaintext;
    }

    private function key(): string
    {
        return sodium_crypto_generichash(
            $this->config->secret,
            '',
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
        );
    }
}
