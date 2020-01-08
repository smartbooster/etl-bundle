<?php

namespace Smart\EtlBundle\Loader;

use Doctrine\ORM\EntityManager;
use Smart\EtlBundle\Entity\ImportableInterface;
use Smart\EtlBundle\Exception\Loader\EntityTypeNotHandledException;
use Smart\EtlBundle\Exception\Loader\EntityAlreadyRegisteredException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class DoctrineInsertUpdateLoader implements LoaderInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var array
     */
    protected $references;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * List of entities to extract
     * [
     *      'class' => []
     * ]
     * @var array
     */
    protected $entitiesToProcess = [];

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $entityClass
     * @param callback $identifierCallback
     * @param string $identifierProperty : if null this entity will be always insert
     * @param array $entityProperties properties to synchronize
     * @return $this
     */
    public function addEntityToProcess($entityClass, $identifierCallback, $identifierProperty, array $entityProperties = [])
    {
        if (isset($this->entitiesToProcess[$entityClass])) {
            throw new EntityAlreadyRegisteredException($entityClass);
        }

        $this->entitiesToProcess[$entityClass] = [
            'class' => $entityClass,
            'callback' => $identifierCallback,
            'identifier' => $identifierProperty,
            'properties' => $entityProperties
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function load(array $data)
    {
        $this->entityManager->beginTransaction();
        try {
            foreach ($data as $object) {
                $this->processObject($object);
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            var_dump('EXCEPTION LOADER : ' . $e->getMessage());
            $this->entityManager->rollback();
        }
    }

    /**
     * @param  ImportableInterface $object
     * @return ImportableInterface
     * @throws \Exception
     * @throws \TypeError
     */
    protected function processObject($object)
    {
        if (!isset($this->entitiesToProcess[get_class($object)])) {
            throw new EntityTypeNotHandledException(get_class($object));
        }
        $identifier = $this->entitiesToProcess[get_class($object)]['callback']($object);

        //Replace relations by their reference
        foreach ($this->entitiesToProcess[get_class($object)]['properties'] as $property) {
            $propertyValue = $this->accessor->getValue($object, $property);
            if ($this->isEntityRelation($propertyValue)) {
                $relation = $propertyValue; //better understanding

                if (!isset($this->entitiesToProcess[get_class($relation)])) {
                    throw new EntityTypeNotHandledException(get_class($relation));
                }
                $relationIdentifier = $this->entitiesToProcess[get_class($relation)]['callback']($relation);
                if (!isset($this->references[$relationIdentifier])) {
                    //new relation should be processed before
                    $this->processObject($relation);
                }
                $this->accessor->setValue(
                    $object,
                    $property,
                    $this->references[$relationIdentifier]
                );
            } elseif ($propertyValue instanceof \Traversable) {
                foreach ($propertyValue as $k => $v) {
                    if ($this->isEntityRelation($v)) {
                        if (!isset($this->entitiesToProcess[get_class($v)])) {
                            throw new EntityTypeNotHandledException(get_class($v));
                        }
                        $relationIdentifier = $this->entitiesToProcess[get_class($v)]['callback']($v);
                        if (!isset($this->references[$relationIdentifier])) {
                            //new relation should be processed before
                            $this->processObject($v);
                        }
                        $propertyValue[$k] = $this->references[$relationIdentifier];
                    }
                }
                $this->accessor->setValue(
                    $object,
                    $property,
                    $propertyValue
                );
            }
        }

        $dbObject = null;
        if (!is_null($this->entitiesToProcess[get_class($object)]['identifier'])) {
            $dbObject = $this->entityManager->getRepository(get_class($object))->findOneBy([$this->entitiesToProcess[get_class($object)]['identifier'] => $identifier]);
        }
        if ($dbObject === null) {
            if (!$object->isImported()) {
                $object->setImportedAt(new \DateTime());
            }
            $this->entityManager->persist($object);
            if (!is_null($identifier)) {
                $this->references[$identifier] = $object;
            }
        } else {
            foreach ($this->entitiesToProcess[get_class($object)]['properties'] as $property) {
                $this->accessor->setValue($dbObject, $property, $this->accessor->getValue($object, $property));
            }
            if (!$dbObject->isImported()) {
                $dbObject->setImportedAt(new \DateTime());
            }
            $this->references[$identifier] = $dbObject;
        }

        return $object;
    }

    /**
     * Check if $propertyValue is an entity relation to process
     *
     * @param  mixed $propertyValue
     * @return bool
     */
    protected function isEntityRelation($propertyValue)
    {
        return (is_object($propertyValue) && !($propertyValue instanceof \DateTime) && !($propertyValue instanceof \Traversable));
    }
}
