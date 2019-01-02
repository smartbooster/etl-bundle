<?php

namespace Smart\EtlBundle\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;
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

    /**
     * @var Tag[]
     */
    protected $tags;

    public function __construct(Project $project = null, $name = null)
    {
        $this->project = $project;
        $this->name = $name;
        $this->tags = new ArrayCollection();
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

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag[] $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        if ($this->tags->contains($tag)) {
            return;
        }
        $this->tags->add($tag);
    }

    /**
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            return;
        }
        $this->tags->removeElement($tag);
    }
}
