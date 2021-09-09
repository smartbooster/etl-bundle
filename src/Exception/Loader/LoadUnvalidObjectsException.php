<?php

namespace Smart\EtlBundle\Exception\Loader;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 */
class LoadUnvalidObjectsException extends LoaderException
{
    /**
     * remove EtlException message prefix to only have dot notation format so that once the bundle is register on a
     * project, developer can rely on translation
     */
    protected $message = '%s';

    public array $arrayValidationErrors;

    public function __construct(array $arrayValidationErrors)
    {
        $this->arrayValidationErrors = $arrayValidationErrors;

        parent::__construct(
            'smart_etl.loader.load_unvalid_objects_error',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            null
        );
    }
}
