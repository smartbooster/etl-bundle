<?php

namespace Smart\EtlBundle\Extractor;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Smart\EtlBundle\Entity\ImportableInterface;
use Smart\EtlBundle\Exception\Extractor\EntityTypeNotHandledException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class DoctrineEntityExtractor extends AbstractExtractor implements ExtractorInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * @var string
     */
    protected $entityToExtract;

    /**
     * @var array
     */
    protected $propertiesToExtract;

    /**
     * @var QueryBuilder
     */
    protected $queryBuilder = null;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $entityToExtract
     */
    public function setEntityToExtract($entityToExtract, array $propertiesToExtract)
    {
        $this->entityToExtract = $entityToExtract;
        $this->propertiesToExtract = $propertiesToExtract;
        $this->queryBuilder = null;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {

        $repository = $this->entityManager->getRepository($this->entityToExtract);
        if (!$repository instanceof EntityRepository) {
            throw new \UnexpectedValueException("No repository found for class {$this->entityToExtract}");
        }
        $this->queryBuilder = $repository->createQueryBuilder('o');

        if ($this->queryBuilder === null) {
            throw new \BadMethodCallException('Invalid entityToExtract');
        }

        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @inheritdoc
     */
    public function extract()
    {
        $entities = $this->queryBuilder->getQuery()->getResult();

        //Replace relation references
        foreach ($entities as $key => $entity) {
            if (!$entity instanceof ImportableInterface) {
                throw new EntityTypeNotHandledException(get_class($entity));
            }
            $entityData = [];
            foreach ($this->propertiesToExtract as $property) {
                $value = $this->accessor->getValue($entity, $property);

                if ($this->isEntityRelation($value)) {
                    if (!$value instanceof ImportableInterface) {
                        throw new EntityTypeNotHandledException(get_class($value));
                    }
                    $entityData[$property] = '@' . $value->getImportId();
                } else {
                    $entityData[$property] = $value;
                }
            }
            $entities[$entity->getImportId()] = $entityData;
            unset($entities[$key]);
        }

        return $entities;
    }

    /**
     * Check if $propertyValue is an entity relation to process
     *
     * @param  mixed $propertyValue
     * @return bool
     */
    protected function isEntityRelation($propertyValue)
    {
        return (is_object($propertyValue) && !($propertyValue instanceof \DateTime));
    }
}
