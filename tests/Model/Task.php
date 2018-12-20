<?php

namespace Smart\EtlBundle\Tests\Model;

use Smart\EtlBundle\Entity\ImportableInterface;
use Smart\EtlBundle\Entity\ImportableTrait;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class Task implements ImportableInterface
{
    use ImportableTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Project
     */
    protected $project;

    public function __construct(Project $project = null, $name = null)
    {
        $this->project = $project;
        $this->name = $name;
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

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Project $project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }
}
