<?php

namespace tbn\TempoTelemetryBundle\Telemetry;

class Tag
{
    public function __construct(public string $name, public string $value)
    {
    }
}
