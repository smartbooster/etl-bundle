<?php

namespace Smart\EtlBundle\Tests\Extractor;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Smart\EtlBundle\Extractor\DoctrineEntityExtractor;
use Smart\EtlBundle\Tests\Entity\Project;
use Smart\EtlBundle\Tests\Entity\Task;

/**
 * vendor/bin/phpunit tests/Extractor/DoctrineEntityExtractorTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class DoctrineEntityExtractorTest extends WebTestCase
{
    public function testExtractEntities()
    {
        //Initialise database
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager('default');
        $metadatas = $em->getMetadataFactory()->getMetadataFor(Project::class);

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema([$metadatas]);

        $this->loadFixtureFiles([
            __DIR__ . '/../fixtures/doctrine-loader/organisation.yml',
            __DIR__ . '/../fixtures/doctrine-loader/project.yml',
            __DIR__ . '/../fixtures/doctrine-loader/tag.yml',
            __DIR__ . '/../fixtures/doctrine-loader/task.yml',
        ]);

        $extractor = new DoctrineEntityExtractor($em);
        $extractor->setEntityToExtract(Project::class, ['organisation', 'name']);
        $qbExtractor = $extractor->getQueryBuilder();
        //We agree that you should not make where like query if you want reasonable performance
        $qbExtractor->andWhere(
            $qbExtractor->expr()->like($qbExtractor->getRootAlias() . '.code', $qbExtractor->expr()->literal('etl%'))
        );
        $extractor->setQueryBuilder($qbExtractor);

        $entities = $extractor->extract();
        $this->assertEquals(1, count($entities));

        $this->assertEquals([
            'etl-bundle' => ['organisation' => '@smartbooster', 'name' => 'ETL Bundle']
        ], $entities);


        $extractor->setEntityToExtract(Task::class, ['code', 'project', 'name', 'tags']);
        $entities = $extractor->extract();

        $this->assertEquals(2, count($entities));

        $this->assertEquals([
            'etl-bundle-setup' => [
                'code' => 'etl-bundle-setup',
                'project' => '@etl-bundle',
                'name' => 'Bundle setup',
                'tags' => ['@doing', '@easy']
            ],
            'etl-bundle-loadyml' => [
                'code' => 'etl-bundle-loadyml',
                'project' => '@etl-bundle',
                'name' => 'Load yml entity file into database',
                'tags' => ['@todo', '@hard']
            ]
        ], $entities);
    }
}
