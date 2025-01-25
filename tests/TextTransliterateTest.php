<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextTransliterateTest extends TestCase
{
    public function testTransliterate()
    {
        if(extension_loaded('intl')) {
            $this->assertEquals('Moskva', Text::transliterate('Москва'));
            $this->assertEquals("Strasse", Text::transliterate('Straße'));
        }
    }
}
