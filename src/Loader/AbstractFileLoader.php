<?php

namespace Smart\EtlBundle\Loader;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
abstract class AbstractFileLoader
{
    /**
     * @var string
     */
    protected $folderToLoad;

    /**
     * @var string
     */
    protected $fileExtension;

    public function __construct($folderToLoad)
    {
        $this->folderToLoad = $folderToLoad;
    }

    /**
     * @return string
     */
    public function getFolderToLoad()
    {
        return $this->folderToLoad;
    }

    /**
     * @param string $folderToLoad
     */
    public function setFolderToLoad($folderToLoad)
    {
        $this->folderToLoad = $folderToLoad;
    }

    protected function check()
    {
        if (!is_dir($this->folderToLoad)) {
            throw new \BadMethodCallException('Invalid folder to load');
        }
    }
}
