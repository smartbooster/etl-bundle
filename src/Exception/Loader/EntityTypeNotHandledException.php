<?php

namespace Smart\EtlBundle\Exception\Loader;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class EntityTypeNotHandledException extends LoaderException
{
    /**
     * @inheritdoc
     */
    protected $message = 'LOADER : Entity type "%s" is not handled.';
}
