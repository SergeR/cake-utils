<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Hash;
use PHPUnit\Framework\TestCase;

class HashExpandTest extends TestCase
{
    public function testExpand()
    {
        $arr = [
            'user.id'      => 1,
            'user.name'    => 'Pavel',
            'user.group.0' => 'admin',
            'user.group.1' => 'root',
            'user.group.2' => 'friends',
        ];
        $expected = [
            'user' => [
                'id'    => 1,
                'name'  => 'Pavel',
                'group' => ['admin', 'root', 'friends']
            ]
        ];

        $this->assertEquals($expected, Hash::expand($arr));
    }
}
