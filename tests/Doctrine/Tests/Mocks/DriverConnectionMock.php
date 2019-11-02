<?php

declare(strict_types=1);

namespace Doctrine\Tests\Mocks;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;

/**
 * Mock class for DriverConnection.
 */
class DriverConnectionMock implements Connection
{
    /** @var Statement */
    private $statementMock;

    /**
     * @return Statement
     */
    public function getStatementMock()
    {
        return $this->statementMock;
    }

    /**
     * @param Statement $statementMock
     */
    public function setStatementMock($statementMock)
    {
        $this->statementMock = $statementMock;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(string $prepareString) : Statement
    {
        return $this->statementMock ?: new StatementMock();
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $sql) : ResultStatement
    {
        return $this->statementMock ?: new StatementMock();
    }

    /**
     * {@inheritdoc}
     */
    public function quote($input, $type = ParameterType::STRING)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function exec(string $statement) : int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function lastInsertId($name = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rollBack()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo()
    {
    }
}
