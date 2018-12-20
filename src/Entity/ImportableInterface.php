<?php

namespace Smart\EtlBundle\Entity;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
interface ImportableInterface
{
    /**
     * @param string $importId
     */
    public function setImportId($importId);

    /**
     * @return string
     */
    public function getImportId();

    /**
     * @return \DateTime
     */
    public function getImportedAt();

    /**
     * @param \DateTime $importedAt
     */
    public function setImportedAt($importedAt);

    /**
     * @return bool
     */
    public function isImported();
}
