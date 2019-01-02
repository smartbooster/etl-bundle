<?php

namespace Smart\EtlBundle\Tests\Model;

use Smart\EtlBundle\Entity\ImportableInterface;
use Smart\EtlBundle\Entity\ImportableTrait;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class Tag implements ImportableInterface
{
    use ImportableTrait;

    /**
     * @var string
     */
    protected $name;

    public function __construct($name = null, $importId = null)
    {
        $this->name = $name;
        $this->importId = $importId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
