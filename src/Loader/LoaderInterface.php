<?php

namespace Smart\EtlBundle\Loader;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
interface LoaderInterface
{
    /**
     * @param array $data
     */
    public function load(array $data);
}
