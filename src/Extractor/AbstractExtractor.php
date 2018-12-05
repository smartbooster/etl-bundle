<?php

namespace Smart\EtlBundle\Extractor;

use Smart\EtlBundle\Transformer\TransformerInterface;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
abstract class AbstractExtractor
{
    /**
     * @var TransformerInterface[]
     */
    protected $transformers = [];

    /**
     * @param TransformerInterface $transformer
     * @return mixed
     */
    public function addTransformer(TransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;
    }

    /**
     * @param  array $data
     * @return mixed
     */
    protected function transformData(array $data)
    {
        foreach ($this->transformers as $transformer) {
            $data = $transformer->transform($data);
            if (is_null($data)) {
                //allow transformer to skip invalid data
                return null;
            }
        }

        return $data;
    }
}
