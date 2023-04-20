<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextTailTest extends TestCase
{
    public function testTail()
    {
        $text = 'Москва — столица России, город федерального значения, административный центр Центрального федерального округа и центр Московской области, в состав которой не входит. Крупнейший по численности населения город России и её субъект — 13097539 человек, самый населённый из городов, полностью расположенных в Европе, занимает 22-е место среди городов мира по численности населения. Центр Московской городской агломерации. Самый крупный город Европы по площади';

        $expected = '...ленности населения. Центр Московской городской агломерации. Самый крупный город Европы по площади';
        $this->assertEquals($expected, Text::tail($text));

        $expected = '...населения. Центр Московской городской агломерации. Самый крупный город Европы по площади';
        $this->assertEquals($expected, Text::tail($text, options: ['exact' => false]));

        $expected = '...ерации. Самый крупный город Европы по площади';
        $this->assertEquals($expected, Text::tail($text, 48));

        $expected = '...Самый крупный город Европы по площади';
        $this->assertEquals($expected, Text::tail($text, 48, ['exact' => false]));

        $expected = '…Самый крупный город Европы по площади';
        $this->assertEquals($expected, Text::tail($text, 48, ['exact' => false, 'ellipsis'=>'…']));
    }
}
