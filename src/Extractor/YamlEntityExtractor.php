<?php

namespace Smart\EtlBundle\Extractor;

use Symfony\Component\Yaml\Yaml;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class YamlEntityExtractor extends AbstractFolderExtrator implements ExtractorInterface
{
    use EntityFileExtractorTrait;
    
    /**
     * @inheritDoc
     */
    protected function getFileExtension()
    {
        return 'yml';
    }

    /**
     * @inheritDoc
     */
    protected function extractFileContent($filepath)
    {
        return Yaml::parse(file_get_contents($filepath));
    }
}
