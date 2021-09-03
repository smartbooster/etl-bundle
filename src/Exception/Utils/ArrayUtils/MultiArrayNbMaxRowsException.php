<?php

namespace Smart\EtlBundle\Exception\Utils\ArrayUtils;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class MultiArrayNbMaxRowsException extends \Exception
{
    /** @var int maximum number of rows allowed */
    public $nbMaxRows;

    /** @var int current number of rows */
    public $nbRows;

    public function __construct($nbMaxRows, $nbRows)
    {
        $this->nbMaxRows = $nbMaxRows;
        $this->nbRows = $nbRows;

        parent::__construct(
            'array_utils.multi_array_nb_max_rows_error',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            null
        );
    }
}
