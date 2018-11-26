<?php

namespace Smart\EtlBundle\Extractor;

use Smart\EtlBundle\Transformer\TransformerInterface;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
interface ExtractorInterface
{
    /**
     * @return mixed
     */
    public function extract();

    /**
     * @param TransformerInterface $transformer
     * @return mixed
     */
    public function addTransformer(TransformerInterface $transformer);
}
