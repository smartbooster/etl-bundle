<?php

namespace Smart\EtlBundle\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayHeaderConsistencyException;
use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayNbMaxRowsException;
use Smart\EtlBundle\Utils\ArrayUtils;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * vendor/bin/phpunit tests/Utils/ArrayUtilsTest.php
 */
class ArrayUtilsTest extends TestCase
{
    const DEFAULT_EXPECTED_MULTI_ARRAY = [
        ['email' => 'jean.dupond@test.fr', 'firstName' => 'Jean', 'height' => '2'],
        ['email' => 'marc.durand@test.fr', 'firstName' => 'Marc', 'height' => '1.80']
    ];

    public function testRemoveEmpty(): void
    {
        $this->assertSame([
            3 => [],
            4 => false,
            5 => '0',
            6 => 0,
            7 => "text"
        ], ArrayUtils::removeEmpty(['', "", null, [], false, '0', 0, "text"]));
    }

    /**
     * @dataProvider arrayFromStringProvider
     */
    public function testArrayFromString(array $expectedArray, ?string $string): void
    {
        $this->assertSame($expectedArray, ArrayUtils::getArrayFromString($string));
    }

    /**
     * @return array
     */
    public function arrayFromStringProvider(): array
    {
        return [
            'get_array_from_string' => [
                // expected
                ["some","exemple", "text"],
                // values
                "some
                exemple
                text"
            ],
            'get_array_from_string_with_delimeter_and_empty_rows' => [
                [0 => "some,", 2 => "exemple;", 4 => "text*"],
                "some,

                exemple;

                text*"
            ],
            'get_an_empty_array_from_empty_string' => [
                [],
                null
            ],
        ];
    }

    /**
     * @dataProvider multiArrayFromSimpleProvider
     */
    public function testGetMultiArrayFromString(array $expectedArray, string $string, array $options = []): void
    {
        $this->assertSame(
            $expectedArray,
            ArrayUtils::getMultiArrayFromString($string, ['email', 'firstName', 'height'], $options)
        );
    }

    public function multiArrayFromSimpleProvider(): array
    {
        return [
            'get_array_from_simple_string' => [
                self::DEFAULT_EXPECTED_MULTI_ARRAY,
                "jean.dupond@test.fr,Jean,2
                marc.durand@test.fr,Marc,1.80"
            ],
            'get_array_from_quoted_string' => [
                self::DEFAULT_EXPECTED_MULTI_ARRAY,
                '"jean.dupond@test.fr","Jean","2"
                "marc.durand@test.fr",Marc,1.80'
            ],
            'get_array_from_quoted_string_with_spaces' => [
                self::DEFAULT_EXPECTED_MULTI_ARRAY,
                '"jean.dupond@test.fr", "Jean", "2"
                "marc.durand@test.fr" , Marc ,1.80'
            ],
            'get_array_from_string_with_empty_line' => [
                [
                    0 => ['email' => 'jean.dupond@test.fr', 'firstName' => 'Jean', 'height' => '2'],
                    2 => ['email' => 'marc.durand@test.fr', 'firstName' => 'Marc', 'height' => '1.80'],
                ],
                "jean.dupond@test.fr,Jean,2

                marc.durand@test.fr,Marc,1.80",
            ],
            'get_array_from_string_with_empty_fields' => [
                [
                    ['email' => 'jean.dupond@test.fr', 'firstName' => 'Jean', 'height' => null],
                    ['email' => 'marc.durand@test.fr', 'firstName' => null, 'height' => '1.80'],
                ],
                "jean.dupond@test.fr,Jean,
                marc.durand@test.fr,,1.80"
            ],
            'get_array_from_simple_string_and_custom_delimiter' => [
                self::DEFAULT_EXPECTED_MULTI_ARRAY,
                "jean.dupond@test.fr;Jean;2
                marc.durand@test.fr;Marc;1.80",
                ['delimiter' => ';']
            ],
            'get_array_from_string_with_row_offset' => [
                [
                    ['email' => 'jean.dupond@test.fr', 'firstName' => null, 'height' => null],
                    ['email' => 'marc.durand@test.fr', 'firstName' => 'Marc', 'height' => '1.80'],
                ],
                "jean.dupond@test.fr
                marc.durand@test.fr,Marc,1.80,,,",
                ['fix_header_consistency' => true]
            ],
            'get_array_from_empty_' => [[], "",],
        ];
    }

    public function testGetMultiArrayFromStringNbMaxRowsException(): void
    {
        $this->expectExceptionObject(new MultiArrayNbMaxRowsException(2, 3));

        ArrayUtils::getMultiArrayFromString(
        "TEST_CODE_1
            TEST_CODE_2
            TEST_CODE_3",
            ['dummy_head'],
            ['nb_max_row' => 2]
        );
    }

    public function testGetMultiArrayFromStringConsistencyException(): void
    {
        $this->expectExceptionObject(new MultiArrayHeaderConsistencyException([2, 3]));

        ArrayUtils::getMultiArrayFromString(
            "jean,dupond
            marc
            bruce,wayne,batman
            clark,kent",
            ['firstName', 'lastName'],
        );
    }
}
