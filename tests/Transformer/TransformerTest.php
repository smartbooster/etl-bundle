<?php

namespace Smart\EtlBundle\Tests\Transformer;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Extractor\CsvEntityExtractor;
use Smart\EtlBundle\Tests\Model\Project;
use Smart\EtlBundle\Transformer\CallbackTransformer;

/**
 * vendor/bin/phpunit tests/Transformer/TransformerTest.php
 *
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class TransformerTest extends TestCase
{
    public function testProjectToTransform()
    {
        $extractor = new CsvEntityExtractor();
        $extractor->setFolderToExtract(__DIR__ . '/../fixtures/entity-csv');
        $extractor
            ->addEntityToProcess('to-transform-project-iso-8859-1', 'Smart\EtlBundle\Tests\Model\Project', function ($e) {
                return $e->getCode();
            })
        ;
        $extractor->addTransformer(new CallbackTransformer(function ($project) {
            if (!isset($project['ID'])) {
                return null;
            }
            $project['import_id'] = $project['ID'];
            $project['name'] = iconv('iso-8859-1', 'UTF-8', $project['Projet']);
            $project['description'] = iconv('iso-8859-1', 'UTF-8', $project['Description']);

            unset($project['ID'], $project['Projet'], $project['Description']);

            return $project;
        }));
        $entities = $extractor->extract();

        $this->assertEquals(1, count($entities));

        $projectEtl = new Project('etl-bundle', 'Récupération de l\'eau de pluie');
        $projectEtl->setDescription(<<<description
---------------------------------------------------------
    Récupération de l'eau de pluie
    sur plusieurs lignes
---------------------------------------------------------
description
        );
    }
}
