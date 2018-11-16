<?php

namespace Smart\EtlBundle\Exception\Extractor;

/**
 * Exception thrown when the entity is already in the list of entities to extract
 *
 * @author Mathieu Ducrot <mathieu.ducrot@pia-production.fr>
 */
class EntityAlreadyRegisteredException extends ExtractException
{
    /**
     * @param string $entityType
     *
     * @return EntityAlreadyRegisteredException
     */
    public static function create($entityType)
    {
        return new self(
            sprintf(
                'Entity type "%s" already registered',
                $entityType
            )
        );
    }
}
