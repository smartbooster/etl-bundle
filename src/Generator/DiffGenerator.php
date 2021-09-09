<?php

namespace Smart\EtlBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * The DiffGenerator class is responsible for comparing a raw data with data pulled from the manager on the entity and
 * generating a diff preview with the changes needed to migrate from one data to the other.
 */
class DiffGenerator
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var PropertyAccessor */
    protected $accessor;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param array $multiArrayData multidimensional array data containing one entity data per row
     * @TODO $identifierCallback can be remove once DiffGenerator will work with multiEntityData instead of Array
     */
    public function generateDiffs(string $entityClass, array $multiArrayData, string $identifier = null, $identifierCallback = null)
    {
        $toReturn = [];
        if ($multiArrayData === []) {
            return $toReturn;
        }

        $identifier = $identifier ?? array_keys($multiArrayData[0])[0];
        // todo enhance entity query by only getting the one that identier are matching from $multiArrayData
        $entitiesFromDb = $this->entityManager->getRepository($entityClass)
            ->createQueryBuilder('o', "o.$identifier")
            ->getQuery()
            ->getResult()
        ;

        foreach ($multiArrayData as $key => $row) {
            if ($identifierCallback === null) {
                $entityFromDb = $entitiesFromDb[reset($row)] ?? null;
            } else {
                $entityFromDb = $entitiesFromDb[$identifierCallback($row)] ?? null;
            }

            $toReturn[$key] = $this->generateDiff($entityFromDb, $row);
        }

        return $toReturn;
    }

    /**
     * @param mixed $entity
     */
    public function generateDiff($entity, array $data): array
    {
        $isNew = ($entity === null);
        $toReturn['diff_type'] = 'new';

        $hasDataChange = false;
        foreach ($data as $property => $value) {
            $toReturn['diff'][$property] = $value;

            if (!$isNew) {
                $toReturn['diff_type'] = 'change';

                $originValue = $this->accessor->getValue($entity, $property);
                $hasChange = ($originValue != $value);
                $toReturn['diff'][$property] = [
                    'value' => $value,
                    'origin_value' => $originValue,
                    'has_change' => $hasChange,
                ];

                if ($hasChange) {
                    $hasDataChange = true;
                }
            }
        }

        if (!$isNew && !$hasDataChange) {
            $toReturn['diff_type'] = 'unchanged';
        }

        return $toReturn;
    }
}
