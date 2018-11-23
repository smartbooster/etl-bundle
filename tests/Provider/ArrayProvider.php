<?php

namespace Smart\EtlBundle\Tests\Provider;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class ArrayProvider
{
    /**
     * @return array
     */
    public static function getSimpleProjects()
    {
        return [
            ['code' => 'etl-bundle', 'name' => 'ETL Bundle'],
            ['code' => 'sonata-bundle', 'name' => 'Sonata Bundle']
        ];
    }

    /**
     * @return array
     */
    public static function getSimpleTasks()
    {
        return [
            ['name' => 'Bundle setup', 'project' => '@etl-bundle'],
            ['name' => 'Load yml entity file into database', 'project' => '@etl-bundle'],
            ['name' => 'Export database entities to yml file', 'project' => '@etl-bundle']
        ];
    }
}
