<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextParseFileSizeTest extends TestCase
{
    public function testParseFileSize()
    {
        $this->assertEquals(2, Text::parseFileSize('2'));
        $this->assertEquals(2, Text::parseFileSize('2B'));
        $this->assertEquals(2, Text::parseFileSize('2b'));
        $this->assertEquals(2048, Text::parseFileSize('2K'));
        $this->assertEquals(2048, Text::parseFileSize('2KB'));
        $this->assertEquals(2 * pow(1024, 2), Text::parseFileSize('2MB'));
        $this->assertEquals(2 * pow(1024, 2), Text::parseFileSize('2M'));
        $this->assertEquals(2 * pow(1024, 3), Text::parseFileSize('2G'));
        $this->assertEquals(2 * pow(1024, 3), Text::parseFileSize('2GB'));
        $this->assertEquals(null, Text::parseFileSize('BAD', null));
        $this->assertEquals(-1, Text::parseFileSize('BAD', -1));
    }

    public function testException()
    {
        $this->expectException(InvalidArgumentException::class);
        Text::parseFileSize('BAD');
    }
}
