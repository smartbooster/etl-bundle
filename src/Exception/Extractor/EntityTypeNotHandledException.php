<?php

namespace Smart\EtlBundle\Exception\Extractor;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class EntityTypeNotHandledException extends ExtractException
{
    /**
     * @inheritdoc
     */
    protected $message = 'EXTRACTOR : Entity type "%s" is not handled.';
}
