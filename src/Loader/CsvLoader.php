<?php

namespace Smart\EtlBundle\Loader;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CsvLoader extends AbstractFileLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    protected $fileExtension = 'csv';

    /**
     * @inheritDoc
     */
    public function load(array $data)
    {
        foreach ($data as $filename => $fileData) {
            $this->processFile($filename, $fileData);
        }
    }

    /**
     * @param string $filename
     * @param array $data
     */
    protected function processFile($filename, $data)
    {
        $filepath = $this->getFolderToLoad() . DIRECTORY_SEPARATOR . $filename . '.' . $this->fileExtension;
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0700, true);
        }
        $fp = fopen($filepath, 'w');

        //write headers
        fputcsv($fp, array_keys($data[0]), ',', '"');
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
}
