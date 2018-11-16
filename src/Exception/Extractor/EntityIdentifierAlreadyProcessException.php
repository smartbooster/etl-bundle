<?php

namespace Smart\EtlBundle\Exception\Extractor;

/**
 * Exception thrown when the entity identifier had already been added to the process entities
 *
 * @author Mathieu Ducrot <mathieu.ducrot@pia-production.fr>
 */
class EntityIdentifierAlreadyProcessException extends ExtractException
{
    /**
     * @param string $entityType
     *
     * @return EntityIdentifierAlreadyProcessException
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
