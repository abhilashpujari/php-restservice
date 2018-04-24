<?php
namespace RestService\Tests;

use PHPUnit\Framework\TestCase;
use RestService\RestService;

class RestServiceTest extends TestCase
{
    /**
     * @var RestService
     */
    protected $restService;

    /**
     * @var $endpoint
     */
    protected $endpoint;

    protected function setUp()
    {
        $this->restService = new RestService();
        $this->endpoint = 'https://jsonplaceholder.typicode.com';
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid null endpoint
     */
    public function testNullEndpointThrowsException()
    {
        $this->restService->get('/posts');
    }

    public function testCanCreateGetRequest()
    {
        $response = $this->restService
            ->setEndpoint($this->endpoint)
            ->get('/posts', [], [], false);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanCreatePostRequest()
    {
        $response = $this->restService
            ->setEndpoint($this->endpoint)
            ->post('/posts', [], [], false);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testCanCreatePutRequest()
    {
        $requestData = [
            "id" => 1,
            "value" => 'test'
        ];

        $response = $this->restService
            ->setEndpoint($this->endpoint)
            ->put('/posts/1', $requestData, [], false);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanCreatePatchRequest()
    {
        $requestData = [
            "id" => 1,
            "value" => 'test'
        ];

        $response = $this->restService
            ->setEndpoint($this->endpoint)
            ->patch('/posts/1', $requestData, [], false);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanCreateDeleteRequest()
    {
        $response = $this->restService
            ->setEndpoint($this->endpoint)
            ->delete('/posts/1', [], [], false);

        $this->assertEquals(200, $response->getStatusCode());
    }
}