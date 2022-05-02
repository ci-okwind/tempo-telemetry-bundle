<?php

namespace tbn\TempoTelemetryBundle;

class TraceConverter
{
    public static function convert(Trace $trace, string $traceId, string $serviceName): array
    {
        $converted = [];

        $converted['id'] = $trace->getId();
        $converted['traceId'] = $traceId;
        $converted['timestamp'] = $trace->getTimestamp();
        $converted['duration'] = $trace->getDuration();
        $converted['name'] = $trace->getName();

        if ($trace->getParent()) {
            $converted['parentId'] = $trace->getParent()->getId();
        }

        $tags = [];
        /** @var Tag $tag  */
        foreach ($trace->getTags() as $tag) {
            $tags[$tag->name] = $tag->value;
        }

        $tags['app'] = $serviceName;

        $converted['tags'] = $tags;
        $converted['localEndpoint'] = [
            'serviceName' => $serviceName
        ];

        return $converted;
    }
}
