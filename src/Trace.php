<?php

namespace tbn\TempoTelemetryBundle;

class Trace
{
    private int $timestamp; // in seconds
    private int $startTimestamp; // in microseconds
    private ?int $stopTimestamp = null; // in microseconds
    private string $id;
    private array $tags = array();
    private ?Trace $parent = null;

    public function __construct(private string $name, ?string $id = null)
    {
        if (is_null($id)) {
            $this->id = IdGenerator::generateSpanId();
        } else {
            $this->id = $id;
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setParent(?Trace $trace): void
    {
        $this->parent = $trace;
    }

    public function getParent(): ?Trace
    {
        return $this->parent;
    }

    public function addTag(Tag $tag): void
    {
        $this->tags[] = $tag;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
    public function isStopped(): bool
    {
        return !is_null($this->stopTimestamp);
    }

    public function getDuration(): int
    {
        // failsafe in cases of an unexpected exception
        if (!$this->isStopped()) {
            $this->stop();
        }

        return intval(($this->stopTimestamp - $this->startTimestamp) / 1000);
    }

    public function start(): void
    {
        $this->timestamp = intval(microtime(true) * 1000000);
        $this->startTimestamp = hrtime(true);
    }

    public function stop(): void
    {
        $this->stopTimestamp = hrtime(true);
    }
}
