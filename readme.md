# Bundle for Tempo Telemetry

Install

```
composer require tbn/tempo-telemetry-bundle
```

## Add the config/packages/tempo_telemetry.yaml
```
tempo_telemetry:
    timeout: 1 # default value is 3
    service_name: 'my-service'
    tempo_url: 'http://yourtempo'
```

## Add the tempo DebugStack service
```
services:
    ...
when@prod:
    services:
        doctrine.dbal.logger:
            alias: tbn\TempoTelemetryBundle\DebugStack
```

# Usage

todo
