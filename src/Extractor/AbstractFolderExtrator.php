<?php

namespace Smart\EtlBundle\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
abstract class AbstractFolderExtrator extends AbstractExtractor
{
    /**
     * @var string
     */
    protected $folderToExtract;

    /**
     * @var string
     */
    abstract protected function getFileExtension();

    /**
     * @param string $filepath
     * @return mixed
     */
    abstract protected function extractFileContent($filepath);

    /**
     * @return string
     */
    public function getFolderToExtract()
    {
        return $this->folderToExtract;
    }

    /**
     * @param string $folderToExtract
     */
    public function setFolderToExtract($folderToExtract)
    {
        $this->folderToExtract = $folderToExtract;
    }

    protected function check()
    {
        if (!is_dir($this->folderToExtract)) {
            throw new \BadMethodCallException('Invalid folder to extract : ' . $this->folderToExtract);
        }
    }

    /**
     * @param string $extension
     * @return array
     */
    protected function getFiles($extension)
    {
        $finder = new Finder();
        $finder->in($this->folderToExtract);
        $files = [];
        /* @var SplFileInfo $file */
        foreach ($finder->files()->name('*.' . $extension) as $file) {
            $files[] = substr($file->getFilename(), 0, -(strlen($extension) + 1));
        }

        return array_combine($files, $files);
    }
}
