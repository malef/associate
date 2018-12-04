<?php

namespace Malef\AssociateTests\Functional\DoctrineOrm\Mock;

use Doctrine\DBAL\Logging\SQLLogger;

class DoctrineOrmQueryLogger implements SQLLogger
{
    /**
     * @var array
     */
    protected $queries = [];

    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->queries[] = [
            'sql' => $sql,
            'params' => $params,
            'types' => $types,
        ];
    }

    public function stopQuery()
    {
    }

    public function clearLoggedQueries(): void
    {
        $this->queries = [];
    }

    public function getLoggedQueries(): array
    {
        return $this->queries;
    }
}
