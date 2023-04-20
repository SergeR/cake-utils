<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextIsMultibyteTest extends TestCase
{
    public function testIsMultibyte()
    {
        $this->assertTrue(Text::isMultibyte("Мультибайт"));
        $this->assertFalse(Text::isMultibyte("Multibyte"));
    }
}
