<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Hash;
use PHPUnit\Framework\TestCase;

class HashMaxDimensionsTest extends TestCase
{
    /**
     *
     * @dataProvider datasets
     */
    public function testMaxDimensions($expected, $arr)
    {
        $this->assertEquals($expected, Hash::maxDimensions($arr));
    }

    public function datasets(): array
    {
        return [
            [0, []],
            [1, [1]],
            [1, [0]],
            [2, [[1]]],
            [2, [0, [1]]],
        ];
    }
}
