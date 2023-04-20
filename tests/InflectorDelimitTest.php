<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorDelimitTest extends TestCase
{
    public function testDelimit()
    {
        Inflector::reset();
        $this->assertEquals('a_delimited_string', Inflector::delimit('ADelimitedString'));
        $this->assertEquals('another-delimited-string', Inflector::delimit('AnotherDelimitedString', '-'));
    }
}
