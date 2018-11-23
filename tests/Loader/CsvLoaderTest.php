<?php

namespace Smart\EtlBundle\Tests\Loader;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Loader\CsvLoader;
use Smart\EtlBundle\Tests\Provider\ArrayProvider;

/**
 * vendor/bin/phpunit tests/Loader/CsvLoaderTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CsvLoaderTest extends TestCase
{
    public function testLoad()
    {
        $loader = new CsvLoader(__DIR__ . '/../../var/csv-loader', 'csv');

        $data = [];
        $data['projects'] = ArrayProvider::getSimpleProjects();
        $data['tasks'] = ArrayProvider::getSimpleTasks();
        $loader->load($data);

        $projectsLoaded = file_get_contents(__DIR__ . '/../../var/csv-loader/projects.csv');
        $projectsExpected = 'code,name' . PHP_EOL;
        $projectsExpected .= 'etl-bundle,"ETL Bundle"' . PHP_EOL;
        $projectsExpected .= 'sonata-bundle,"Sonata Bundle"' . PHP_EOL;

        $this->assertEquals($projectsExpected, $projectsLoaded);

        $tasksLoaded = file_get_contents(__DIR__ . '/../../var/csv-loader/tasks.csv');
        $tasksExpected = 'name,project' . PHP_EOL;
        $tasksExpected .= '"Bundle setup",@etl-bundle' . PHP_EOL;
        $tasksExpected .= '"Load yml entity file into database",@etl-bundle' . PHP_EOL;
        $tasksExpected .= '"Export database entities to yml file",@etl-bundle' . PHP_EOL;

        $this->assertEquals($tasksExpected, $tasksLoaded);
    }
}
