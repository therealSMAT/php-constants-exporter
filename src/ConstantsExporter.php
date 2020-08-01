<?php

namespace Therealsmat;

use \ReflectionClass;
use Therealsmat\Services\FileService;
use Therealsmat\Contracts\ConstantsFormatterInterface;
use Therealsmat\Exceptions\ConstantsNotExportedException;

/**
 * Class ConstantsExporter
 * @package Therealsmat
 */
class ConstantsExporter
{
    const DESTINATION_FILE_EXTENSION = '.js';

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var ConstantsFormatterInterface
     */
    private $constantsExporter;

    /**
     * @var array
     */
    private $constantsToExport = [];

    /**
     * @var bool
     */
    private $shouldExcludeParentConstants = false;

    /**
     * ConstantsExporter constructor.
     * @param array $constantsToExport
     */
    public function __construct(array $constantsToExport = [])
    {
        $this->constantsToExport = $constantsToExport;
        $this->fileService = new FileService;
        $this->constantsExporter = new JsConstantsFormatter;
    }

    /**
     * @throws ConstantsNotExportedException
     */
    public function __destruct()
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
     * @return $this
     */
    public function excludingParentConstants(): self
    {
        $this->shouldExcludeParentConstants = true;
        return $this;
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
            $newFileName = (new ReflectionClass($source))->getShortName();
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
        $reflectionClass = new ReflectionClass($source);
        $constants = $this->getReflectedClassConstants($reflectionClass);

        $generatedJsConstants = $this->constantsExporter
            ->setConstants($reflectionClass->getShortName(), $constants)
            ->format();

        $this->fileService->put($destination, $generatedJsConstants);
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    private function getReflectedClassConstants(ReflectionClass $reflectionClass): array
    {
        if ($this->shouldExcludeParentConstants === false) {
            return $reflectionClass->getConstants();
        }

        return array_diff(
            $reflectionClass->getConstants(),
            $this->getAncestorsConstants($reflectionClass->getParentClass())
        );
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return array
     */
    private function getAncestorsConstants(ReflectionClass $reflectionClass): array
    {
        $constants = [];

        $constants = array_merge($constants, $reflectionClass->getConstants());

        if ($reflectionClass->getParentClass() !== false) {
            $this->getAncestorsConstants($reflectionClass->getParentClass());
        }

        return $constants;
    }
}