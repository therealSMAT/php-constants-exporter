<?php

namespace Therealsmat;

use Exception;
use ReflectionClass;
use Therealsmat\Services\FileHelper;
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
     * @var FileHelper
     */
    private $fileHelper;

    /**
     * @var ConstantsFormatterInterface
     */
    private $constantsFormatter;

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
        $this->fileHelper = new FileHelper;
        $this->constantsFormatter = new JsConstantsFormatter;
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
            } catch (Exception $e)
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

        if ($this->fileHelper->isDir($destination)) {
            $newFileName = (new ReflectionClass($source))->getShortName();
            $destination = $destination . DIRECTORY_SEPARATOR . $newFileName . self::DESTINATION_FILE_EXTENSION;
        }

        return $destination;
    }

    /**
     * @param string $source
     * @param string $destination
     * @throws \ReflectionException
     */
    private function copyConstantsToDestination(string $source, string $destination)
    {
        $reflectionClass = new ReflectionClass($source);
        $constants = $this->getReflectedClassConstants($reflectionClass);
        $constantName = $reflectionClass->getShortName();

        $generatedJsConstants = $this->constantsFormatter
            ->setConstants($constantName, $constants)
            ->format();


        if (!$this->destinationHasSameConstant($constantName, $destination)) {
            $this->fileHelper->put($destination, $generatedJsConstants, FILE_APPEND);
            return;
        }

        $this->fileHelper->put(
            $destination,
            preg_replace(
                $this->getRegexPattern($constantName),
                $generatedJsConstants,
                $this->fileHelper->readContents($destination)
            )
        );
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

    /**
     * @param string $constantName
     * @param string $filePath
     * @return bool
     */
    private function destinationHasSameConstant(string $constantName, string $filePath): bool
    {
        if (!$this->fileHelper->fileExists($filePath)) {
            return false;
        }

        preg_match(
            $this->getRegexPattern($constantName),
            $this->fileHelper->readContents($filePath),
            $matches
        );

        return !empty($matches);
    }

    /**
     * @param string $constantName
     * @return string
     */
    private function getRegexPattern(string $constantName): string
    {
        return "/export const $constantName = {([^}]+)};\n/";
    }
}