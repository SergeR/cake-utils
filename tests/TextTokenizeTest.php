<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextTokenizeTest extends TestCase
{
    public function testTokenize()
    {
        $tokens = Text::tokenize('text,tokenize');
        $expected = ['text', 'tokenize'];
        self::assertEquals($expected, $tokens);

        $tokens = Text::tokenize("text, tokenize");
        self::assertEquals($expected, $tokens);

        $tokens = Text::tokenize('text.tokenize', '.');
        self::assertEquals($expected, $tokens);

        $tokens = Text::tokenize("text, tokenize (blue, green)");
        $expected = ['text', 'tokenize (blue, green)'];
        self::assertEquals($expected, $tokens);

        $tokens = Text::tokenize("params.[shipping.carrier_id]", '.', '[', ']');
        $expected = ['params', '[shipping.carrier_id]'];
        self::assertEquals($expected, $tokens);

    }
}
