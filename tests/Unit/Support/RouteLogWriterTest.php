<?php

namespace SgtCoder\LaravelFunctions\Tests\Unit\Support;

use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\Attributes\Test;
use SgtCoder\LaravelFunctions\Jobs\WriteRouteLog;
use SgtCoder\LaravelFunctions\Tests\TestCase;

use Illuminate\Support\Facades\{
    Queue,
    Schema
};

/**
 * Pins that log_route()'s outbound rows get the same treatment as the inbound middleware's:
 * headers redacted, bodies capped, write mode honoured, failures kept away from the caller.
 */
class RouteLogWriterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('log_routes', function (Blueprint $table) {
            $table->id();
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('api_provider')->nullable();
            $table->string('uri');
            $table->text('request_headers')->nullable();
            $table->text('request_body')->nullable();
            $table->string('method');
            $table->string('ip')->nullable();
            $table->string('http_code');
            $table->unsignedInteger('total_ms')->nullable();
            $table->text('response_headers')->nullable();
            $table->text('response_body')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function log(array $overrides = []): void
    {
        log_route(array_merge([
            'api_provider' => 'App\Services\ExampleApiService',
            'uri' => 'https://api.example.com/v1/resources/resource_1',
            'request_headers' => ['x-api-key' => 'sk_live_super_secret'],
            'request_body' => ['a' => 1],
            'response_headers' => ['content-type' => ['application/json']],
            'response_body' => '{"ok":true}',
            'method' => 'get',
            'status_code' => 200,
            'total_ms' => 12,
        ], $overrides));
    }

    private function row(): ?object
    {
        // @phpstan-ignore-next-line - the stub model stands in for the consuming application's
        return \App\Models\LogRoute::query()->first();
    }

    /** The row describing an authenticated call must not be a copy of its credential. */
    #[Test]
    public function it_redacts_the_provider_api_key_from_an_outbound_call()
    {
        $this->log();

        $headers = $this->row()->request_headers;

        $this->assertSame(['REDACTED'], $headers['x-api-key']);
        $this->assertStringNotContainsString('sk_live_super_secret', json_encode($headers));
    }

    #[Test]
    public function it_redacts_a_set_cookie_response_header()
    {
        $this->log(['response_headers' => ['set-cookie' => ['session=abcdef']]]);

        $this->assertSame(['REDACTED'], $this->row()->response_headers['set-cookie']);
    }

    #[Test]
    public function it_caps_an_oversized_response_body()
    {
        config()->set('laravel-functions.log_route.max_response_bytes', 200);

        $this->log(['response_body' => str_repeat('x', 5000)]);

        $body = $this->row()->response_body;

        $this->assertSame(203, strlen($body));
        $this->assertStringEndsWith('...', $body);
    }

    /** Replaced wholesale rather than cut, so it cannot be mistaken for a complete payload. */
    #[Test]
    public function it_replaces_an_oversized_array_body_with_a_marker()
    {
        config()->set('laravel-functions.log_route.max_response_bytes', 200);

        $this->log(['response_body' => ['recipients' => array_fill(0, 200, ['status' => 'completed'])]]);

        $body = $this->row()->response_body;

        $this->assertTrue($body['truncated']);
        $this->assertGreaterThan(200, $body['bytes']);
        $this->assertStringEndsWith('...', $body['preview']);
    }

    #[Test]
    public function it_leaves_a_body_under_the_cap_alone()
    {
        $this->log(['response_body' => ['ok' => true]]);

        $this->assertSame(['ok' => true], $this->row()->response_body);
    }

    /** A frequent caller should not be paying for the insert on its own thread. */
    #[Test]
    public function it_honours_queue_mode_for_outbound_calls()
    {
        Queue::fake();
        config()->set('laravel-functions.log_route.mode', 'queue');
        config()->set('laravel-functions.log_route.queue', 'log_route');

        $this->log();

        $this->assertNull($this->row(), 'queue mode must not write on the calling thread');

        Queue::assertPushed(WriteRouteLog::class, fn(WriteRouteLog $job) => $job->attributes['http_code'] === 200);
    }

    /** A secret must not reach the queue payload either, where it would sit until the worker ran. */
    #[Test]
    public function it_sanitises_before_the_queue_not_in_the_worker()
    {
        Queue::fake();
        config()->set('laravel-functions.log_route.mode', 'queue');

        $this->log();

        Queue::assertPushed(WriteRouteLog::class, function (WriteRouteLog $job) {
            $this->assertStringNotContainsString('sk_live_super_secret', serialize($job->attributes));

            return true;
        });
    }

    /** A log write must never be able to fail the API call it describes. */
    #[Test]
    public function a_failed_write_does_not_throw_into_the_caller()
    {
        Schema::drop('log_routes');

        $this->log();

        $this->assertFalse(Schema::hasTable('log_routes'), 'log_route() returned rather than throwing');
    }

    #[Test]
    public function it_defers_the_write_until_termination()
    {
        config()->set('laravel-functions.log_route.mode', 'after_response');

        $this->log();

        $this->assertNull($this->row());

        $this->app->terminate();

        $this->assertNotNull($this->row());
    }
}
