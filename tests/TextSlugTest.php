<?php
/**
 * @author Serge Rodovnichenko <serge@syrnik.com>
 * @copyright Serge Rodovnichenko, 2023
 * @license
 */

use SergeR\CakeUtility\Text;
use PHPUnit\Framework\TestCase;

class TextSlugTest extends TestCase
{
    public function testSlug()
    {
        $this->assertEquals('Moskva', Text::slug('Москва'));
        $this->assertEquals('Moskva-Rossia', Text::slug('Москва, Россия'));
        $this->assertEquals('Moskva_Rossia', Text::slug('Москва, Россия', ['replacement' => '_']));
        $this->assertEquals('Moskva_Rossia.html', Text::slug('Москва, Россия.html', ['replacement' => '_', 'preserve' => '.']));
    }
}
