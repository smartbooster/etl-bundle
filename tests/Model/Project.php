<?php

namespace Smart\EtlBundle\Tests\Model;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class Project
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    public function __construct($code = null, $name = null)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
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
