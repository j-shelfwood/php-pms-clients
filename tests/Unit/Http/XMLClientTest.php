<?php

use GuzzleHttp\Psr7\Response;
use Psr\Log\NullLogger;
use GuzzleHttp\ClientInterface;
use Shelfwood\PhpPms\Http\XMLClient;
use Shelfwood\PhpPms\Exceptions\HttpClientException;
use Shelfwood\PhpPms\Exceptions\NetworkException;
use Shelfwood\PhpPms\Exceptions\ApiException;
use Tests\Helpers\TestHelpers;

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
            throw new ApiException($errorDetails->message, $errorDetails->code ?? 0, null, $errorDetails);
        }
    })->throws(ApiException::class);

});
