<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Hash;
use PHPUnit\Framework\TestCase;

class HashCheckTest extends TestCase
{
    public function testCheck()
    {
        $arr = ['alpha' => ['beta' => ['gamma' => 'centauri']]];
        $this->assertTrue(Hash::check($arr, 'alpha.beta.gamma'));
        $this->assertFalse(Hash::check($arr, 'alpha.beta.omega'));
    }
}
