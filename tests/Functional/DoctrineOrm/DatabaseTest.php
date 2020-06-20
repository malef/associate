<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm;

use Doctrine\ORM\Tools\ToolsException;
use Malef\AssociateTests\Functional\DoctrineOrm\Mock\BaseFixtureProvider;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\Database\DefaultConnection;
use PHPUnit\DbUnit\DataSet\ArrayDataSet;
use PHPUnit\DbUnit\DataSet\IDataSet;
use PHPUnit\DbUnit\TestCaseTrait;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTest extends TestCase
{
    use TestCaseTrait;

    /**
     * @var BaseFixtureProvider
     */
    private $dataProvider;

    /**
     * @var \PDO
     */
    private static $pdo = null;

    /**
     * @var Connection
     */
    private $conn = null;

    /**
     * @return DefaultConnection
     *
     * @throws ToolsException
     */
    final public function getConnection()
    {
        $entityManager = $this->getFixtureProvider()->getEntityManager();
        $pdo = $entityManager->getConnection()->getWrappedConnection();

        $entityManager->clear();
        $this->getFixtureProvider()->dropSchema();
        $this->getFixtureProvider()->createSchema();
        $entityManager->clear();

        return $this->createDefaultDBConnection($pdo);
    }

    /**
     * @return ArrayDataSet|IDataSet
     */
    public function getDataSet()
    {
        return $this->createArrayDataSet(
            $this->getFixtureProvider()->getArrayDataSetRawData()
        );
    }

    public function getFixtureProvider(): BaseFixtureProvider
    {
        if (!$this->dataProvider instanceof BaseFixtureProvider) {
            $this->dataProvider = $this->createDataProvider();
        }

        return $this->dataProvider;
    }

    abstract protected function createDataProvider(): BaseFixtureProvider;
}
