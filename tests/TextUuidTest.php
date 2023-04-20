<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextUuidTest extends TestCase
{
    public function testUuid()
    {
        $uuid = Text::uuid();
        $this->assertIsString($uuid, 'UUID not a string');
        $this->assertEquals(36, strlen($uuid), 'UUID is not a 36-character length');
    }
}
