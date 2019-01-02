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
     * @ORM\ManyToMany(targetEntity="Smart\EtlBundle\Tests\Entity\Tag")
     * @ORM\JoinTable(name="etlbundle_task_tag",
     *      joinColumns={@ORM\JoinColumn(name="task_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    protected $tags;

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
    
    /**
     * @return string
     */
    public function getImportId()
    {
        if (is_null($this->importId)) {
            return $this->getCode();
        }

        return $this->importId;
    }
}
