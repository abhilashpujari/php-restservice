<?php

namespace RestService;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Uri;
use RestService\Exceptions\SocketException;

/**
 * Class RestService
 * @package RestService
 */
class RestService 
{
    /**
     * @var string
     */
    protected $accept;
    
    /**
     * @var string
     */
    protected $apiEndpoint;

    /**
     * @var int
     */
    protected $connectionTimeout;
    
    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var bool
     */
    protected $isFireAndForget = false;

    /**
     * @var array
     */
    protected $options;
    
    /**
     * @var array
     */
    protected $requestHeaders = [];

    public function __construct()
    {
        $this->resetRequest();
    }

    /**
     * @return array
     */
    protected function getRequestHeaders()
    {
        $headers = $this->requestHeaders;

        // set accept header --- default application/json
        if ($this->accept) {
            $headers['Accept'] = $this->accept;
        }

        return $headers;
    }

    /**
     * @param $requestHeaders
     * @return RestService
     */
    public function setRequestHeaders($requestHeaders)
    {
        $this->requestHeaders = $requestHeaders;
        return $this;
    }

    /**
     * @param $endpoint
     * @return RestService
     */
    public function setEndpoint($endpoint)
    {
        $this->apiEndpoint = $endpoint;
        return $this;
    }

    /**
     * @param $isFireAndForget
     * @param int $connectionTimeout
     * @return RestService
     */
     public function setIsFireAndForget($isFireAndForget, $connectionTimeout = 5)
     {
         $this->isFireAndForget = (bool)$isFireAndForget;
         $this->connectionTimeout = $connectionTimeout;
         return $this;
     }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function head($uri, array $params = [], array $headers = [])
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('HEAD', $uri, $options);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function get($uri, array $params = [], array $headers = [])
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('GET', $uri, $options);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws Exception
     * @throws SocketException
     */
    public function post($uri, $params = [], array $headers = [])
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'json' => $params
        ];

        if ($this->isFireAndForget) {
            return $this->fire('POST', $uri, $options);
        } else {
            return $this->send('POST', $uri, $options);
        }
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws Exception
     * @throws SocketException
     */
    public function put($uri, $params = [], array $headers = [])
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'json' => $params
        ];

        if ($this->isFireAndForget) {
            return $this->fire('PUT', $uri, $options);
        } else {
            return $this->send('PUT', $uri, $options);
        }
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    public function delete($uri, array $params = [], array $headers = [])
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('DELETE', $uri, $options);
    }

    /**
     * @param $uri
     * @param $method
     * @param array $options
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws Exception
     */
    protected function send($uri, $method, $options = [])
    {
        if (is_null($this->apiEndpoint)) {
            throw new Exception("Invalid null endpoint");
        }

        try {
            $response = $this->guzzle->request($method, $uri, $options);
            $this->resetRequest();
            return $response;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @return bool
     * @throws SocketException
     */
    protected function fire($method, $uri, array $options = [])
    {
        $uri = new Uri($uri);

        /** @var $socketRequest PsrRequest */
        $socketRequest = new PsrRequest($method, $uri, $options['headers'], json_encode($options['json']));
        $socketRequest = $socketRequest
            ->withAddedHeader('Content-Type', 'application/json; charset=utf-8')
            ->withAddedHeader('Content-Length', strlen($socketRequest->getBody()))
            ->withAddedHeader('Connection', 'Close');

        $host = ($uri->getScheme() === 'https')
            ? 'ssl://' . $uri->getHost()
            : $uri->getHost();

        if ($uri->getPort()) {
            $port = $uri->getPort();
        } else {
            $port = ($uri->getScheme() === 'https') ? 443 : 80;
        }

        $socket = @fsockopen($host, $port, $errno, $errstr, $this->connectionTimeout);
        if (!$socket) {
            throw new SocketException($errstr, $errno);
        }

        fwrite($socket, \GuzzleHttp\Psr7\str($socketRequest));
        fclose($socket);
        return true;
    }

    /**
     *  reset request
     * @return void
     */
    public function resetRequest()
    {
        $this->accept = 'application/json';
        $this->guzzle = new Client();
        $this->apiEndpoint = null;
        $this->connectionTimeout = 5;
        $this->isFireAndForget = false;
        $this->requestHeaders = [];
    }
}