<?php

namespace Tests\Feature\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Mocks\HasRssMocks;
use Tests\TestCase;

/**
 * Class EpisodeImportTest
 *
 * @author Vincent Neubauer <v.neubauer@darlor.de>
 */
class EpisodeImportTest extends TestCase
{
    use HasRssMocks;
    use RefreshDatabase;

    private MockHandler $handler;

    public function validResponseProvider(): array
    {
        // wrap each response string into an array
        return array_map(fn($response) => [$response], $this->getRssResponses());
    }

    /** @dataProvider validResponseProvider */
    public function test_import_from_feed($xml)
    {
        $this->handler->append(new Response(200, [], $xml));
        $this->artisan('podcast:import')
            ->assertExitCode(0);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new MockHandler();
        $this->app->instance(ClientInterface::class, new Client([
            'handler' => new HandlerStack($this->handler),
        ]));
    }
}
