<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Hash;
use PHPUnit\Framework\TestCase;

class HashGetTest extends TestCase
{
    public function testGet()
    {
        $this->assertNull(Hash::get([], 0));

        $arr = ['alpha' => ['beta' => ['gamma' => 'centauri']]];
        $this->assertEquals('centauri', Hash::get($arr, 'alpha.beta.gamma'));
        $this->assertEquals('none', Hash::get($arr, 'alpha.beta.omega', 'none'));
        $this->assertNull(Hash::get($arr, 'alpha.beta.omega'));

        $arr = [null => 'is_null'];
        $this->assertEquals('is_null', Hash::get($arr, null));

        $arr = [true => 'is_null'];
        $this->assertEquals('is_null', Hash::get($arr, true));
        $arr = [false => 'is_null'];
        $this->assertEquals('is_null', Hash::get($arr, false));

        $arr = [['alpha', 'beta', ['gamma' => 'centauri']]];
        $this->assertEquals('centauri', Hash::get($arr, '0.2.gamma'));
        $this->assertEquals('centauri', Hash::get($arr, [0, 2, 'gamma']));
    }

    public function testException()
    {
        $this->expectException(InvalidArgumentException::class);
        Hash::get(['f'], (object)['lala' => 1]);
    }
}
