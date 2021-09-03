<?php

namespace Smart\EtlBundle\Tests\Utils;

use Smart\EtlBundle\Utils\StringUtils;
use PHPUnit\Framework\TestCase;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * vendor/bin/phpunit tests/Utils/StringUtilsTest.php
 */
class StringUtilsTest extends TestCase
{
    /**
     * @dataProvider countRowsProvider
     * @param int $expected
     * @param string $values
     */
    public function testCountRows(int $expected, string $values): void
    {
        $this->assertSame($expected, StringUtils::countRows($values));
    }

    public function countRowsProvider(): array
    {
        return [
            'count_multiple_rows' => [
                // expected
                3,
                // values
                "some
                exemple
                text"
            ],
            'count_empty_row' => [
                5,
                "some
                
                text
                
                "
            ],
            'count_one_row' => [1, ""],
        ];
    }
}
