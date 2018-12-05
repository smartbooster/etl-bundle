<?php

namespace Smart\EtlBundle\Transformer;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class IconvTransformer implements TransformerInterface
{
    /**
     * @var string
     */
    protected $inCharset;

    /**
     * @var string
     */
    protected $outCharset;

    public function __construct($inCharset, $outCharset)
    {
        $this->inCharset = $inCharset;
        $this->outCharset = $outCharset;
    }

    /**
     * @inheritdoc
     */
    public function transform(array $data)
    {
        foreach ($data as $key => $value) {
            $convertedKey = iconv($this->inCharset, $this->outCharset, $key);
            $data[$convertedKey] = iconv($this->inCharset, $this->outCharset, $value);
            if (strcmp($key, $convertedKey) !== 0) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}
