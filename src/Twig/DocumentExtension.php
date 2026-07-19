<?php

namespace App\Twig;

use App\Service\Document\LocalizedDocumentProvider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DocumentExtension extends AbstractExtension
{
    public function __construct(private readonly LocalizedDocumentProvider $localizedDocumentProvider)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('localized_document_url', [$this, 'getLocalizedDocumentUrl']),
        ];
    }

    public function getLocalizedDocumentUrl(string $type, string $locale): ?string
    {
        return $this->localizedDocumentProvider->getPublicUrl($type, $locale);
    }
}
