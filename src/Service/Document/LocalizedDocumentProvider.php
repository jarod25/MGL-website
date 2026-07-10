<?php

namespace App\Service\Document;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class LocalizedDocumentProvider
{
    private const FALLBACK_LOCALE = 'fr';
    private const DOCUMENTS = [
        'rules' => 'documents/rules/regulations_mgt_%s.pdf',
        'parental_authorization' => 'documents/authorizations/parental_authorization_%s.pdf',
        'image_rights' => 'documents/image-rights/image_rights_%s.pdf',
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
        $document = $this->getDocument($type, $locale);

        return $document ? '/' . $document->relativePath : null;
    }

    public function getDocument(string $type, string $locale): ?LocalizedDocument
    {
        if (!isset(self::DOCUMENTS[$type])) {
            return null;
        }

        $normalizedLocale = $this->normalizeLocale($locale);
        $documentLocale = $normalizedLocale;
        $relativePath = $this->getExistingRelativePath($type, $documentLocale);

        if (null === $relativePath && self::FALLBACK_LOCALE !== $documentLocale) {
            $documentLocale = self::FALLBACK_LOCALE;
            $relativePath = $this->getExistingRelativePath($type, $documentLocale);
        }

        if (null === $relativePath) {
            return null;
        }

        return new LocalizedDocument(
            $type,
            $documentLocale,
            $relativePath,
            $this->publicDirectory . '/' . $relativePath,
            basename($relativePath),
        );
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
