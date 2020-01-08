<?php

namespace Smart\EtlBundle\Extractor;

use Smart\EtlBundle\Exception\Extractor\EntityAlreadyRegisteredException;
use Smart\EtlBundle\Exception\Extractor\EntityIdentifiedNotFoundException;
use Smart\EtlBundle\Exception\Extractor\EntityIdentifierAlreadyProcessedException;
use Smart\EtlBundle\Exception\Extractor\EntityTypeNotHandledException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
trait EntityFileExtractorTrait
{
    /**
     * @var string
     */
    protected $folderToExtract;

    /**
     * List of entities to extract
     * [
     *      'entity_code' => 'Model Classname'
     * ]
     * @var array
     */
    protected $entitiesToProcess = [];

    /**
     * @var array
     */
    protected $entities = [];

    /**
     * @var PropertyAccessor
     */
    protected $accessor;

    /**
     * @param string $entityType
     * @param string $entityClass
     * @param callback $identifierCallback
     * @return $this
     * @throws \Exception
     */
    public function addEntityToProcess($entityType, $entityClass, $identifierCallback)
    {
        if (isset($this->entitiesToProcess[$entityType])) {
            throw new EntityAlreadyRegisteredException($entityType);
        }

        $this->entitiesToProcess[$entityType] = [
            'type' => $entityType,
            'class' => $entityClass,
            'callback' => $identifierCallback
        ];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function extract()
    {
        try {
            $this->init();
            $this->check();

            $files = $this->getFiles($this->getFileExtension());
            foreach ($this->entitiesToProcess as $entityType => $data) {
                if (!isset($files[$entityType])) {
                    continue;
                }
                $this->processFile($entityType);
            }

            return $this->entities;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @see AbstractFolderExtrator::getFiles()
     */
    abstract protected function getFiles($extension);

    /**
     * @var AbstractFolderExtrator::getFileExtension()
     */
    abstract protected function getFileExtension();

    /**
     * @var AbstractFolderExtrator::extractFileContent()
     */
    abstract protected function extractFileContent($filepath);

    /**
     * @see AbstractExtractor::transformData()
     */
    abstract protected function transformData(array $data);

    protected function check()
    {
        parent::check();

        if (count($this->entitiesToProcess) === 0) {
            throw new \BadMethodCallException('Nothing to process');
        }
    }

    protected function init()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $entityType
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    protected function processObject($entityType, array $data)
    {
        if (!isset($this->entitiesToProcess[$entityType])) {
            throw new EntityTypeNotHandledException($entityType);
        }

        $data = $this->transformData($data);
        if ($data === null) {
            return null;
        }

        $objectClass = $this->entitiesToProcess[$entityType]['class'];
        $object = new $objectClass();

        foreach ($data as $key => $value) {
            $valueToSet = $value;
            if (is_string($value) && strpos($value, '@') === 0) {
                //handle relations
                $valueToSet = $this->getEntity($value);
            } elseif (is_array($value)) {
                foreach ($valueToSet as $k => $v) {
                    if (is_string($v) && strpos($v, '@') === 0) {
                        $valueToSet[$k] = $this->getEntity($v);
                    }
                }
            }

            $this->accessor->setValue($object, $key, $valueToSet);
        }

        return $object;
    }

    /**
     * @param string $identifier
     * @return mixed
     * @throws \Exception
     */
    protected function getEntity($identifier)
    {
        if (strpos($identifier, '@') === 0) {
            $identifier = substr($identifier, 1);
        }
        if (!isset($this->entities[$identifier])) {
            throw new EntityIdentifiedNotFoundException($identifier);
        }

        return $this->entities[$identifier];
    }

    /**
     * @param  string $filename
     * @throws \Exception
     */
    protected function processFile($filename)
    {
        $filepath = sprintf('%s/%s.' . $this->getFileExtension(), $this->folderToExtract, $filename);

        $data = $this->extractFileContent($filepath);
        if ($data === null) {
            return;
        }

        foreach ($data as $values) {
            if ($values === null) {
                continue;
            }
            $object = $this->processObject($filename, $values);
            if ($object !== null) {
                $entityIdentifier = $this->entitiesToProcess[$filename]['callback']($object);
                if (isset($this->entities[$entityIdentifier])) {
                    throw new EntityIdentifierAlreadyProcessedException($entityIdentifier);
                }
                $this->entities[$entityIdentifier] = $object;
            }
        }
    }
}
