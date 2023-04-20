<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Inflector;
use PHPUnit\Framework\TestCase;

class InflectorHumanizeTest extends TestCase
{
    public function testHumanize()
    {
        Inflector::reset();

        $this->assertEquals('A Human Readable String', Inflector::humanize('a_human_readable_string'));
        $this->assertEquals('A Human Readable Words', Inflector::humanize('a-human-readable-words', '-'));
    }
}
