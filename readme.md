# Bundle for Tempo Telemetry

Install

```
composer require tbn/tempo-telemetry-bundle
```


## Update your .env

```
TEMPO_URL='http://yourInstance:9411'
```


## Add the tempo service
```
services:
    tbn\TempoTelemetryBundle\Tempo:
        bind:
            string $serviceName: 'your service name'
            string $url: '%env(resolve:TEMPO_URL)%'
when@prod:
    services:
        doctrine.dbal.logger:
            alias: tbn\TempoTelemetryBundle\DebugStack
```

# Usage

todo