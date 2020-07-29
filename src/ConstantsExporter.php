<?php

namespace Therealsmat;

class ConstantsExporter
{
    private array $constantsSourceToDestination = [];
    private string $destinationFileExt = '.js';
    private string $indentationCharacter = '    '; // 4 spaces instead of a tab

    public function setConstantsSourceToDestination(array $constantsSourceToDestination): self
    {
        $this->constantsSourceToDestination = $constantsSourceToDestination;
        return $this;
    }

    public function perform()
    {
        foreach ($this->constantsSourceToDestination as $source => $destination) {
            try {
                $destinationFilePath = $this->getDestinationFilePath($source, $destination);
                $this->copyConstantsToDestination($source, $destinationFilePath);
            } catch (\Exception $e)
            {
                var_dump($e->getMessage());
            }
        }
    }

    private function getDestinationFilePath(string $source, string $destination): string
    {
        $destination = rtrim($destination, '/');

        if (is_dir($destination)) {
            $newFileName = (new \ReflectionClass($source))->getShortName();
            $destination = $destination . DIRECTORY_SEPARATOR . $newFileName . $this->destinationFileExt;
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

        file_put_contents($destination, $fileContent, FILE_APPEND);
    }

    private function getJsKeyValuePairFromConstants(array $constants): string
    {
        $result = '';
        $indentationCharacter = $this->indentationCharacter;

        foreach ($constants as $key => $value) {
            $result .= "$indentationCharacter$key: '$value'," . PHP_EOL;
        }
        $result = rtrim($result, PHP_EOL);
        return $result;
    }
}