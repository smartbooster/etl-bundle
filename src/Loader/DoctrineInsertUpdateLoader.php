<?php

namespace Smart\EtlBundle\Loader;

use Doctrine\ORM\EntityManager;
use Smart\EtlBundle\Entity\ImportableInterface;
use Smart\EtlBundle\Exception\Loader\EntityTypeNotHandledException;
use Smart\EtlBundle\Exception\Loader\EntityAlreadyRegisteredException;
use Smart\EtlBundle\Exception\Loader\LoaderException;
use Smart\EtlBundle\Exception\Loader\LoadUnvalidObjectsException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class DoctrineInsertUpdateLoader implements LoaderInterface
{
    const VALIDATION_GROUPS = 'smart_etl_loader';

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

    /**
     * @var array
     */
    protected $loadLogs = [];

    /**
     * @var array keep validation errors for each process object with his own associative data index
     */
    protected $arrayValidationErrors = [];

    /**
     * @var mixed
     */
    protected $processKey = null;

    protected ?ValidatorInterface $validator = null;

    /**
     * @param ValidatorInterface|null $validator TODO NEXT_MAJOR remove nullable
     */
    public function __construct($entityManager, ValidatorInterface $validator = null)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
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
            // TODO NEXT MAJOR remove callback param and use accessor getValue instead
            'callback' => $identifierCallback,
            'identifier' => $identifierProperty,
            'properties' => $entityProperties
        ];

        return $this;
    }

    /**
     * @throws \Exception
     */
    public function load(array $data)
    {
        $this->entityManager->beginTransaction();
        try {
            foreach ($data as $key => $object) {
                $this->processKey = $key;
                $this->processObject($object);
            }

            if (count($this->arrayValidationErrors) > 0) {
                throw new LoadUnvalidObjectsException($this->arrayValidationErrors);
            }

            // todo add a batch size for performance
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            if ($e instanceof LoaderException) {
                throw $e;
            }

            throw new \Exception('EXCEPTION LOADER : ' . $e->getMessage());
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
        $objectClass = get_class($object);
        if (!isset($this->entitiesToProcess[$objectClass])) {
            throw new EntityTypeNotHandledException($objectClass);
        }

        if ($this->validator !== null) {
            $validationErrors = $this->validator->validate($object, null, self::VALIDATION_GROUPS);
            if ($validationErrors->count() > 0) {
                $this->arrayValidationErrors[$this->processKey] = $validationErrors;

                return null;
            }
        }

        $identifier = $this->entitiesToProcess[$objectClass]['callback']($object);

        //Replace relations by their reference
        foreach ($this->entitiesToProcess[$objectClass]['properties'] as $property) {
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
        if (!is_null($this->entitiesToProcess[$objectClass]['identifier'])) {
            // todo enhance entity query by moving this on the load method and init the existing $dbObjects with matching identifier
            $dbObject = $this->entityManager->getRepository($objectClass)->findOneBy([$this->entitiesToProcess[$objectClass]['identifier'] => $identifier]);
        }
        if ($dbObject === null) {
            if (!$object->isImported()) {
                $object->setImportedAt(new \DateTime());
            }
            $this->entityManager->persist($object);
            if (!is_null($identifier)) {
                $this->references[$identifier] = $object;
            }

            if (isset($this->loadLogs[$objectClass])) {
                $this->loadLogs[$objectClass]['nb_created']++;
            } else {
                $this->loadLogs[$objectClass] = [
                    'nb_created' => 1,
                    'nb_updated' => 0,
                ];
            }
        } else {
            // todo validate if there is no change (if so do not increase the nb_updated)
            foreach ($this->entitiesToProcess[$objectClass]['properties'] as $property) {
                $this->accessor->setValue($dbObject, $property, $this->accessor->getValue($object, $property));
            }
            if (!$dbObject->isImported()) {
                $dbObject->setImportedAt(new \DateTime());
            }
            $this->references[$identifier] = $dbObject;

            if (isset($this->loadLogs[$objectClass])) {
                $this->loadLogs[$objectClass]['nb_updated']++;
            } else {
                $this->loadLogs[$objectClass] = [
                    'nb_created' => 0,
                    'nb_updated' => 1,
                ];
            }
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

    public function getLogs(): array
    {
        return $this->loadLogs;
    }

    public function clearLogs(): void
    {
        $this->loadLogs = [];
    }
}
