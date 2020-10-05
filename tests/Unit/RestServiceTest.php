<?php

namespace RestService\Tests\Unit;

use Exception;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use RestService\RestService;

/**
 * Class RestServiceTest
 * @package RestService\Tests\Unit
 */
class RestServiceTest extends UnitTestCase
{
    /**
     * @var RestService
     */
    protected $restService;

    /**
     * @var $endpoint
     */
    protected $endpoint;

    protected function setUp(): void
    {
        $this->endpoint = 'https://test.com';
    }

    public function testNullEndpointThrowsException()
    {
        $this->expectExceptionMessage("Invalid null endpoint");
        $this->expectException(Exception::class);
        $mock = new MockHandler();
        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $restService->get('/posts');
    }


    public function testCanCreateGetRequest()
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'])
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $response = $restService
            ->setEndpoint($this->endpoint)
            ->get('/posts', [], [], false);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('Bar', $response->getHeader('X-Foo')[0]);
    }

    public function testCanCreatePostRequest()
    {
        $mock = new MockHandler([
            new Response(200)
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $response = $restService
            ->setEndpoint($this->endpoint)
            ->post('/posts', [], [], false);

        self::assertEquals(200, $response->getStatusCode());
    }

    public function testCanCreatePutRequest()
    {
        $mock = new MockHandler([
            new Response(201)
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $requestData = [
            "id" => 1,
            "value" => 'test'
        ];

        $response = $restService
            ->setEndpoint($this->endpoint)
            ->put('/posts/1', $requestData, [], false);

        self::assertEquals(201, $response->getStatusCode());
    }

    public function testCanCreatePatchRequest()
    {
        $mock = new MockHandler([
            new Response(201)
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $requestData = [
            "id" => 1,
            "value" => 'test'
        ];

        $response = $restService
            ->setEndpoint($this->endpoint)
            ->patch('/posts/1', $requestData, [], false);

        self::assertEquals(201, $response->getStatusCode());
    }

    public function testCanCreateDeleteRequest()
    {
        $mock = new MockHandler([
            new Response(204)
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $response = $restService
            ->setEndpoint($this->endpoint)
            ->delete('/posts/1', [], [], false);

        self::assertEquals(204, $response->getStatusCode());
    }

    public function testCanCreatePurgeRequest()
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'])
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $response = $restService
            ->setEndpoint($this->endpoint)
            ->purge('/posts', [], [], false);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('Bar', $response->getHeader('X-Foo')[0]);
    }

    public function testCanSetRequestHeader()
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'])
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $requestHeaders = [
            'auth-token' => '123'
        ];

        $restService
            ->setEndpoint($this->endpoint)
            ->setRequestHeaders($requestHeaders);

        self::assertEquals([
            'auth-token' => '123',
            'Accept' => 'application/json'
        ], $this->callMethod($restService, 'getRequestHeaders'));
    }

    public function testCanSetIsFireAndForgetRequest()
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'])
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $requestData = [
            "id" => 1,
            "value" => 'test'
        ];

        $response = $restService
            ->setEndpoint($this->endpoint)
            ->setIsFireAndForget(true, 200)
            ->put('/posts/1', $requestData, [], false);

        self::assertTrue($response);
    }

    public function testCanCreateHeadRequest()
    {
        $mock = new MockHandler([
            new Response(200, ['X-Foo' => 'Bar'])
        ]);

        $handler = HandlerStack::create($mock);

        $restService = new RestService(['handler' => $handler]);
        $response = $restService
            ->setEndpoint($this->endpoint)
            ->head('/posts', [], [], false);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('Bar', $response->getHeader('X-Foo')[0]);
    }
}