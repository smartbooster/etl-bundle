<?php

namespace Smart\EtlBundle\Tests\Loader;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Loader\YamlLoader;
use Smart\EtlBundle\Tests\Provider\ArrayProvider;

/**
 * vendor/bin/phpunit tests/Loader/YamlLoaderTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class YamlLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new YamlLoader(__DIR__ . '/../../var/yaml-loader', 'yml');

        $data = [];
        $data['projects'] = ArrayProvider::getSimpleProjects();
        $data['tasks'] = ArrayProvider::getSimpleTasks();
        $loader->load($data);

        $projectsLoaded = file_get_contents(__DIR__ . '/../../var/yaml-loader/projects.yml');
        $projectsExpected = <<<projectsExpected
-
    code: etl-bundle
    name: 'ETL Bundle'
-
    code: sonata-bundle
    name: 'Sonata Bundle'

projectsExpected;

        $this->assertEquals($projectsExpected, $projectsLoaded);

        $tasksLoaded = file_get_contents(__DIR__ . '/../../var/yaml-loader/tasks.yml');
        $tasksExpected = <<<tasksExpected
-
    name: 'Bundle setup'
    project: '@etl-bundle'
-
    name: 'Load yml entity file into database'
    project: '@etl-bundle'
-
    name: 'Export database entities to yml file'
    project: '@etl-bundle'

tasksExpected;

        $this->assertEquals($tasksExpected, $tasksLoaded);
    }
}
