<?php

namespace Smart\EtlBundle\Utils;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class StringUtils
{
    public static function countRows(string $string): int
    {
        return substr_count($string, PHP_EOL) + 1;
    }
}
