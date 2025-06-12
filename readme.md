# Bundle for Tempo Telemetry

This bundle provides SQL query tracing for Doctrine DBAL 4.0+ using the middleware approach. It sends trace data to a Tempo instance for monitoring and analysis.

## Installation

Install the bundle via Composer:

```bash
composer require okwind/tempo-telemetry-bundle
```

## Configuration

### 1. Register the bundle in your application

For Symfony applications, add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    Okwind\TempoTelemetryBundle\TempoTelemetryBundle::class => ['all' => true],
];
```

### 2. Create a configuration file

Create a file at `config/packages/tempo_telemetry.yaml` with the following content:

```yaml
tempo_telemetry:
    timeout: 1 # default value is 3
    service_name: 'my-service'
    tempo_url: 'http://yourtempo'
```

Parameters:
- `timeout`: Connection timeout in seconds (default: 3)
- `service_name`: Name of your service (required)
- `tempo_url`: URL of your Tempo instance (required)

### 3. Register the SQLTracingMiddleware with Doctrine DBAL

Add the following to your services configuration (e.g., `config/services.yaml`):

```yaml
services:
    # ...

    # For all environments
    Okwind\TempoTelemetryBundle\Telemetry\Tempo:
        public: true
        arguments:
            - '@logger'
            - '%Okwind.tempo_telemetry.service_name%'
            - '%Okwind.tempo_telemetry.tempo_url%'
            - '%Okwind.tempo_telemetry.timeout%'

    # Only in production environment (recommended)
    when@prod:
        services:
            # Register the middleware with Doctrine DBAL
            doctrine.dbal.middleware.sql_tracing:
                class: Okwind\TempoTelemetryBundle\Telemetry\SQLTracingMiddleware
                arguments:
                    - '@Okwind\TempoTelemetryBundle\Telemetry\Tempo'
                tags:
                    - { name: doctrine.middleware }
```
