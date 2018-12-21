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
            [
                'name' => 'Bundle setup',
                'project' => '@etl-bundle',
                'tags' => []
            ],
            [
                'name' => 'Load yml entity file into database',
                'project' => '@etl-bundle',
                'tags' => ['@doing', '@easy']
            ],
            [
                'name' => 'Export database entities to yml file',
                'project' => '@etl-bundle',
                'tags' => ['@todo', '@hard']
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getSimpleTags()
    {
        return [
            ['name' => 'Todo', 'import_id' => 'todo'],
            ['name' => 'Doing', 'import_id' => 'doing'],
            ['name' => 'Done', 'import_id' => 'done'],
            ['name' => 'Easy', 'import_id' => 'easy'],
            ['name' => 'Hard', 'import_id' => 'hard'],
        ];
    }
}
