<?php

namespace Therealsmat;

use Therealsmat\Contracts\ConstantsFormatterInterface;

class JsConstantsFormatter implements ConstantsFormatterInterface
{
    const INDENTATION_CHARACTER = '    '; // 4 spaces instead of a tab

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $constants;

    /**
     * @param string $title
     * @param array $constants
     * @return $this
     */
    public function setConstants(string $title, array $constants): self
    {
        $this->title = $title;
        $this->constants = $constants;
        return $this;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        $generatedJsConstants = $this->getJsKeyValuePairFromConstants();
        $constantName = $this->title;

        return <<<EOD
export const $constantName = {
$generatedJsConstants
};

EOD;
    }

    /**
     * @return string
     */
    private function getJsKeyValuePairFromConstants(): string
    {
        $result = '';
        $indentationCharacter = self::INDENTATION_CHARACTER;

        foreach ($this->constants as $key => $value) {
            $result .= "$indentationCharacter$key: '$value'," . PHP_EOL;
        }
        $result = rtrim($result, PHP_EOL);
        return $result;
    }
}