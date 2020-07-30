<?php

namespace Therealsmat\Services;

class FileService
{
    /**
     * @param string $filePath
     * @param string $content
     * @param int $flags
     * @return false|int
     */
    public function put(string $filePath, string $content, int $flags = FILE_APPEND)
    {
        return file_put_contents($filePath, $content, $flags);
    }
}