<?php

namespace Smart\EtlBundle\Loader;

use Doctrine\ORM\EntityManager;
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
     * @param function $identifierCallback
     * @param string $identifierProperty
     * @param array $entityProperties properties to synchronize
     * @return $this
     */
    public function addEntityToProcess($entityClass, $identifierCallback, $identifierProperty, array $entityProperties)
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
            var_dump($e->getMessage());
            $this->entityManager->rollback();
        }
    }

    /**
     * @param  mixed $object
     * @return mixed
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
            if (is_object($this->accessor->getValue($object, $property))) {
                $relation = $this->accessor->getValue($object, $property);

                if (!isset($this->entitiesToProcess[get_class($relation)])) {
                    throw new EntityTypeNotHandledException(get_class($relation));
                }
                $relationIdentifier = $this->entitiesToProcess[get_class($relation)]['callback']($relation);
                $this->accessor->setValue(
                    $object,
                    $property,
                    $this->references[$relationIdentifier]
                );
            }
        }

        $dbObject = $this->entityManager->getRepository(get_class($object))->findOneBy([
            $this->entitiesToProcess[get_class($object)]['identifier'] => $identifier
        ]);
        if ($dbObject === null) {
            $this->entityManager->persist($object);
            $this->references[$identifier] = $object;
        } else {
            foreach ($this->entitiesToProcess[get_class($object)]['properties'] as $property) {
                $this->accessor->setValue($dbObject, $property, $this->accessor->getValue($object, $property));
            }
            $this->references[$identifier] = $dbObject;
        }

        return $object;
    }
}
