<?php

namespace tbn\TempoTelemetryBundle\Telemetry;

class IdGenerator
{
    private const TRACE_ID_HEX_LENGTH = 32;
    private const SPAN_ID_HEX_LENGTH = 16;

    public static function generateTraceId(): string
    {
        return self::randomHex(self::TRACE_ID_HEX_LENGTH);
    }

    public static function generateSpanId(): string
    {
        return self::randomHex(self::SPAN_ID_HEX_LENGTH);
    }

    public static function randomHex(int $hexLength): string
    {
        return bin2hex(random_bytes(intdiv($hexLength, 2)));
    }
}
