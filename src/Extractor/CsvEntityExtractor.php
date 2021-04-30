<?php

namespace Smart\EtlBundle\Extractor;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CsvEntityExtractor extends AbstractFolderExtrator implements ExtractorInterface
{
    use EntityFileExtractorTrait;

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
        $nbHeaders = count($headers);

        $datas = [];
        while (!$file->eof()) {
            $csvData = $file->fgetcsv();
            if (count($csvData) != $nbHeaders) {
                continue;
            }
            //Handle many relations
            $csvData = array_map(function ($e) {
                if (strpos($e, '|') !== false) {
                    if (strlen($e) == 1) {
                        return [];
                    }
                    return explode('|', $e);
                }
                return $e;
            }, $csvData);

            $datas[] = array_combine($headers, $csvData);
        }

        return $datas;
    }
}
