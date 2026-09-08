<?php

if (!function_exists('get_log_name')) {
    /**
     * get_log_name
     *
     * @param  mixed $signature
     * @return mixed
     */
    function get_log_name($signature)
    {
        $log_name = explode(' ', $signature);
        $log_name = $log_name[0];
        $log_name = str_replace(["\r\n", "\r", "\n"], '', $log_name);

        return $log_name;
    }
}

if (!function_exists('log_string')) {
    /**
     * log_string
     *
     * @param  mixed $signature
     * @param  mixed $type
     * @param  mixed $message
     * @param  mixed $disable_timestamp
     * @param  mixed $newline
     * @return mixed
     */
    function log_string($signature = null, $type = 'DEFAULT', $message = null, $disable_timestamp = false, $newline = false)
    {
        if ($signature === null) return '';

        $log_name = get_log_name($signature);

        $log = null;
        if (!$disable_timestamp) $log = '[' . now()->format('Y-m-d H:i:s') . '][' . $log_name . '][' . $type . ']: ';

        $template = match ($type) {
            'INFO' => '<fg=#ffc107;options=bold>' . $log . '</>' . $message,
            'SUCCESS' => '<fg=#28a745;options=bold>' . $log . '</>' . $message,
            'WARNING' => '<fg=#ffc107;options=bold>' . $log . '</>' . $message,
            'ERROR' => '<fg=#dc3545;options=bold>' . $log . '</>' . $message,
            'DEFAULT' => '<fg=#000000;options=bold>' . $log . '</>' . $message,
            default => '<fg=#000000;options=bold>' . $log . '</>' . $message,
        };

        if ($newline) $template .= "\n";

        return $template;
    }
}

if (!function_exists('console_log')) {
    /**
     * console_log
     *
     * @param  mixed $signature
     * @param  mixed $type
     * @param  mixed $message
     * @param  mixed $disable_timestamp
     * @param  mixed $newline
     * @return mixed
     */
    function console_log($signature = null, $type = 'DEFAULT', $message = null, $disable_timestamp = false, $newline = false)
    {
        $template = log_string($signature, $type, $message, $disable_timestamp, $newline);

        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $output->writeln($template);

        return true;
    }
}

if (!function_exists('console_log_line')) {
    /**
     * console_log_line
     *
     * @return mixed
     */
    function console_log_line()
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $output->writeln('');

        return true;
    }
}

if (!function_exists('command_log_name')) {
    /**
     * command_log_name
     *
     * @param  string $command
     * @return string
     */
    function command_log_name($command)
    {
        $command = explode(' ', $command)[0];

        $command = slugify($command);
        $command = $command . '.log';

        return $command;
    }
}

if (!function_exists('log_channel')) {
    /**
     * log_channel
     *
     * @param  mixed $type
     * @param  mixed $message
     * @param  mixed $args
     * @param  mixed $channel
     * @param  mixed $log_name
     * @return mixed
     */
    function log_channel($type, $message, $args = [], $channel = null, $log_name = false)
    {
        $channel ??= config('logging.default');

        if ($log_name) {
            $message = get_log_name($message);
        }

        \Illuminate\Support\Facades\Log::channel($channel)->{$type}($message, [$args]);

        return true;
    }
}

if (!function_exists('log_response')) {
    /**
     * log_response
     *
     * @param  mixed $type
     * @param  mixed $message
     * @param  mixed $args
     * @param  mixed $log_name
     * @return mixed
     */
    function log_response($type, $message, $args = [], $log_name = 'api_logs')
    {
        $args = is_array($args) ? $args : [$args];
        $channel = \Illuminate\Support\Facades\Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/' . $log_name . '.log'),
        ]);

        $channel->{$type}($message, $args);
    }
}

if (!function_exists('log_route')) {
    /**
     * Record one outbound API call in log_routes, the counterpart to the log.route middleware.
     * Both hand their row to RouteLogWriter, so both get the same redaction, caps and mode.
     *
     * A console caller has no authenticated user or client IP, so both are left null rather than
     * recording the scheduler's own.
     *
     * @param  array<string, mixed>  $data
     * @return void
     */
    function log_route($data)
    {
        $user = app()->runningInConsole() ? null : request()->user();

        \SgtCoder\LaravelFunctions\Support\RouteLogWriter::write([
            'model_type' => $user ? $user::class : null,
            'model_id' => $user->id ?? null,
            'api_provider' => $data['api_provider'],
            'uri' => $data['uri'],
            'request_headers' => $data['request_headers'] ?? [],
            'request_body' => $data['request_body'] ?? [],
            'response_headers' => $data['response_headers'] ?? [],
            'response_body' => $data['response_body'] ?? [],
            'method' => $data['method'],
            'ip' => app()->runningInConsole() ? null : request()->ip(),
            'http_code' => $data['status_code'],
            'total_ms' => $data['total_ms'] ?? null,
        ]);
    }
}
