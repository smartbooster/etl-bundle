<?php

namespace Smart\EtlBundle\Tests\Generator;

use Smart\EtlBundle\Generator\DiffGenerator;
use Smart\EtlBundle\Tests\AbstractWebTestCase;
use Smart\EtlBundle\Tests\Entity\Project;

/**
 * @author Mathieu Ducrot <mathieu.ducrot@smartbooster.io>
 *
 * vendor/bin/phpunit tests/Generator/DiffGeneratorTest.php
 */
class DiffGeneratorTest extends AbstractWebTestCase
{
    public function testGenerateDiff(): void
    {
        $diffGenerator = new DiffGenerator($this->entityManager);

        // test case with null entity
        $this->assertSame([
            'diff_type' => 'new',
            'diff' =>  [
                'code' => 'DUMMY',
                'name' => 'Test',
            ]
        ], $diffGenerator->generateDiff(null, ['code' => 'DUMMY', 'name' => 'Test']));

        // test case with existing entity with change
        $project = new Project();
        $project->setCode('P1');
        $project->setName('Smart Pilot');
        $this->assertSame([
            'diff_type' => 'change',
            'diff' =>  [
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
        ], $diffGenerator->generateDiff($project, ['code' => 'P1', 'name' => 'Smart Monitor']));

        // test case with existing entity and no change
        $project = new Project();
        $project->setCode('P1');
        $project->setName('Smart Pilot');
        $this->assertSame([
            'diff_type' => 'unchanged',
            'diff' =>  [
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
        ], $diffGenerator->generateDiff($project, ['code' => 'P1', 'name' => 'Smart Pilot']));
    }

    /**
     * Functional test to check that diff with entity from database is indeed detected
     */
    public function testGenerateDiffs(): void
    {
        $diffGenerator = new DiffGenerator($this->entityManager);

        $this->loadFixtureFiles([
            $this->getFixtureDir() . '/generator/project.yml',
        ]);

        $this->assertSame([
            [
                'diff_type' => 'new',
                'diff' =>  [
                    'code' => 'DUMMY',
                    'name' => 'Test',
                ]
            ],
            [
                'diff_type' => 'change',
                'diff' =>  [
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
        ], $diffGenerator->generateDiffs(Project::class, [
            ['code' => 'DUMMY', 'name' => 'Test'],
            ['code' => 'P1', 'name' => 'Smart Monitor'],
        ]));

        // test empty data
        $this->assertSame([], $diffGenerator->generateDiffs(Project::class, []));

        // test identifier callback
        $this->assertSame([
            [
                'diff_type' => 'new',
                'diff' =>  [
                    'code' => 'DUMMY',
                    'name' => 'Test',
                ]
            ],
        ], $diffGenerator->generateDiffs(Project::class, [
            ['code' => 'DUMMY', 'name' => 'Test'],
        ], 'code', function($row) {
            return $row['code'];
        }));
    }
}
