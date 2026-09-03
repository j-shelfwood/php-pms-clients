<?php

use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Shelfwood\PhpPms\Http\XMLClient;
use Shelfwood\PhpPms\Exceptions\HttpClientException;
use Shelfwood\PhpPms\Exceptions\NetworkException;
use Shelfwood\PhpPms\Exceptions\ApiException;
use Tests\Helpers\TestHelpers;
use Tests\Support\InMemoryCache;

class TestXMLClient extends XMLClient
{
    public function publicExecutePostRequest(string $url, array $formData): string
    {
        return $this->executePostRequest($url, $formData);
    }
}
describe('XMLClientTest', function () {

    it('throws on HTTP error', function () {
        $mock = Mockery::mock(ClientInterface::class);
        $mock->shouldReceive('request')
            ->andThrow(new \GuzzleHttp\Exception\RequestException('fail', new \GuzzleHttp\Psr7\Request('POST', 'test')));

        $client = new TestXMLClient('http://test', 'apikey', $mock, new NullLogger());
        $client->publicExecutePostRequest('http://test/endpoint', []);
    })->throws(NetworkException::class);

    it('maps a cURL timeout (ConnectException) to NetworkException', function () {
        $mock = Mockery::mock(ClientInterface::class);
        $mock->shouldReceive('request')
            ->andThrow(new \GuzzleHttp\Exception\ConnectException(
                'cURL error 28: Operation timed out after 30001 milliseconds with 179618 bytes received',
                new \GuzzleHttp\Psr7\Request('POST', 'test')
            ));

        $client = new TestXMLClient('http://test', 'apikey', $mock, new NullLogger());

        try {
            $client->publicExecutePostRequest('http://test/endpoint', []);
            $this->fail('Expected NetworkException');
        } catch (NetworkException $e) {
            expect($e->getPrevious())->toBeInstanceOf(\GuzzleHttp\Exception\ConnectException::class)
                ->and($e->getMessage())->toContain('cURL error 28');
        }
    });

    it('passes a configured timeout to Guzzle and defaults to 30', function () {
        $seen = [];
        $mock = Mockery::mock(ClientInterface::class);
        $mock->shouldReceive('request')->twice()->andReturnUsing(function ($m, $u, $o) use (&$seen) {
            $seen[] = $o['timeout'];
            return new Response(200, [], '<ok/>');
        });

        (new TestXMLClient('http://test', 'k', $mock, new NullLogger()))->publicExecutePostRequest('http://test/e', []);
        (new TestXMLClient('http://test', 'k', $mock, new NullLogger(), null, null, 60, 60, 90))->publicExecutePostRequest('http://test/e', []);

        expect($seen)->toBe([30, 90]);
    });

    it('rejects a non-positive timeout', function () {
        new TestXMLClient('http://test', 'k', Mockery::mock(ClientInterface::class), new NullLogger(), null, null, 60, 60, 0);
    })->throws(\InvalidArgumentException::class);

    it('throws on API error in XML', function () {
        $mock = Mockery::mock(ClientInterface::class);
        $mock->shouldReceive('request')
            ->andReturn(new Response(200, [], '<Errors><Error Code="123" ShortText="Failure"/></Errors>'));

        $client = new TestXMLClient('http://test', 'apikey', $mock, new NullLogger());
        // Simulate the new flow: parse XML and check for API error
        $xml = '<Errors><Error Code="123" ShortText="Failure"/></Errors>';
        $parsed = \Shelfwood\PhpPms\Http\XMLParser::parse($xml);
        if (\Shelfwood\PhpPms\Http\XMLParser::hasError($parsed)) {
            $errorDetails = \Shelfwood\PhpPms\Http\XMLParser::extractErrorDetails($parsed);
            throw new ApiException($errorDetails->message, is_numeric($errorDetails->code) ? (int)$errorDetails->code : 0, null, $errorDetails);
        }
    })->throws(ApiException::class);

    it('throttles using shared cache', function () {
        $mock = Mockery::mock(ClientInterface::class);
        $mock->shouldReceive('request')
            ->twice()
            ->andReturn(
                new Response(200, [], '<success/>'),
                new Response(200, [], '<success/>')
            );

        $cache = new InMemoryCache();
        $client = new TestXMLClient(
            'http://test',
            'apikey',
            $mock,
            new NullLogger(),
            $cache,
            'booking_manager:apikey',
            1,
            1
        );

        $start = microtime(true);
        $client->publicExecutePostRequest('http://test/endpoint', []);
        $client->publicExecutePostRequest('http://test/endpoint', []);
        $elapsed = microtime(true) - $start;

        expect($elapsed)->toBeGreaterThan(0.9);
    });

});
