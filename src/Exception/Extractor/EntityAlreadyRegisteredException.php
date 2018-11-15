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
     * @inheritdoc
     */
    protected $message = 'EXTRACTOR : Entity type "%s" already registered';
}
