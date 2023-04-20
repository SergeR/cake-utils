<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorPluralizeTest extends TestCase
{
    public function testPluralize()
    {
        Inflector::reset();
        $this->assertEquals('words', Inflector::pluralize('word'));
    }
}
