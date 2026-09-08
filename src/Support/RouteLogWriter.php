<?php

namespace SgtCoder\LaravelFunctions\Support;

use App\Models\LogRoute as LogRouteModel;
use SgtCoder\LaravelFunctions\Jobs\WriteRouteLog;
use Throwable;

/**
 * The one place a log_routes row is sanitised and written. Both the log.route middleware and the
 * log_route() helper land here, so a row is treated the same whichever produced it - and neither
 * can throw into the request or call it describes.
 */
class RouteLogWriter
{
    /**
     * Always replaced before storage, on top of the configured list.
     *
     * @var list<string>
     */
    public const ALWAYS_REDACT_HEADERS = [
        'authorization',
        'cookie',
        'proxy-authorization',
        'set-cookie',
        'x-api-key',
        'xi-api-key',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function write(array $attributes): void
    {
        $attributes = static::sanitize($attributes);

        $mode = config('laravel-functions.log_route.mode', 'sync');

        if ($mode === 'queue') {
            try {
                WriteRouteLog::dispatch($attributes)
                    ->onConnection(config('laravel-functions.log_route.connection'))
                    ->onQueue(config('laravel-functions.log_route.queue'));

                return;
            } catch (Throwable $exception) {
                // An unreachable queue must not turn a successful response into a 500; fall
                // through to the inline write, which is what sync mode does anyway.
                report($exception);
            }
        }

        if ($mode === 'after_response') {
            app()->terminating(fn() => static::insert($attributes));

            return;
        }

        static::insert($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function insert(array $attributes): void
    {
        try {
            // @phpstan-ignore-next-line - the model lives in the consuming application
            LogRouteModel::create($attributes);
        } catch (Throwable $exception) {
            // Losing a log row is preferable to failing the request or API call it describes.
            report($exception);
        }
    }

    /**
     * Applied before the queue rather than in the worker, so a secret never reaches the payload.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function sanitize(array $attributes): array
    {
        foreach (['request_headers', 'response_headers'] as $key) {
            if (isset($attributes[$key]) && is_array($attributes[$key])) {
                $attributes[$key] = static::redactHeaders($attributes[$key]);
            }
        }

        $attributes['request_body'] = static::truncate(
            $attributes['request_body'] ?? null,
            (int) config('laravel-functions.log_route.max_request_bytes', 10000)
        );

        $attributes['response_body'] = static::truncate(
            $attributes['response_body'] ?? null,
            (int) config('laravel-functions.log_route.max_response_bytes', 10000)
        );

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $headers
     * @return array<string, mixed>
     */
    public static function redactHeaders(array $headers): array
    {
        $redact = array_unique(array_merge(
            static::ALWAYS_REDACT_HEADERS,
            array_map('strtolower', (array) config('laravel-functions.log_route.redact_headers', []))
        ));

        foreach ($headers as $name => $value) {
            if (in_array(strtolower((string) $name), $redact, true)) {
                $headers[$name] = ['REDACTED'];
            }
        }

        return $headers;
    }

    /**
     * A string is cut; an array is replaced wholesale by a marker, because cutting a decoded
     * structure in place would leave something that reads as a complete-but-wrong payload.
     */
    public static function truncate(mixed $value, int $max): mixed
    {
        if ($max <= 0 || $value === null || is_scalar($value) && ! is_string($value)) {
            return $value;
        }

        if (is_string($value)) {
            return strlen($value) > $max ? substr($value, 0, $max) . '...' : $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        $encoded = json_encode($value);

        if ($encoded === false || strlen($encoded) <= $max) {
            return $value;
        }

        return [
            'truncated' => true,
            'bytes' => strlen($encoded),
            'preview' => substr($encoded, 0, $max) . '...',
        ];
    }
}
