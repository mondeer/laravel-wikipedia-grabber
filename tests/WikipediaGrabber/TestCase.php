<?php

namespace Illuminated\Wikipedia\WikipediaGrabber\Tests;

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;
use Illuminated\Wikipedia\ServiceProvider;
use Mockery;

Mockery::globalHelpers();

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    protected function resolveApplicationConfiguration($app)
    {
        $orchestraConfig = $this->getBasePath() . '/config/wikipedia-grabber.php';
        copy(__DIR__ . '/fixture/config/wikipedia-grabber.php', $orchestraConfig);

        parent::resolveApplicationConfiguration($app);

        unlink($orchestraConfig);
    }

    protected function mockWikipediaQuery()
    {
        $body = json_encode([
            'query' => [
                'pages' => [[
                    'pageid' => 1234567,
                    'title' => 'Mocked Page',
                    'extract' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    'revisions' => [[
                        'contentformat' => 'text/x-wiki',
                        'contentmodel' => 'wikitext',
                        'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                    ]],
                ]],
            ],
        ]);
        $response = new Response(200, ['Content-Type' => 'application/json'], Psr7\stream_for($body));

        $client = mock('overload:GuzzleHttp\Client');
        $client->expects()->get('', Mockery::any())->andReturn($response);
    }
}
