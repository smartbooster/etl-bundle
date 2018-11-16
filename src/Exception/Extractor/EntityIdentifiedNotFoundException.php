<?php

namespace Smart\EtlBundle\Exception\Extractor;

/**
 * Exception thrown when the entity is not found by his identifier
 *
 * @author Mathieu Ducrot <mathieu.ducrot@pia-production.fr>
 */
class EntityIdentifiedNotFoundException extends ExtractException
{
    /**
     * @param string $identifier
     *
     * @return EntityIdentifiedNotFoundException
     */
    public static function create($identifier)
    {
        return new self(
            sprintf(
                'Entity identifed by ' . $identifier . ' not found',
                $identifier
            )
        );
    }
}
