<?php

namespace Smart\EtlBundle\Exception\Extractor;

use Smart\EtlBundle\Exception\EtlException;

/**
 * Base exception during the extract process.
 *
 * @author Mathieu Ducrot <mathieu.ducrot@pia-production.fr>
 */
class ExtractException extends EtlException
{
    /**
     * @inheritdoc
     */
    protected $message = 'EXTRACTOR : Exception : "%s"';
}
