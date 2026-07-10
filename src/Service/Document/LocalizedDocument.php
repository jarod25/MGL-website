<?php

namespace App\Service\Document;

final readonly class LocalizedDocument
{
    public function __construct(
        public string $type,
        public string $locale,
        public string $relativePath,
        public string $absolutePath,
        public string $filename,
    ) {
    }
}
