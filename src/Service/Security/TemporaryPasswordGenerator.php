<?php

namespace App\Service\Security;

final class TemporaryPasswordGenerator
{
    private const LOWERCASE = 'abcdefghijklmnopqrstuvwxyz';
    private const UPPERCASE = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    private const DIGITS = '0123456789';
    private const SPECIAL = '!@#$%^&*()-_=+{};:,<.>';

    public function generate(int $length = 20): string
    {
        $length = max(16, $length);
        $characters = [
            $this->pick(self::LOWERCASE),
            $this->pick(self::UPPERCASE),
            $this->pick(self::DIGITS),
            $this->pick(self::SPECIAL),
        ];

        $pool = self::LOWERCASE.self::UPPERCASE.self::DIGITS.self::SPECIAL;
        while (count($characters) < $length) {
            $characters[] = $this->pick($pool);
        }

        return implode('', $this->secureShuffle($characters));
    }

    private function pick(string $characters): string
    {
        return $characters[random_int(0, strlen($characters) - 1)];
    }

    /**
     * @param list<string> $characters
     * @return list<string>
     */
    private function secureShuffle(array $characters): array
    {
        for ($i = count($characters) - 1; $i > 0; --$i) {
            $j = random_int(0, $i);
            [$characters[$i], $characters[$j]] = [$characters[$j], $characters[$i]];
        }

        return $characters;
    }
}
