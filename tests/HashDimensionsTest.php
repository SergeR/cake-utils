<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Hash;
use PHPUnit\Framework\TestCase;

class HashDimensionsTest extends TestCase
{
    public function testDimensions()
    {
        $this->assertEquals(0, Hash::dimensions([]));
        $this->assertEquals(1, Hash::dimensions([0]));
        $this->assertEquals(2, Hash::dimensions([[0]]));
        $this->assertEquals(1, Hash::dimensions([0, 1]));
        $this->assertEquals(1, Hash::dimensions([0, 1 => []]));
        $this->assertEquals(2, Hash::dimensions([0 => [], 1]));
        $this->assertEquals(2, Hash::dimensions([0 => [0], 1]));
    }
}
