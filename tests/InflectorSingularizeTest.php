<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorSingularizeTest extends TestCase
{
    public function testSingularize()
    {
        Inflector::reset();
        $this->assertEquals('word', Inflector::singularize('words'));
        $this->assertNotEquals('музыкант', Inflector::singularize('музыканты'));
    }
}
