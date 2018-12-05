# Utils to ease your implementation

## ImportableTrait

When your import data regularly you will have to face the question : Did I already import this data ?

As you source and destination won't share the same identifier, the best way to answer this question is to store
the source id and compare with it the next time.

[`ImportableTrait`](https://github.com/smartbooster/etl-bundle/blob/master/src/Entity/ImportableTrait.php) adds a
importId and an importedAt data to your entities.

We also provied a `ImportableInterface`.
