<?php

namespace Therealsmat\Services;

class FileHelper
{
    /**
     * @param string $filePath
     * @return bool
     */
    public function isDir(string $filePath): bool
    {
        return is_dir($filePath);
    }

    /**
     * @param string $filePath
     * @return string
     */
    public function readContents(string $filePath): string
    {
        return file_get_contents($filePath);
    }

    /**
     * @param string $filePath
     * @param string $content
     * @param int $flags
     * @return false|int
     */
    public function put(string $filePath, string $content, int $flags = 0)
    {
        return file_put_contents($filePath, $content, $flags);
    }
}