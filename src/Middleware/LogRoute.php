<?php

namespace SgtCoder\LaravelFunctions\Middleware;

use App\Models\LogRoute as LogRouteModel;
use Closure;
use SgtCoder\LaravelFunctions\Support\RouteLogWriter;

use Illuminate\Http\{
    JsonResponse,
    Request,
    Response,
    UploadedFile
};

class LogRoute
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /**
         * LARAVEL_START is defined in public/index.php before the framework boots, so the
         * recorded total covers bootstrapping and the whole middleware stack rather than
         * only the portion of the request that runs inside this middleware. It is not
         * defined for requests that do not enter through public/index.php.
         */
        $started_at = defined('LARAVEL_START')
            ? LARAVEL_START
            : ($request->server('REQUEST_TIME_FLOAT') ?: microtime(true));

        $response = $next($request);

        $total_ms = (int) round((microtime(true) - $started_at) * 1000);

        // After $next() on purpose: consumers may request()->merge() during the controller.
        $attributes = $this->attributes($request, $response, $total_ms);

        // Redaction, caps and write mode live in the writer, shared with the log_route() helper.
        RouteLogWriter::write($attributes);

        return $response;
    }

    /**
     * Scalars and arrays only, so the payload can cross a queue.
     *
     * @return array<string, mixed>
     */
    protected function attributes(Request $request, mixed $response, int $total_ms): array
    {
        $user = $request->user();

        return [
            'model_type' => $user ? $user::class : null,
            'model_id' => $user->id ?? null,
            'api_provider' => 'system',
            'uri' => $request->getUri(),
            'request_headers' => $request->header(),
            'request_body' => $this->requestBody($request),
            'response_headers' => $response->headers->all(),
            'response_body' => $this->getResponseBody($response),
            'method' => $request->getMethod(),
            'ip' => $request->ip(),
            'http_code' => $response->getStatusCode(),
            'total_ms' => $total_ms,
        ];
    }

    /**
     * Request input with the model's ignorable keys nulled.
     *
     * input() rather than all(), which merges uploaded files.
     *
     * @return array<string, mixed>
     */
    protected function requestBody(Request $request): array
    {
        $body = $request->input();

        // @phpstan-ignore-next-line - property provided by the consuming application's model
        $ignorable = (array) ((new LogRouteModel)->ignorable ?: []);

        foreach ($ignorable as $ignore) {
            if (isset($body[$ignore])) {
                $body[$ignore] = 'NULLED';
            }
        }

        return $this->scrubUploads($body);
    }

    /**
     * Reduce any UploadedFile to its filename so the payload stays serialisable.
     *
     * @param  array<mixed>  $value
     * @return array<mixed>
     */
    protected function scrubUploads(array $value): array
    {
        foreach ($value as $key => $item) {
            if ($item instanceof UploadedFile) {
                $value[$key] = $item->getClientOriginalName();
            } elseif (is_array($item)) {
                $value[$key] = $this->scrubUploads($item);
            } elseif (is_object($item)) {
                $value[$key] = method_exists($item, '__toString') ? (string) $item : null;
            }
        }

        return $value;
    }

    /**
     * Get the response body content safely.
     *
     * @param  \Symfony\Component\HttpFoundation\Response  $response
     * @return mixed
     */
    private function getResponseBody($response)
    {
        if (!config('laravel-functions.log_route.store_response_body', true)) {
            return null;
        }

        // getContent() returns false for streamed and binary responses.
        if (!$response instanceof Response && !$response instanceof JsonResponse) {
            return null;
        }

        try {
            $content = $response->getContent();

            if ($content === false) {
                return null;
            }

            // Try to decode JSON responses
            if (
                $response->headers->get('Content-Type') &&
                str_contains($response->headers->get('Content-Type'), 'application/json')
            ) {
                return json_decode($content, true);
            }

            // RouteLogWriter applies the size cap.
            return $content;
        } catch (\Exception $e) {
            return 'Error retrieving response body: ' . $e->getMessage();
        }
    }
}
