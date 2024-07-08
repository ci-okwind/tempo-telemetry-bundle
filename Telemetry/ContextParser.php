<?php

namespace Okwind\TempoTelemetryBundle\Telemetry;

class ContextParser
{
    public const TRACE_PARENT_HEADER_NAME = 'traceparent';

    public static function parseHeaderTrace(string $header): ?array
    {
        // traceParent = {version}-{trace-id}-{parent-id}-{trace-flags}
        $pieces = explode('-', $header);

        if (count($pieces) != 4) {
            return null;
        }

        [$version, $traceId, $spanId, $traceFlags] = $pieces;
        if (!SpanContextValidator::isValidTraceId($traceId)
            || !SpanContextValidator::isValidSpanId($spanId)
        ) {
            return null;
        }

        return $pieces;
    }
}
