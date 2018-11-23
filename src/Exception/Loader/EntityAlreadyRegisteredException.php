<?php

namespace Smart\EtlBundle\Exception\Loader;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class EntityAlreadyRegisteredException extends LoaderException
{
    /**
     * @inheritdoc
     */
    protected $message = 'LOADER : Entity type "%s" already registered';
}
