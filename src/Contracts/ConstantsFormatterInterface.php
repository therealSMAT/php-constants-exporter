<?php

namespace Therealsmat\Contracts;

interface ConstantsFormatterInterface
{
    public function setConstants(string $title, array $constants): self;
    public function format(): string;
}