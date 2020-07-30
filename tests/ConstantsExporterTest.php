<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Therealsmat\ConstantsExporter;
use Therealsmat\Services\FileService;
use Therealsmat\Exceptions\ConstantsNotExportedException;

class ConstantsExporterTest extends TestCase
{
    const FILE_DIR = 'js' . DIRECTORY_SEPARATOR;

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }

    public function testConstantsAreExportedToCorrectPaths()
    {
        $mock = \Mockery::mock('overload:' . FileService::class);
        $mock->allows()->put(SimpleConstantsStub::$jsFileName, SimpleConstantsStub::content())
            ->once()->andReturn(\Mockery::type('int'));

        try {
            $performed = (new ConstantsExporter())->setConstantsToExport([
                SimpleConstantsStub::class => self::FILE_DIR
            ])->perform();

            $this->assertTrue($performed);
        } catch (ConstantsNotExportedException $e) {
            var_dump($e->getMessage());
        }
    }

    public function testConstantsCanBePassedFromTheConstructor()
    {
        $mock = \Mockery::mock('overload:' . FileService::class);
        $mock->allows()->put(SimpleConstantsStub::$jsFileName, SimpleConstantsStub::content())
            ->once()->andReturn(\Mockery::type('int'));

        try {
            $performed = (new ConstantsExporter([
                SimpleConstantsStub::class => self::FILE_DIR
            ]))->perform();

            $this->assertTrue($performed);
        } catch (ConstantsNotExportedException $e) {
            var_dump($e->getMessage());
        }
    }
}


class SimpleConstantsStub
{
    const ONE = 1;
    const TWO = 2;
    const THREE = 3;
    const FOUR = 4;
    const FIVE = 5;

    public static $jsFileName = ConstantsExporterTest::FILE_DIR . 'SimpleConstantsStub.js';

    public static function content()
    {
        return "export const SimpleConstantsStub = {
    ONE: '1',
    TWO: '2',
    THREE: '3',
    FOUR: '4',
    FIVE: '5',
};
";
    }
}
