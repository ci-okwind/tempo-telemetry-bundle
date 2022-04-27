<?php

namespace tbn\TempoTelemetryBundle;

use Doctrine\DBAL\Logging\SQLLogger;

class DebugStack implements SQLLogger
{
    public $traces = [];
    public ?Trace $currentTrace = null;

    public function __construct(private Tempo $tempo)
    {
    }

    public function startQuery($sql, ?array $params = null, ?array $types = null)
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

    public function stopQuery()
    {
        $this->currentTrace->stop();
    }
}
