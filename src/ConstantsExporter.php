<?php

namespace Therealsmat;

use Therealsmat\Services\FileService;
use Therealsmat\Exceptions\ConstantsNotExportedException;

/**
 * Class ConstantsExporter
 * @package Therealsmat
 */
class ConstantsExporter
{
    const INDENTATION_CHARACTER = '    '; // 4 spaces instead of a tab
    const DESTINATION_FILE_EXTENSION = '.js';

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var array
     */
    private $constantsToExport = [];

    /**
     * ConstantsExporter constructor.
     * @param array $constantsToExport
     */
    public function __construct(array $constantsToExport = [])
    {
        $this->constantsToExport = $constantsToExport;
        $this->fileService = new FileService;
    }

    /**
     * @param array $constantsToExport
     * @return $this
     */
    public function setConstantsToExport(array $constantsToExport): self
    {
        $this->constantsToExport = $constantsToExport;
        return $this;
    }

    /**
     * @return bool
     * @throws ConstantsNotExportedException
     */
    public function perform(): bool
    {
        foreach ($this->constantsToExport as $source => $destination) {
            try {
                $destinationFilePath = $this->getDestinationFilePath($source, $destination);
                $this->copyConstantsToDestination($source, $destinationFilePath);
            } catch (\Exception $e)
            {
                throw new ConstantsNotExportedException($e->getMessage(), $e->getCode(), $e);
            }
        }
        return true;
    }

    /**
     * @param string $source
     * @param string $destination
     * @return string
     * @throws \ReflectionException
     */
    private function getDestinationFilePath(string $source, string $destination): string
    {
        $destination = rtrim($destination, '/');

        if (is_dir($destination)) {
            $newFileName = (new \ReflectionClass($source))->getShortName();
            $destination = $destination . DIRECTORY_SEPARATOR . $newFileName . self::DESTINATION_FILE_EXTENSION;
        }

        return $destination;
    }

    /**
     * @param $source
     * @param $destination
     * @throws \ReflectionException
     */
    private function copyConstantsToDestination($source, $destination)
    {
        $reflectionClass = new \ReflectionClass($source);
        $constantName = $reflectionClass->getShortName();

        $generatedJsConstants = $this->getJsKeyValuePairFromConstants($reflectionClass->getConstants());

        $fileContent = <<<EOD
export const $constantName = {
$generatedJsConstants
};

EOD;

        $this->fileService->put($destination, $fileContent);
    }

    /**
     * @param array $constants
     * @return string
     */
    private function getJsKeyValuePairFromConstants(array $constants): string
    {
        $result = '';
        $indentationCharacter = self::INDENTATION_CHARACTER;

        foreach ($constants as $key => $value) {
            $result .= "$indentationCharacter$key: '$value'," . PHP_EOL;
        }
        $result = rtrim($result, PHP_EOL);
        return $result;
    }
}