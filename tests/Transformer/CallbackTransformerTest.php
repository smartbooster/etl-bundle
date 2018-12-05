<?php

namespace Smart\EtlBundle\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Transformer\CallbackTransformer;

/**
 * vendor/bin/phpunit tests/Transformer/CallbackTransformerTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CallbackTransformerTest extends TestCase
{
    public function testTransform()
    {
        $transformer = new CallbackTransformer(function ($data) {
            foreach ($data as $key => $value) {
                if ($key != 'to_transform') {
                    unset($data[$key]);
                } else {
                    $data[$key] = strtolower($value);
                }
            }

            return $data;
        });

        $data = [
            'dummy_key' => 'Dummy value',
            'to_transform' => ' To transfORM Value',
            'another_dummy_key' => 'Another Dummy KEY'
        ];

        $this->assertEquals(['to_transform' => ' to transform value'], $transformer->transform($data));
    }
}
