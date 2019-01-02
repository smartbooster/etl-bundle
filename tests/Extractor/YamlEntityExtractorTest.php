<?php

namespace Smart\EtlBundle\Tests\Extractor;

use Smart\EtlBundle\Extractor\YamlEntityExtractor;

/**
 * vendor/bin/phpunit tests/Extractor/YamlEntityExtractorTest.php
 *
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class YamlEntityExtractorTest extends AbstractEntityExtractorTest
{
    /**
     * @inheritdoc
     */
    protected function getExtractor()
    {
        $extractor = new YamlEntityExtractor();
        $extractor->setFolderToExtract(__DIR__ . '/../fixtures/entity-yaml');

        return $extractor;
    }
}
