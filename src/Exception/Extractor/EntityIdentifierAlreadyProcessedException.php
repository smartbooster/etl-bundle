<?php

namespace Smart\EtlBundle\Exception\Extractor;

/**
 * Exception thrown when the entity identifier had already been added to the process entities
 *
 * @author Mathieu Ducrot <mathieu.ducrot@pia-production.fr>
 */
class EntityIdentifierAlreadyProcessedException extends ExtractException
{
    /**
     * @inheritdoc
     */
    protected $message = 'EXTRACTOR : Entity with identifier "%s" already processed';
}
