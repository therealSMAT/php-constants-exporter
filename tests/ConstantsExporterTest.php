<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use Therealsmat\ConstantsExporter;
use Therealsmat\Services\FileHelper;

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
        $this->mockFileHelper(SimpleConstantsStub::$jsFileName, SimpleConstantsStub::content());

        (new ConstantsExporter)
            ->setConstantsToExport([SimpleConstantsStub::class => self::FILE_DIR]);
        $this->assertTrue(true);
    }

    public function testConstantsCanBePassedFromTheConstructor()
    {
        $this->mockFileHelper(SimpleConstantsStub::$jsFileName, SimpleConstantsStub::content());

        new ConstantsExporter([SimpleConstantsStub::class => self::FILE_DIR]);
        $this->assertTrue(true);
    }

    public function testParentConstantsMayBeExcludedFromExportedConstants()
    {
        $this->mockFileHelper(ChildConstantsStub::$jsFileName, ChildConstantsStub::shallowContent());

        (new ConstantsExporter)
            ->setConstantsToExport([ChildConstantsStub::class => self::FILE_DIR])
            ->excludingParentConstants();
        $this->assertTrue(true);
    }

    public function testAncestorConstantsMayBeExcludedFromExportedConstants()
    {
        $this->mockFileHelper(DescendantConstantsStub::$jsFileName, DescendantConstantsStub::shallowContent());

        (new ConstantsExporter)
            ->setConstantsToExport([DescendantConstantsStub::class => self::FILE_DIR])
            ->excludingParentConstants();
        $this->assertTrue(true);
    }

    public function testItCopiesAllInheritedConstantsByDefault()
    {
        $this->mockFileHelper(DescendantConstantsStub::$jsFileName, DescendantConstantsStub::content());

        (new ConstantsExporter)
            ->setConstantsToExport([DescendantConstantsStub::class => self::FILE_DIR]);
        $this->assertTrue(true);
    }

    /**
     * @param string $filename
     * @param string $content
     */
    private function mockFileHelper(
        string $filename,
        string $content,
        bool $destinationisDir = true,
        bool $fileExists = false,
        int $putFlag = FILE_APPEND
    ) {
        $mock = Mockery::mock('overload:' . FileHelper::class);

        $mock->allows()->fileExists($filename)->once()->andReturn($fileExists);
        $mock->allows()->isDir(Mockery::type('string'))->once()->andReturn($destinationisDir);
        $mock->allows()->put($filename, $content, $putFlag)->once()->andReturn(Mockery::type('int'));
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

class ParentConstantsStub {
    const ONE = 1;
    const TWO = 2;
    const THREE = 3;
    const FOUR = 4;
    const FIVE = 5;
}

class ChildConstantsStub extends ParentConstantsStub {
    const SIX = 6;
    const SEVEN = 7;
    const EIGHT = 8;

    public static $jsFileName = ConstantsExporterTest::FILE_DIR . 'ChildConstantsStub.js';

    public static function content()
    {
        return "export const ChildConstantsStub = {
    ONE: '1',
    TWO: '2',
    THREE: '3',
    FOUR: '4',
    FIVE: '5',
    SIX: '6',
    SEVEN: '7',
    EIGHT: '8'
};
";
    }

    public static function shallowContent()
    {
        return "export const ChildConstantsStub = {
    SIX: '6',
    SEVEN: '7',
    EIGHT: '8',
};
";
    }
}

class FirstAncestorConstantsStub {
    const ONE = 1;
    const TWO = 2;
    const THREE = 3;
    const FOUR = 4;
    const FIVE = 5;
}

class SecondAncestorConstantsStub extends FirstAncestorConstantsStub {
    const SIX = 6;
    const SEVEN = 7;
    const EIGHT = 8;
    const NINE = 9;
    const TEN = 10;
}

class ThirdAncestorConstantsStub extends SecondAncestorConstantsStub
{
    const ELEVEN = 11;
    const TWELVE = 12;
    const THIRTEEN = 13;
    const FOURTEEN = 14;
    const FIFTEEN = 15;
}

class DescendantConstantsStub extends ThirdAncestorConstantsStub
{
    const SIXTEEN = 16;
    const SEVENTEEN = 17;

    public static $jsFileName = ConstantsExporterTest::FILE_DIR . 'DescendantConstantsStub.js';

    public static function content()
    {
        return "export const DescendantConstantsStub = {
    SIXTEEN: '16',
    SEVENTEEN: '17',
    ELEVEN: '11',
    TWELVE: '12',
    THIRTEEN: '13',
    FOURTEEN: '14',
    FIFTEEN: '15',
    SIX: '6',
    SEVEN: '7',
    EIGHT: '8',
    NINE: '9',
    TEN: '10',
    ONE: '1',
    TWO: '2',
    THREE: '3',
    FOUR: '4',
    FIVE: '5',
};
";
    }

    public static function shallowContent()
    {
        return "export const DescendantConstantsStub = {
    SIXTEEN: '16',
    SEVENTEEN: '17',
};
";
    }
}
