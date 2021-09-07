<?php

namespace Smart\EtlBundle\Tests\Loader;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Smart\EtlBundle\Exception\Loader\LoadUnvalidObjectsException;
use Smart\EtlBundle\Loader\DoctrineInsertUpdateLoader;
use Smart\EtlBundle\Tests\AbstractWebTestCase;
use Smart\EtlBundle\Tests\Entity\Milestone;
use Smart\EtlBundle\Tests\Entity\Organisation;
use Smart\EtlBundle\Tests\Entity\Project;
use Smart\EtlBundle\Tests\Entity\Tag;
use Smart\EtlBundle\Tests\Entity\Task;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * vendor/bin/phpunit tests/Loader/DoctrineInsertUpdateLoaderTest.php
 *
 * @author Nicolas Bastien <nicolas.bastien@smartbooster.io>
 */
class DoctrineInsertUpdateLoaderTest extends AbstractWebTestCase
{
    public function testLoad()
    {
        //Initialise database
        $em = $this->entityManager;
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

        $smartbooster = new Organisation();
        $smartbooster->setName('SMART BOOSTER updated');
        $smartbooster->setImportId('smartbooster');

        $projectEtl = new Project('etl-bundle', 'ETL Bundle');
        $projectEtl->setOrganisation($smartbooster);
        $projectEtl->setDescription('new description updated');

        $loader = new DoctrineInsertUpdateLoader($em, self::$container->get('validator'));
        $loader->addEntityToProcess(
            Organisation::class,
            function ($e) {
                return $e->getImportId();
            },
            'importId',
            [] //nothing to update, just for relation linking
        );
        $loader->addEntityToProcess(
            Project::class,
            function ($e) {
                return $e->getCode();
            },
            'code',
            [
                'organisation',
                'code',
                'name',
                'description'
            ]
        );
        $loader->addEntityToProcess(
            Task::class,
            function ($e) {
                return $e->getCode();
            },
            'code',
            [
                'project',
                'code',
                'name',
                'tags'
            ]
        );
        $loader->addEntityToProcess(
            Tag::class,
            function ($e) {
                return $e->getImportId();
            },
            'importId',
            ['name']
        );

        // Test updating an entity
        $loader->load([$projectEtl]);

        /** @var Project $projectEtlLoaded */
        $projectEtlLoaded = $em->getRepository(Project::class)->findOneBy([
            'code' => 'etl-bundle'
        ]);
        $this->assertEquals('new description updated', $projectEtlLoaded->getDescription());
        $this->assertTrue($projectEtlLoaded->isImported());
        $this->assertEquals(2, $em->getRepository(Project::class)->count([]));

        //Test relation linking
        $smartboosterDb = $projectEtlLoaded->getOrganisation();
        $this->assertEquals('SMART BOOSTER', $smartboosterDb->getName());

        //Test Insertion
        $newProject = new Project('new-project', 'new project');
        $loader->load([$projectEtl, $newProject]);
        $this->assertEquals(3, $em->getRepository(Project::class)->count([]));
        /** @var Project $projectEtlLoaded */
        $newProjectLoaded = $em->getRepository(Project::class)->findOneBy([
            'code' => 'new-project'
        ]);
        $this->assertEquals('new project', $newProjectLoaded->getName());
        $this->assertTrue($newProjectLoaded->isImported());
        $newProject->setName('new project updated');
        $loader->load([$projectEtl, $newProject]);
        $this->assertEquals(3, $em->getRepository(Project::class)->count([]));
        /** @var Project $projectEtlLoaded */
        $newProjectLoaded = $em->getRepository(Project::class)->findOneBy([
            'code' => 'new-project'
        ]);
        $this->assertEquals('new project updated', $newProjectLoaded->getName());

        //=======================
        //  Test relations
        //=======================
        $tagTodo = new Tag('Todo', 'todo');
        $tagDoing = new Tag('Doing', 'doing');
        $tagDone = new Tag('Done', 'done');
        $tagEasy = new Tag('Easy', 'easy');
        $tagHard = new Tag('Hard', 'hard');

        $this->assertEquals(2, $em->getRepository(Task::class)->count([]));
        $taskSetUp = new Task($projectEtl, 'Bundle setup updated');
        $taskSetUp->setCode('etl-bundle-setup');
        $taskSetUp->addTag($tagTodo);

        $newTask = new Task($projectEtl, 'New Task');
        $newTask->setCode('etl-bundle-new-task');
        $newTask->addTag($tagDoing);
        $newTask->addTag($tagEasy);

        $loader->load([$taskSetUp, $newTask]);

        $this->assertEquals(3, $em->getRepository(Task::class)->count([]));
        $newTaskLoaded = $em->getRepository(Task::class)->findOneBy([
            'code' => 'etl-bundle-new-task'
        ]);
        $this->assertEquals('New Task', $newTaskLoaded->getName());
        $this->assertEquals(2, $newTaskLoaded->getTags()->count());

        $newTask->setName('New Task updated');
        $loader->load([$taskSetUp, $newTask]);
        $this->assertEquals(3, $em->getRepository(Task::class)->count([]));
        $this->assertEquals('New Task updated', $newTaskLoaded->getName());

        //ManyToMany
        $taskSetUpLoaded = $em->getRepository(Task::class)->findOneBy([
            'code' => 'etl-bundle-setup'
        ]);
        $this->assertEquals(1, count($taskSetUpLoaded->getTags()));
        //Test manytomany remove and replace
        $tagTodoLoaded = $em->getRepository(Tag::class)->findOneBy(['importId' => 'todo']);
        $taskSetUp->removeTag($tagTodoLoaded);
        $taskSetUp->addTag($tagDone);

        $loader->load([$taskSetUp]);
        $taskSetUpLoaded = $em->getRepository(Task::class)->findOneBy([
            'code' => 'etl-bundle-setup'
        ]);
        $this->assertEquals(1, count($taskSetUpLoaded->getTags()));
        //Test manytomany remove
        $tagDoneLoaded = $em->getRepository(Tag::class)->findOneBy(['importId' => 'done']);
        $taskSetUp->removeTag($tagDoneLoaded);
        $loader->load([$taskSetUp]);
        $taskSetUpLoaded = $em->getRepository(Task::class)->findOneBy([
            'code' => 'etl-bundle-setup'
        ]);
        $this->assertEquals(0, count($taskSetUpLoaded->getTags()));
    }

    public function testLoadLogs()
    {
        $this->loadFixtureFiles([]);
        $project1 = new Project('p1', 'Project 1');
        $project2 = new Project('p2', 'Project 2');

        $loader = new DoctrineInsertUpdateLoader($this->entityManager, self::$container->get('validator'));
        $loader->addEntityToProcess(
            Project::class,
            function ($e) { return $e->getCode(); },
            'code',
            ['code', 'name',]
        );

        $loader->load([$project1, $project2]);
        // test nb_created logs
        $this->assertEquals([
            Project::class => ['nb_created' => 2, 'nb_updated' => 0],
        ], $loader->getLogs());

        // test nb_updated logs + nb_created on top with entityClass index already defined
        $loader->clearLogs();
        $project3 = new Project('p3', 'Project 3');
        $loader->load([$project1, $project2, $project3]);
        $this->assertEquals([
            Project::class => ['nb_created' => 1, 'nb_updated' => 2],
        ], $loader->getLogs());
    }

    public function testLoadUnvalidObjectsException()
    {
        $this->loadFixtureFiles([]);

        $project1 = new Project('p1', 'Project 1');
        $milestone1 = new Milestone();
        $milestone1->setName("Epic Milestone");
        $milestone2 = new Milestone();
        $milestone2->setName("Epic Milestone");

        $loader = new DoctrineInsertUpdateLoader($this->entityManager, self::$container->get('validator'));
        $loader->addEntityToProcess(
            Project::class,
            function ($e) { return $e->getCode(); },
            'code',
            ['code', 'name',]
        );
        $loader->addEntityToProcess(
            Milestone::class,
            function ($e) { return $e->getImportId(); },
            'code',
            ['name', 'project']
        );

        try {
            $loader->load([$milestone2, $project1, $milestone1]);
        } catch (\Exception $e) {
            $this->assertInstanceOf(LoadUnvalidObjectsException::class, $e);
            $this->assertCount(2, $e->arrayValidationErrors);
        }
    }
}
