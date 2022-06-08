<?php

namespace Smart\EtlBundle\Utils;

use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayHeaderConsistencyException;
use Smart\EtlBundle\Exception\Utils\ArrayUtils\MultiArrayNbMaxRowsException;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class ArrayUtils
{
    /**
     * Clean data from an array
     * - remove null value and empty string
     */
    public static function removeEmpty(array $array): array
    {
        return array_filter($array, function ($item) {
            return $item !== null && (!is_string($item) || $item !== '');
        });
    }

    /**
     * Convert and clean data from string to array
     */
    public static function getArrayFromString(?string $string): array
    {
        $toReturn = explode(PHP_EOL, $string);
        $toReturn = array_map(function ($row) {
            return trim($row);
        }, $toReturn);

        return array_unique(self::removeEmpty($toReturn));
    }

    /**
     * Convert and clean data from string to a multidimensional array
     *
     * @param array $options list all options to configure the transform behavior. Current available options :
     *  - delimiter (string) delimiter used to explode the row data
     *  - nb_max_row (integer) maximum limit number of row allowed in the data content to import
     *  - fix_header_consistency (boolean) allow to fix header consistency error
     * @throws MultiArrayNbMaxRowsException
     * @throws MultiArrayHeaderConsistencyException
     */
    public static function getMultiArrayFromString(string $string, array $header, array $options = []): array
    {
        $nbRows = StringUtils::countRows($string);
        if (isset($options['nb_max_row']) && $nbRows > $options['nb_max_row']) {
            throw new MultiArrayNbMaxRowsException($options['nb_max_row'], $nbRows);
        }

        $toReturn = self::getArrayFromString($string);
        $nbHeader = count($header);
        $headerConsistencyError = [];

        // options config
        $delimiterOption = $options['delimiter'] ?? ',';
        $fixHeaderConsistencyOption = (isset($options['fix_header_consistency']) && $options['fix_header_consistency']);

        foreach ($toReturn as $key => $row) {
            $rowData = str_getcsv($row, $delimiterOption);

            // @todo extract in method mapTrimNull
            $rowData = array_map(function ($data) {
                if ($data !== null) {
                    $data = trim($data);

                    if ($data === '') {
                        $data = null;
                    }
                }

                return $data;
            }, $rowData);

            $validRowConsistency = true;
            if ($fixHeaderConsistencyOption) {
                // @todo extract in method syncHeader
                // fill missing field on the row with null values
                $rowData = array_pad($rowData, $nbHeader, null);
                // slice extra data on the row
                $rowData = array_slice($rowData, 0, $nbHeader);
            } else {
                if (count($rowData) != $nbHeader) {
                    $headerConsistencyError[] = $key + 1;
                    $validRowConsistency = false;
                }
            }

            // combine to form the multidimensional array
            if ($validRowConsistency) {
                $toReturn[$key] = array_combine($header, $rowData);
            }
        }

        if (count($headerConsistencyError) > 0) {
            throw new MultiArrayHeaderConsistencyException($headerConsistencyError);
        }

        return $toReturn;
    }

    /**
     * Add value in the array of values of key
     */
    public static function addMultidimensionalArrayValue(array $array, string $key, $value): array
    {
        if (!isset($array[$key])) {
            $array[$key] = [];
        }
        array_push($array[$key], $value);

        return $array;
    }
}
