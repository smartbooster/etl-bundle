<?php

namespace Smart\EtlBundle\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Transformer\IconvTransformer;

/**
 * vendor/bin/phpunit tests/Transformer/IconvTransformerTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class IconvTransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new IconvTransformer('iso-8859-1', 'UTF-8');

        $utf8String = 'éçà@`èù?!';
        $isoString = iconv('UTF-8', 'iso-8859-1', $utf8String);

        $this->assertEquals([$utf8String], $transformer->transform([$isoString]));
    }
}
