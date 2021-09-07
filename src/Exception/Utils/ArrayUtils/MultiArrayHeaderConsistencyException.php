<?php

namespace Smart\EtlBundle\Exception\Utils\ArrayUtils;

use Symfony\Component\HttpFoundation\Response;

class MultiArrayHeaderConsistencyException extends \Exception
{
    /** @var array List of keys of the MultiArray where the error occurred */
    public $keys;

    public function __construct(array $keys)
    {
        $this->keys = $keys;

        parent::__construct(
            'array_utils.multi_array_header_consistency_error',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            null
        );
    }
}
