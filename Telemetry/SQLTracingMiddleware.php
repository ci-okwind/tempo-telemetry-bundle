<?php

namespace Okwind\TempoTelemetryBundle\Telemetry;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Driver\Result;

class SQLTracingMiddleware implements Middleware
{
    private array $traces = [];
    private ?Trace $currentTrace = null;

    public function __construct(private Tempo $tempo)
    {
    }

    public function wrap(Driver $driver): Driver
    {
        return new class ($driver, $this) extends AbstractDriverMiddleware {
            private SQLTracingMiddleware $middleware;

            public function __construct(Driver $wrappedDriver, SQLTracingMiddleware $middleware)
            {
                parent::__construct($wrappedDriver);
                $this->middleware = $middleware;
            }

            public function connect(array $params): Connection
            {
                $connection = parent::connect($params);
                
                return new class ($connection, $this->middleware) extends AbstractConnectionMiddleware {
                    private SQLTracingMiddleware $middleware;

                    public function __construct(Connection $wrappedConnection, SQLTracingMiddleware $middleware)
                    {
                        parent::__construct($wrappedConnection);
                        $this->middleware = $middleware;
                    }

                    public function prepare(string $sql): Statement
                    {
                        $stmt = parent::prepare($sql);
                        
                        return new class ($stmt, $sql, $this->middleware) extends AbstractStatementMiddleware {
                            private string $sql;
                            private SQLTracingMiddleware $middleware;

                            public function __construct(Statement $wrappedStatement, string $sql, SQLTracingMiddleware $middleware)
                            {
                                parent::__construct($wrappedStatement);
                                $this->sql = $sql;
                                $this->middleware = $middleware;
                            }

                            public function execute($params = null): Result
                            {
                                $this->middleware->startQuery($this->sql, $params);
                                try {
                                    $result = parent::execute($params);
                                    $this->middleware->stopQuery();
                                    return $result;
                                } catch (\Exception $e) {
                                    $this->middleware->stopQuery();
                                    throw $e;
                                }
                            }
                        };
                    }

                    public function query(string $sql): Result
                    {
                        $this->middleware->startQuery($sql);
                        try {
                            $result = parent::query($sql);
                            $this->middleware->stopQuery();
                            return $result;
                        } catch (\Exception $e) {
                            $this->middleware->stopQuery();
                            throw $e;
                        }
                    }

                    public function exec(string $sql): int
                    {
                        $this->middleware->startQuery($sql);
                        try {
                            $result = parent::exec($sql);
                            $this->middleware->stopQuery();
                            return $result;
                        } catch (\Exception $e) {
                            $this->middleware->stopQuery();
                            throw $e;
                        }
                    }
                };
            }
        };
    }

    public function startQuery($sql, ?array $params = null): void
    {
        // if any of the previous trace are still opened, then close it
        // in case of a sql crash then a rollback is runned
        foreach ($this->traces as $trace) {
            if (!$trace->isStopped()) {
                $trace->stop();
            }
        }

        $trace = new Trace(name: 'SQL');
        $trace->addTag(new Tag(name: 'sql', value: $sql));
        $trace->start();
        $this->traces[] = $trace;
        $this->currentTrace = $trace;

        $this->tempo->addTrace($this->currentTrace);
    }

    public function closesTraces(): void
    {
        // if any of the previous trace are still opened, then close it
        // in case of a sql crash then a rollback is runned
        foreach ($this->traces as $trace) {
            if (!$trace->isStopped()) {
                $trace->stop();
            }
        }
    }

    public function stopQuery(): void
    {
        if ($this->currentTrace !== null) {
            $this->currentTrace->stop();
        }
    }
}