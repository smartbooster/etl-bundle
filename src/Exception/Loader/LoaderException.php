<?php

namespace Smart\EtlBundle\Exception\Loader;

use Smart\EtlBundle\Exception\EtlException;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class LoaderException extends EtlException
{
    /**
     * @inheritdoc
     */
    protected $message = 'LOADER : Exception : "%s"';
}
