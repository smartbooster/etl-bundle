<?php

namespace Smart\EtlBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\EtlBundle\Entity\ImportableInterface;
use Smart\EtlBundle\Entity\ImportableTrait;
use Smart\EtlBundle\Tests\Model\Project;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="etlbundle_milestone")
 */
class Milestone implements ImportableInterface
{
    use ImportableTrait;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Smart\EtlBundle\Tests\Entity\Project")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull(groups={"smart_etl_loader"})
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
    public function getImportId()
    {
        if (is_null($this->importId)) {
            return $this->getName();
        }

        return $this->importId;
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
