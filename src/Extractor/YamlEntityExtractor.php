<?php

namespace Smart\EtlBundle\Extractor;

use Smart\EtlBundle\Exception\Extractor\EntityAlreadyRegisteredException;
use Smart\EtlBundle\Exception\Extractor\EntityIdentifiedNotFoundException;
use Smart\EtlBundle\Exception\Extractor\EntityIdentifierAlreadyProcessException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Yaml\Yaml;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class YamlEntityExtractor extends AbstractFolderExtrator implements ExtractorInterface
{
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

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $entityType
     * @param string$entityClass
     * @param function $identifierCallback
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
            $this->check();
            
            $files = $this->getFiles('yml');
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
    
    protected function check()
    {
        parent::check();
        
        if (count($this->entitiesToProcess) === 0) {
            throw new \BadMethodCallException('Nothing to process');
        }
    }

    /**
     * @param $filename
     * @throws \Exception
     */
    protected function processFile($filename)
    {
        $filepath = sprintf('%s/%s.yml', $this->folderToExtract, $filename);

        $data = Yaml::parse(file_get_contents($filepath));
        foreach ($data as $values) {
            $object = $this->processObject($filename, $values);
            if ($object !== null) {
                $entityIdentifier = $this->entitiesToProcess[$filename]['callback']($object);
                if (isset($this->entities[$entityIdentifier])) {
                    throw new EntityIdentifierAlreadyProcessException($entityIdentifier);
                }
                $this->entities[$entityIdentifier] = $object;
            }
        }
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
            throw new \Exception('Entity type ' . $entityType . ' is not handled');
        }

        $objectClass = $this->entitiesToProcess[$entityType]['class'];
        $object = new $objectClass();

        foreach ($data as $key => $value) {
            $valueToSet = $value;
            if (strpos($value, '@') === 0) {
                //handle relations
                $valueToSet = $this->getEntity($value);
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
}
