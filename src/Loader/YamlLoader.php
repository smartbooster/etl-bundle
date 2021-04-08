<?php

namespace Smart\EtlBundle\Loader;

use Symfony\Component\Yaml\Yaml;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class YamlLoader extends AbstractFileLoader implements LoaderInterface
{
    /**
     * @inheritDoc
     */
    protected $fileExtension = 'yml';

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

        file_put_contents($filepath, Yaml::dump($data, 3));
    }
}
