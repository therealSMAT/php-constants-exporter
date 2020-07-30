<?php

namespace Tests;

use Mockery;
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
        $mock = Mockery::mock('overload:' . FileService::class);

        $mock->allows()
            ->put(SimpleConstantsStub::$jsFileName, SimpleConstantsStub::content())
            ->once()
            ->andReturn(Mockery::type('int'));

        try {
            (new ConstantsExporter())->setConstantsToExport([SimpleConstantsStub::class => self::FILE_DIR]);

            $this->assertTrue(true);
        } catch (ConstantsNotExportedException $e) {
            //
        }
    }

    public function testConstantsCanBePassedFromTheConstructor()
    {
        $mock = Mockery::mock('overload:' . FileService::class);
        $mock->allows()
            ->put(SimpleConstantsStub::$jsFileName, SimpleConstantsStub::content())
            ->once()
            ->andReturn(Mockery::type('int'));

        try {
            new ConstantsExporter([SimpleConstantsStub::class => self::FILE_DIR]);
            $this->assertTrue(true);
        } catch (ConstantsNotExportedException $e) {
            var_dump($e->getMessage());
        }
    }
}

// Next, we test that it only copies constants from own class, and does not copy inherited constants...

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
