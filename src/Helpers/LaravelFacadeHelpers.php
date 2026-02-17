<?php

if (!function_exists('artisan')) {
    /**
     * artisan
     *
     * @return \Illuminate\Support\Facades\Artisan
     */
    function artisan()
    {
        return \Illuminate\Support\Facades\Artisan::getFacadeRoot();
    }
}

if (!function_exists('http')) {
    /**
     * http
     *
     * @return \Illuminate\Support\Facades\Http
     */
    function http()
    {
        return \Illuminate\Support\Facades\Http::getFacadeRoot();
    }
}

if (!function_exists('http_safe')) {
    /**
     * Same fluent API as Http::. get/post/put/patch/delete/head return null on ConnectionException.
     * pool() returns the array of responses directly (not the wrapper).
     */
    function http_safe(): object
    {
        return new class {
            private $client;

            public function __call(string $method, array $args): mixed
            {
                $this->client ??= \Illuminate\Support\Facades\Http::getFacadeRoot();
                if (in_array($method, ['get', 'post', 'put', 'patch', 'delete', 'head'])) {
                    try {
                        return $this->client->{$method}(...$args);
                    } catch (\Illuminate\Http\Client\ConnectionException $e) {
                        return null;
                    }
                }
                if ($method === 'pool') {
                    return $this->client->pool(...$args);
                }
                $this->client = $this->client->{$method}(...$args);
                return $this;
            }
        };
    }
}

if (!function_exists('storage')) {
    /**
     * storage
     *
     * @return \Illuminate\Support\Facades\Storage
     */
    function storage()
    {
        return \Illuminate\Support\Facades\Storage::getFacadeRoot();
    }
}

if (!function_exists('laravel_cookie')) {
    /**
     * laravel_cookie
     *
     * @return \Illuminate\Support\Facades\Cookie
     */
    function laravel_cookie()
    {
        return \Illuminate\Support\Facades\Cookie::getFacadeRoot();
    }
}

if (!function_exists('laravel_file')) {
    /**
     * laravel_file
     *
     * @return \Illuminate\Support\Facades\File
     */
    function laravel_file()
    {
        return \Illuminate\Support\Facades\File::getFacadeRoot();
    }
}
