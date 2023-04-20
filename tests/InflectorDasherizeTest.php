<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorDasherizeTest extends TestCase
{
    public function testDasherize()
    {
        Inflector::reset();

        $this->assertEquals('string-to-be-dasherized', Inflector::dasherize('StringToBeDasherized'));
    }
}
