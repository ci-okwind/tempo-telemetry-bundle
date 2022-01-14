<?php

namespace tbn\TempoTelemetryBundle;

class Tag
{
    public function __construct(public string $name, public string $value)
    {
    }
}
