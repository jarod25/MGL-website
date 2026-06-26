<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class PublicImageResolver
{
    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['svg', 'png', 'webp', 'jpg', 'jpeg'];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function resolve(string $directory, string $basename): ?string
    {
        if (!$this->isSafeRelativePath($directory) || !$this->isSafeBasename($basename)) {
            return null;
        }

        $directory = trim($directory, '/');

        foreach (self::ALLOWED_EXTENSIONS as $extension) {
            $relativePath = $directory.'/'.$basename.'.'.$extension;
            $absolutePath = $this->projectDir.'/public/'.$relativePath;

            if ($this->isValidImage($absolutePath, $extension)) {
                return $relativePath;
            }
        }

        return null;
    }

    private function isSafeRelativePath(string $directory): bool
    {
        $directory = trim($directory, '/');

        return $directory !== ''
            && !str_contains($directory, '..')
            && preg_match('/^[a-zA-Z0-9_\/-]+$/', $directory) === 1;
    }

    private function isSafeBasename(string $basename): bool
    {
        return $basename !== ''
            && !str_contains($basename, '/')
            && !str_contains($basename, '\\')
            && !str_contains($basename, '..')
            && preg_match('/^[a-zA-Z0-9_-]+$/', $basename) === 1;
    }

    private function isValidImage(string $absolutePath, string $extension): bool
    {
        if (!is_file($absolutePath) || !is_readable($absolutePath) || filesize($absolutePath) === 0) {
            return false;
        }

        if ($extension === 'svg') {
            return $this->isValidSvg($absolutePath);
        }

        return @getimagesize($absolutePath) !== false;
    }

    private function isValidSvg(string $absolutePath): bool
    {
        $handle = @fopen($absolutePath, 'rb');
        if ($handle === false) {
            return false;
        }

        $chunk = fread($handle, 4096);
        fclose($handle);

        return is_string($chunk) && stripos($chunk, '<svg') !== false;
    }
}
