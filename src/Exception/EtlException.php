<?php

namespace Smart\EtlBundle\Exception;

use RuntimeException;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class EtlException extends RuntimeException
{
    /**
     * @var string
     */
    protected $message = 'ETL Exception : "%s"';

    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        return parent::__construct(sprintf($this->message, $message), $code, $previous);
    }
}
