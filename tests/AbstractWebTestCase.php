<?php

namespace Smart\EtlBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractWebTestCase extends WebTestCase
{
    use FixturesTrait;

    protected ?KernelBrowser $client = null;

    protected ?EntityManagerInterface $entityManager;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        /** @var EntityManagerInterface $em */
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();
        $this->entityManager = $em;
    }

    protected static function getKernelClass()
    {
        require_once './tests/app/AppKernel.php';

        return 'AppKernel';
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // avoid memory leaks
        if ($this->entityManager != null) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

    protected function getFixtureDir(): string
    {
        return __DIR__ . '/fixtures';
    }

    /**
     * https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
     * Méthode permettant d'appeler les méthodes private ou protected des classes pour pouvoir les tester
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     * @throws \ReflectionException
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
