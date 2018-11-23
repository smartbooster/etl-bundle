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
     * @inheritdoc
     */
    protected $message = 'EXTRACTOR : Entity identifed by "%s" not found';
}
