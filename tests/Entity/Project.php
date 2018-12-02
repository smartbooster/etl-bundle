<?php

namespace Smart\EtlBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Smart\EtlBundle\Tests\Model\Project as ProjectModel;

/**
 * @ORM\Entity()
 * @ORM\Table(name="etlbundle_project")
 */
class Project extends ProjectModel
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
     * @var Organisation
     * @ORM\ManyToOne(targetEntity="Smart\EtlBundle\Tests\Entity\Organisation", inversedBy="projects")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $organisation;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Smart\EtlBundle\Tests\Entity\Task", mappedBy="project")
     */
    protected $tasks;
}
