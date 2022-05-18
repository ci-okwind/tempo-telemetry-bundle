<?php

namespace tbn\TempoTelemetryBundle;

use Psr\Log\LoggerInterface;

class Tempo
{
    private array $traces = [];
    private ?string $traceId = null;
    private ?Trace $currentTrace = null;
    private ?Trace $rootTrace = null;
    private string $rootSpanId;
    private ?string $callerSpanId = null;
    private bool $doSend = true;

    const TIMEOUT = 3;

    public function __construct(private LoggerInterface $logger, private string $serviceName, private string $url)
    {
    }

    public function setRootTrace(Trace $trace): void
    {
        $this->purgeTraces();
        $this->rootSpanId = $trace->getId();

        if ($this->callerSpanId) {
            $trace->setParent(new Trace(name:'fakeRoot', id: $this->callerSpanId));
        }

        $this->rootTrace = $trace;
        $this->setCurrentTrace($trace);
        $this->traces[] = $trace;
    }

    public function setCurrentTrace(Trace $trace): void
    {
        $this->currentTrace = $trace;
    }

    public function addTrace(Trace $trace): void
    {
        $trace->setParent($this->currentTrace);
        $this->traces[] = $trace;
    }

    public function getTraceId(): string
    {
        if (is_null($this->traceId)) {
            $this->traceId = IdGenerator::generateTraceId();
        };

        return $this->traceId;
    }

    public function setTraceId(string $value): void
    {
        $this->traceId = $value;
    }

    public function getRootTraceId(): string
    {
        if (is_null($this->rootTrace)) {
            $this->rootSpanId = IdGenerator::generateSpanId();
        }
        return $this->rootSpanId;
    }

    public function setCallerSpanId(string $value): void
    {
        $this->callerSpanId = $value;
    }

    public function disableSend(): void
    {
        $this->doSend = false;
    }

    public function enableSend(): void
    {
        $this->doSend = true;
    }

    public function send()
    {
        if (false === $this->doSend) {
            return;
        }

        $alls = [];

        try {
            while ($trace = array_shift($this->traces)) {
                $alls[] = TraceConverter::convert($trace, $this->getTraceId(), $this->serviceName);
            }

            $client = new \GuzzleHttp\Client();
            $client->request('POST', $this->url, ['json' => $alls, 'timeout' => static::TIMEOUT]);
        } catch (\Exception $ex) {
            $this->logger->warning($ex->getMessage());
        }
    }

    public function purgeTraces(): void
    {
        $this->traces = [];
    }
}
