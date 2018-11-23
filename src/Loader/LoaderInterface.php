<?php

namespace Smart\EtlBundle\Loader;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
interface LoaderInterface
{
    public function load(array $data);
}
