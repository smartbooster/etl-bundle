<?php

namespace Smart\EtlBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\EtlBundle\Tests\Model\Task as TaskModel;

/**
 * @ORM\Entity()
 * @ORM\Table(name="etlbundle_task")
 */
class Task extends TaskModel
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $code;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Smart\EtlBundle\Tests\Entity\Project", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $project;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

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
}
