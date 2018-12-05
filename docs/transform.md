# Transform your data

## Overview

After you have extracted your data for a source, you will have to process it to make it fits into your destination format.

Examples of common transformations :

- case transformation : lowercase, uppercase, slugify...
- cleaning : remove non valid data based on business logic (harder than a where query in your extractor)
- encoding convertion
- ...

Transformers will help you with this point.

## What is a transformer

A transformer is a simple object which will transform your data at the end of your extraction process.
After you extractor has done tis job job, it will pass each line of data through every transformer before returning
your final data.

Transformers allow you to remove some data for the extraction, it only have to return null for unwanted data.

## How to add a transformer to your extractors

[`ExtractorInterface`](https://github.com/smartbooster/etl-bundle/blob/master/src/Extractor/ExtractorInterface.php)
allow you to add transformers through the `addtransformer` method.

```php

$extractor = new CsvEntityExtractor();
...

$extractor->addTransformer(new IconvTransformer('iso-8859-1', 'UTF-8'));
$datas = $extractor->extract();

```

**Important :** Transformers are called in the same order that you add them so if you want transformer A to be called
after B, you have to add A to your extractor before B.

**Performance tip :** If some of your transformers remove data from the extractor, put them first if possible so others
will process less data.

**Quality tips :** Transformers are simple to unit test. So develop them with unit test first, you will save a lot of time.

## How to implement your own transformers

A transformer have to implement the [`TransformerInterface`](https://github.com/smartbooster/etl-bundle/blob/master/src/Transformer/TransformerInterface.php).

Example :

```php
<?php

namespace AppBundle\Transformer;

use Smart\EtlBundle\Transformer\TransformerInterface;

class OptimizeIncomeTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform(array $data)
    {
        if (isset($data['price'])) {
            $data['price'] += 10;
        }

        return $data;
    }
}
```

This transformer will simply add 10 to every price.

**Important :** your transform method should always return $data, if it doesn't, your data will be removed.

## How to remove data for your extractor

Transformers allow you to remove some data for the extraction, it only have to return null for unwanted data.

Example :

```php
<?php

namespace AppBundle\Transformer;

use Smart\EtlBundle\Transformer\TransformerInterface;

class OptimizeIncomeTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function transform(array $data)
    {
        if (isset($data['price'])) {
            if ($data['price'] < 100) {
                //we don't want low price product
                return null;
            }
            $data['price'] += 10;
        }

        return $data;
    }
}
```
