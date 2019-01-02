<?php

namespace Smart\EtlBundle\Tests\Extractor;

use PHPUnit\Framework\TestCase;
use Smart\EtlBundle\Exception\Extractor\EntityAlreadyRegisteredException;
use Smart\EtlBundle\Exception\Extractor\EntityIdentifiedNotFoundException;
use Smart\EtlBundle\Exception\Extractor\EntityIdentifierAlreadyProcessedException;
use Smart\EtlBundle\Extractor\ExtractorInterface;
use Smart\EtlBundle\Tests\Entity\Tag;
use Smart\EtlBundle\Tests\Model\Project;
use Smart\EtlBundle\Tests\Model\Task;

/**
 * Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
abstract class AbstractEntityExtractorTest extends TestCase
{
    /**
     * @return ExtractorInterface
     */
    abstract protected function getExtractor();

    /**
     * @throws \Exception
     */
    public function testEntityAlreadyRegisteredException()
    {
        $this->expectException(EntityAlreadyRegisteredException::class);

        $extractor = $this->getExtractor();
        $extractor
            ->addEntityToProcess('project', Project::class, function ($e) {
                return $e->getCode();
            })
            ->addEntityToProcess('project', Project::class, function ($e) {
                return $e->getCode();
            })
        ;
    }

    /**
     * @throws \Exception
     */
    public function testEntityIdentifierAlreadyProcessException()
    {
        $this->expectException(EntityIdentifierAlreadyProcessedException::class);

        $extractor = $this->getExtractor();
        $extractor
            ->addEntityToProcess('project', Project::class, function ($e) {
                return "same_code";
            })
        ;
        $extractor->extract();
    }

    /**
     * @throws \Exception
     */
    public function testEntityIdentifiedNotFoundException()
    {
        $this->expectException(EntityIdentifiedNotFoundException::class);

        $extractor = $this->getExtractor();
        $extractor
            ->addEntityToProcess('task', Task::class, function ($e) {
                return 'task' . $e->getProject()->getCode() . '-' . substr(md5($e->getName()), 0, 5);
            })
        ;
        $extractor->extract();
    }

    /**
     * @throws \Exception
     */
    public function testExtractEntities()
    {
        $extractor = $this->getExtractor();
        $extractor
            ->addEntityToProcess('project', Project::class, function ($e) {
                return $e->getCode();
            })
            ->addEntityToProcess('tag', Tag::class, function ($e) {
                return $e->getImportId();
            })
            ->addEntityToProcess('task', Task::class, function ($e) {
                return 'task' . $e->getProject()->getCode() . '-' . substr(md5($e->getName()), 0, 5);
            })
        ;
        $entities = $extractor->extract();
        $this->assertEquals(10, count($entities));

        $projectEtl = new Project('etl-bundle', 'ETL Bundle');
        $projectSonata = new Project('sonata-bundle', 'Sonata Bundle');

        $tagTodo = new Tag('Todo', 'todo');
        $tagDoing = new Tag('Doing', 'doing');
        $tagDone = new Tag('Done', 'done');
        $tagEasy = new Tag('Easy', 'easy');
        $tagHard = new Tag('Hard', 'hard');

        $taskA = new Task($projectEtl, 'Bundle setup');

        $taskB = new Task($projectEtl, 'Load yml entity file into database');
        $taskB->addTag($tagDoing);
        $taskB->addTag($tagEasy);

        $taskC = new Task($projectEtl, 'Export database entities to yml file');
        $taskC->addTag($tagTodo);
        $taskC->addTag($tagHard);

        $this->assertEquals([
            'etl-bundle' => $projectEtl,
            'sonata-bundle' => $projectSonata,
            'todo' => $tagTodo,
            'doing' => $tagDoing,
            'done' => $tagDone,
            'easy' => $tagEasy,
            'hard' => $tagHard,
            'tasketl-bundle-9d05b' => $taskA,
            'tasketl-bundle-519be' =>$taskB,
            'tasketl-bundle-c9264' => $taskC
        ], $entities);
    }
}
