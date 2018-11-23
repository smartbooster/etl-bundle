<?php

namespace Smart\EtlBundle\Extractor;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CsvEntityExtractor extends AbstractFolderExtrator implements ExtractorInterface
{
    use EntityExtractorTrait;

    /**
     * @inheritDoc
     */
    protected function getFileExtension()
    {
        return 'csv';
    }

    /**
     * @inheritDoc
     */
    protected function extractFileContent($filepath)
    {
        $file = new \SplFileObject($filepath);

        //csv file have to be formatted with headers on the first line
        $headers = $file->fgetcsv();

        $data = [];
        while (!$file->eof()) {
            $data[] = array_combine($headers, $file->fgetcsv());
        }

        return $data;
    }
}
