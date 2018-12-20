<?php

namespace Smart\EtlBundle\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Extractor\CsvEntityExtractor;
use Smart\EtlBundle\Tests\Model\Project;
use Smart\EtlBundle\Tests\Model\Task;

/**
 * vendor/bin/phpunit tests/Extractor/CsvEntityExtractorTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class CsvEntityExtractorTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testExtractEntities()
    {
        $extractor = new CsvEntityExtractor();
        $extractor->setFolderToExtract(__DIR__ . '/../fixtures/entity-csv');
        $extractor
            ->addEntityToProcess('project', Project::class, function ($e) {
                return $e->getCode();
            })
            ->addEntityToProcess('task', Task::class, function ($e) {
                return 'task' . $e->getProject()->getCode() . '-' . substr(md5($e->getName()), 0, 5);
            })
        ;
        $entities = $extractor->extract();

        $this->assertEquals(5, count($entities));

        $projectEtl = new Project('etl-bundle', 'ETL Bundle');
        $projectSonata = new Project('sonata-bundle', 'Sonata Bundle');
        $taskA = new Task($projectEtl, 'Bundle setup');
        $taskB = new Task($projectEtl, 'Load yml entity file into database');
        $taskC = new Task($projectEtl, 'Export database entities to yml file');

        $this->assertEquals([
            'etl-bundle' => $projectEtl,
            'sonata-bundle' => $projectSonata,
            'tasketl-bundle-9d05b' => $taskA,
            'tasketl-bundle-519be' =>$taskB,
            'tasketl-bundle-c9264' => $taskC
        ], $entities);
    }
}
