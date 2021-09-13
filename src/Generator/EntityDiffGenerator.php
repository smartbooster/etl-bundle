<?php

namespace Smart\EtlBundle\Generator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Louis Fortunier <louis.fortunier@smartbooster.io>
 *
 * The EntityDiffGenerator class is responsible for comparing entities and
 * generating a diff preview with the changes needed to migrate from one data to the other.
 */
class EntityDiffGenerator
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

    public function generateDiffs(string $entityClass, array $entities, array $properties, string $identifier = null): array
    {
        $toReturn = [];
        if ($entities === []) {
            return $toReturn;
        }
        $identifier = $identifier != null ? $identifier : $properties[0];
        $identifiers = array_map(function ($entity) use ($identifier) {
            return $this->accessor->getValue($entity, $identifier);
        }, $entities);

        $entitiesFromDb = $this->entityManager->getRepository($entityClass)
            ->createQueryBuilder('o', "o.$identifier")
            ->where("o.$identifier IN (:identifiers)")
            ->setParameter('identifiers', $identifiers)
            ->getQuery()
            ->getResult();

        foreach ($entities as $key => $entity) {
            $entityFromDb = null;

            if (isset($entitiesFromDb[$this->accessor->getValue($entity, $identifier)])) {
                $entityFromDb = $entitiesFromDb[$this->accessor->getValue($entity, $identifier)];
            }

            $toReturn[$key] = $this->generateDiff($entity, $entityFromDb, $properties);
        }

        return $toReturn;
    }

    /**
     * @param mixed $entity
     */
    public function generateDiff($entity, $entityFromDb, array $properties): array
    {
        $isNew = ($entityFromDb === null);
        $toReturn['diff_type'] = 'new';

        $hasDataChange = false;
        foreach ($properties as $property) {
            $value = $this->accessor->getValue($entity, $property);
            $toReturn['diff'][$property] = $value;

            if (!$isNew) {
                $toReturn['diff_type'] = 'change';

                $originValue = $this->accessor->getValue($entityFromDb, $property);
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
