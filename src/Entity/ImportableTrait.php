<?php

namespace Smart\EtlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
trait ImportableTrait
{
    /**
     * Store id for remote source, use this as identifiant to make reference in import scripts.
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true, unique=true)
     */
    protected $importId;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $importedAt;

    /**
     * @return string
     */
    public function getImportId()
    {
        return $this->importId;
    }

    /**
     * @param string $importId
     */
    public function setImportId($importId)
    {
        $this->importId = $importId;
    }

    /**
     * @return \DateTime
     */
    public function getImportedAt()
    {
        return $this->importedAt;
    }

    /**
     * @param \DateTime $importedAt
     */
    public function setImportedAt($importedAt)
    {
        $this->importedAt = $importedAt;
    }

    /**
     * @return bool
     */
    public function isImported()
    {
        return $this->getImportedAt() != null;
    }
}
