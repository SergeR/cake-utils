<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextToListTest extends TestCase
{
    public function testToList()
    {
        $list = ['one', 'two', 'three'];

        $expected = "one, two and three";
        $this->assertEquals($expected, Text::toList($list));

        $expected = "one, two or three";
        $this->assertEquals($expected, Text::toList($list, 'or'));

        $expected = "one?two or three";
        $this->assertEquals($expected, Text::toList($list, 'or', '?'));
    }
}
