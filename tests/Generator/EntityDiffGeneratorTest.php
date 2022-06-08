<?php

namespace Smart\EtlBundle\Tests\Generator;

use Smart\EtlBundle\Generator\EntityDiffGenerator;
use Smart\EtlBundle\Tests\AbstractWebTestCase;
use Smart\EtlBundle\Tests\Entity\Project;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * vendor/bin/phpunit tests/Generator/EntityDiffGeneratorTest.php
 */
class EntityDiffGeneratorTest extends AbstractWebTestCase
{
    public function testGenerateDiff(): void
    {
        $diffGenerator = new EntityDiffGenerator($this->entityManager);

        $newProject = new Project();
        $newProject->setCode('DUMMY');
        $newProject->setName('Test');
        // test case with null entity
        $this->assertSame([
            'diff_type' => 'new',
            'diff' => [
                'code' => 'DUMMY',
                'name' => 'Test',
            ]
        ], $diffGenerator->generateDiff($newProject, null, ['code', 'name']));

        // test case with existing entity with change
        $oldProjectChange = new Project();
        $oldProjectChange->setCode('P1');
        $oldProjectChange->setName('Smart Pilot');

        $newProjectChange = new Project();
        $newProjectChange->setCode('P1');
        $newProjectChange->setName('Smart Monitor');
        $this->assertSame([
            'diff_type' => 'change',
            'diff' => [
                'code' => [
                    'value' => 'P1',
                    'origin_value' => 'P1',
                    'has_change' => false,
                ],
                'name' => [
                    'value' => 'Smart Monitor',
                    'origin_value' => 'Smart Pilot',
                    'has_change' => true,
                ],
            ]
        ], $diffGenerator->generateDiff($newProjectChange, $oldProjectChange, ['code', 'name']));

        // test case with existing entity and no change
        $oldProjectNoChange = new Project();
        $oldProjectNoChange->setCode('P1');
        $oldProjectNoChange->setName('Smart Pilot');

        $newProjectNoChange = new Project();
        $newProjectNoChange->setCode('P1');
        $newProjectNoChange->setName('Smart Pilot');
        $this->assertSame([
            'diff_type' => 'unchanged',
            'diff' => [
                'code' => [
                    'value' => 'P1',
                    'origin_value' => 'P1',
                    'has_change' => false,
                ],
                'name' => [
                    'value' => 'Smart Pilot',
                    'origin_value' => 'Smart Pilot',
                    'has_change' => false,
                ],
            ]
        ], $diffGenerator->generateDiff($newProjectNoChange, $oldProjectNoChange, ['code', 'name']));
    }

    /**
     * Functional test to check that diff with entity from database is indeed detected
     */
    public function testGenerateDiffs(): void
    {
        $diffGenerator = new EntityDiffGenerator($this->entityManager);

        $this->loadFixtureFiles([
            $this->getFixtureDir() . '/generator/project.yml',
        ]);

        $projectChange = new Project();
        $projectChange->setCode('P1');
        $projectChange->setName('Smart Monitor');

        $newProject = new Project();
        $newProject->setCode('DUMMY');
        $newProject->setName('Test');

        $this->assertEquals([
            [
                'diff_type' => 'new',
                'diff' => [
                    'code' => 'DUMMY',
                    'name' => 'Test',
                ]
            ],
            [
                'diff_type' => 'change',
                'diff' => [
                    'code' => [
                        'value' => 'P1',
                        'origin_value' => 'P1',
                        'has_change' => false,
                    ],
                    'name' => [
                        'value' => 'Smart Monitor',
                        'origin_value' => 'Smart Pilot',
                        'has_change' => true,
                    ],
                ]
            ]
        ], $diffGenerator->generateDiffs(Project::class, [0 => $newProject, 1 => $projectChange], ['code', 'name']));

        // test empty data
        $this->assertSame([], $diffGenerator->generateDiffs(Project::class, [], []));

        // test identifier
        $this->assertSame([
            [
                'diff_type' => 'new',
                'diff' => [
                    'code' => 'DUMMY',
                    'name' => 'Test',
                ]
            ],
        ], $diffGenerator->generateDiffs(Project::class, [0 => $newProject], ['code', 'name'], 'name'));
    }
}
