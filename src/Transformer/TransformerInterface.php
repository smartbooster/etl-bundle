<?php

namespace Smart\EtlBundle\Transformer;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
interface TransformerInterface
{
    /**
     * @param  array $data
     * @return mixed
     */
    public function transform(array $data);
}
