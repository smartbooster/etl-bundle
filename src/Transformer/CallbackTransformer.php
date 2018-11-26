<?php

namespace Smart\EtlBundle\Transformer;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CallbackTransformer implements TransformerInterface
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    public function transform(array $data)
    {
        return call_user_func($this->callback, $data);
    }
}
