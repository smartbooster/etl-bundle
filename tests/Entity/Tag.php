<?php

namespace Smart\EtlBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;
use Smart\EtlBundle\Tests\Model\Tag as TagModel;

/**
 * @ORM\Entity()
 * @ORM\Table(name="etlbundle_tag")
 */
class Tag extends TagModel
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
    protected $name;
}
