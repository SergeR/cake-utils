<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorVariableTest extends TestCase
{
    public function testVariable()
    {
        Inflector::reset();
        $this->assertEquals('userDefinedArray', Inflector::variable('user_defined_array'));
    }
}
