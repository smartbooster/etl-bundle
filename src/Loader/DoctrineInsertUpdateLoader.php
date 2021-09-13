<?php

namespace Smart\EtlBundle\Loader;

use Doctrine\ORM\EntityManager;
use Smart\EtlBundle\Entity\ImportableInterface;
use Smart\EtlBundle\Exception\Loader\EntityTypeNotHandledException;
use Smart\EtlBundle\Exception\Loader\EntityAlreadyRegisteredException;
use Smart\EtlBundle\Exception\Loader\LoaderException;
use Smart\EtlBundle\Exception\Loader\LoadUnvalidObjectsException;
use Smart\EtlBundle\Utils\ArrayUtils;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class DoctrineInsertUpdateLoader implements LoaderInterface
{
    const VALIDATION_GROUPS = 'smart_etl_loader';
    const BATCH_SIZE = 30;

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

    protected ValidatorInterface $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct($entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $entityClass
     * @param string $identifierProperty : if null this entity will be always insert
     * @param array $entityProperties properties to synchronize
     * @return $this
     */
    public function addEntityToProcess($entityClass, $identifierProperty, array $entityProperties = [])
    {
        if (isset($this->entitiesToProcess[$entityClass])) {
            throw new EntityAlreadyRegisteredException($entityClass);
        }

        $this->entitiesToProcess[$entityClass] = [
            'class' => $entityClass,
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
            $index = 1;
            $dbObjects = $this->getDbObjects($data);

            foreach ($data as $key => $object) {
                $this->processKey = $key;
                $this->processObject($object, $dbObjects);

                if (($index % self::BATCH_SIZE) === 0) {
                    $this->entityManager->flush();
                }

                $index++;
            }

            if (count($this->arrayValidationErrors) > 0) {
                throw new LoadUnvalidObjectsException($this->arrayValidationErrors);
            }

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
    protected function processObject($object, array $dbObjects)
    {
        $objectClass = get_class($object);
        if (!isset($this->entitiesToProcess[$objectClass])) {
            throw new EntityTypeNotHandledException($objectClass);
        }

        $validationErrors = $this->validator->validate($object, null, self::VALIDATION_GROUPS);
        if ($validationErrors->count() > 0) {
            $this->arrayValidationErrors[$this->processKey] = $validationErrors;

            return null;
        }

        $identifier = $this->accessor->getValue($object, $this->entitiesToProcess[$objectClass]['identifier']);
        //Replace relations by their reference
        foreach ($this->entitiesToProcess[$objectClass]['properties'] as $property) {
            $propertyValue = $this->accessor->getValue($object, $property);
            if ($this->isEntityRelation($propertyValue)) {
                $relation = $propertyValue; //better understanding

                $relationIdentifier = $this->accessor->getValue($relation, $this->entitiesToProcess[get_class($relation)]['identifier']);
                if (!isset($this->references[$relationIdentifier])) {
                    //new relation should be processed before
                    $this->processObject($relation, $dbObjects);
                }
                $this->accessor->setValue(
                    $object,
                    $property,
                    $this->references[$relationIdentifier]
                );
            } elseif ($propertyValue instanceof \Traversable) {
                foreach ($propertyValue as $k => $v) {
                    if ($this->isEntityRelation($v)) {
                        $relationIdentifier = $this->accessor->getValue($v, $this->entitiesToProcess[get_class($v)]['identifier']);
                        if (!isset($this->references[$relationIdentifier])) {
                            //new relation should be processed before
                            $this->processObject($v, $dbObjects);
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

        if (isset($dbObjects[$objectClass]) && isset($dbObjects[$objectClass][$identifier])) {
            $dbObject = $dbObjects[$objectClass][$identifier];
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

    private function getDbObjects(array $datas): array
    {
        $dbObjectsParam = [];
        $toReturn = [];

        // construct of array with all db object identifier needed
        foreach ($datas as $object) {
            $objectClass = get_class($object);

            $dbObjectsParam = ArrayUtils::addMultidimensionalArrayValue($dbObjectsParam, $objectClass, $this->accessor->getValue($object, $this->entitiesToProcess[$objectClass]['identifier']));
            $dbObjectsParam = $this->addDbObjectRelationParam($object, $dbObjectsParam);
        }

        // get all needed db object
        foreach ($dbObjectsParam as $class => $identifiers) {
            $dbObjects = $this->entityManager->getRepository($class)->findBy([$this->entitiesToProcess[$class]['identifier'] => $identifiers]);
            foreach ($dbObjects as $dbObject) {
                $toReturn[$class][$this->accessor->getValue($dbObject, $this->entitiesToProcess[$class]['identifier'])] = $dbObject;
            }
        }

        return $toReturn;
    }

    /** Look relation of object and add param in $dbObjectsParam */
    private function addDbObjectRelationParam($object, $dbObjectsParam): array
    {
        $objectClass = get_class($object);
        foreach ($this->entitiesToProcess[$objectClass]['properties'] as $property) {
            $propertyValue = $this->accessor->getValue($object, $property);
            if ($this->isEntityRelation($propertyValue)) {
                $relation = $propertyValue; //better understanding

                $dbObjectsParam = $this->addRelationParam($relation, $dbObjectsParam);
            } elseif ($propertyValue instanceof \Traversable) {
                foreach ($propertyValue as $v) {
                    if ($this->isEntityRelation($v)) {
                        $dbObjectsParam = $this->addRelationParam($v, $dbObjectsParam);
                    }
                }
            }
        }

        return $dbObjectsParam;
    }

    private function addRelationParam($object, array $dbObjectsParam): array
    {
        if (!isset($this->entitiesToProcess[get_class($object)])) {
            throw new EntityTypeNotHandledException(get_class($object));
        }
        $relationIdentifier = $this->accessor->getValue($object, $this->entitiesToProcess[get_class($object)]['identifier']);
        if (!isset($this->references[$relationIdentifier])) {
            $dbObjectsParam = ArrayUtils::addMultidimensionalArrayValue($dbObjectsParam, get_class($object), $relationIdentifier);
            $dbObjectsParam = $this->addDbObjectRelationParam($object, $dbObjectsParam);
        }

        return $dbObjectsParam;
    }
}
