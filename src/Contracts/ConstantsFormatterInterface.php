<?php

namespace Therealsmat\Contracts;

interface ConstantsFormatterInterface
{
    public function setConstants(string $title, array $constants);
    public function format(): string;
}