<?php

namespace App\Service\Document;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class LocalizedDocumentProvider
{
    private const FALLBACK_LOCALE = 'fr';
    private const DOCUMENTS = [
        'rules' => 'documents/rules/regulations_mgt_%s.pdf',
    ];

    /** @var string[] */
    private const SUPPORTED_LOCALES = ['fr', 'en', 'de'];

    public function __construct(
        #[Autowire('%kernel.project_dir%/public')]
        private readonly string $publicDirectory,
    ) {
    }

    public function getPublicUrl(string $type, string $locale): ?string
    {
        if (!isset(self::DOCUMENTS[$type])) {
            return null;
        }

        $normalizedLocale = $this->normalizeLocale($locale);
        $relativePath = $this->getExistingRelativePath($type, $normalizedLocale)
            ?? $this->getExistingRelativePath($type, self::FALLBACK_LOCALE);

        return $relativePath ? '/' . $relativePath : null;
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(substr($locale, 0, 2));

        return in_array($locale, self::SUPPORTED_LOCALES, true) ? $locale : self::FALLBACK_LOCALE;
    }

    private function getExistingRelativePath(string $type, string $locale): ?string
    {
        $relativePath = sprintf(self::DOCUMENTS[$type], $locale);
        $absolutePath = $this->publicDirectory . '/' . $relativePath;

        return is_file($absolutePath) ? $relativePath : null;
    }
}
