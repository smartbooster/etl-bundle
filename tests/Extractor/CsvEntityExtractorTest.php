<?php

namespace Smart\EtlBundle\Tests\Extractor;

use Smart\EtlBundle\Extractor\CsvEntityExtractor;

/**
 * vendor/bin/phpunit tests/Extractor/CsvEntityExtractorTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CsvEntityExtractorTest extends AbstractEntityExtractorTest
{
    /**
     * @inheritdoc
     */
    protected function getExtractor()
    {
        $extractor = new CsvEntityExtractor();
        $extractor->setFolderToExtract(__DIR__ . '/../fixtures/entity-csv');

        return $extractor;
    }
}
