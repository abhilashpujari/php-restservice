<?php

namespace RestService;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use RestService\Exceptions\SocketException;
use function GuzzleHttp\Psr7\str;

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

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->guzzle = new Client($options);
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
     * @param bool $isFireAndForget
     * @param int $connectionTimeout
     * @return $this
     */
    public function setIsFireAndForget($isFireAndForget = false, $connectionTimeout = 5)
    {
        $this->isFireAndForget = (bool)$isFireAndForget;
        $this->connectionTimeout = $connectionTimeout;
        return $this;
    }

    /**
     * @param PsrResponse $response
     * @return mixed|string
     */
    public function getResponseBody(PsrResponse $response)
    {
        if (!empty($response->getHeader('Content-Type')) && stristr($response->getHeader('Content-Type')[0],
                'application/json')
        ) {
            return json_decode(
                $response->getBody()
            );
        } else {
            return $response->getBody()->__toString();
        }
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @param bool|true $returnResponseBodyOnly
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    public function delete($uri, array $params = [], array $headers = [], $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('DELETE', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @param bool|true $returnResponseBodyOnly
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    public function get($uri, array $params = [], array $headers = [], $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('GET', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @param bool|true $returnResponseBodyOnly
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    public function head($uri, array $params = [], array $headers = [], $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('HEAD', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @param bool|true $returnResponseBodyOnly
     * @return bool|mixed|ResponseInterface
     * @throws Exception
     * @throws SocketException
     */
    public function patch($uri, $params = [], array $headers = [], $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'json' => $params
        ];

        if ($this->isFireAndForget) {
            return $this->fire('PATCH', $uri, $options);
        } else {
            return $this->send('PATCH', $uri, $options, $returnResponseBodyOnly);
        }
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @param bool|true $returnResponseBodyOnly
     * @return bool|mixed|ResponseInterface
     * @throws Exception
     * @throws SocketException
     */
    public function post($uri, $params = [], array $headers = [], $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'json' => $params
        ];

        if ($this->isFireAndForget) {
            return $this->fire('POST', $uri, $options);
        } else {
            return $this->send('POST', $uri, $options, $returnResponseBodyOnly);
        }
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @param bool|true $returnResponseBodyOnly
     * @return bool|mixed|ResponseInterface
     * @throws Exception
     * @throws SocketException
     */
    public function put($uri, $params = [], array $headers = [], $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'json' => $params
        ];

        if ($this->isFireAndForget) {
            return $this->fire('PUT', $uri, $options);
        } else {
            return $this->send('PUT', $uri, $options, $returnResponseBodyOnly);
        }
    }

    /**
     * @param $uri
     * @param array $params
     * @param array $headers
     * @param bool|true $returnResponseBodyOnly
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    public function purge($uri, array $params = [], array $headers = [], $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('PURGE', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param $method
     * @param $uri
     * @param array $options
     * @param bool|true $returnResponseBodyOnly
     * @return mixed|ResponseInterface
     * @throws Exception
     */
    protected function send($method, $uri, $options = [], $returnResponseBodyOnly = true)
    {
        if (is_null($this->apiEndpoint)) {
            throw new Exception("Invalid null endpoint");
        }

        try {
            /** @var Response $response */
            $response = $this->guzzle->request($method, $uri, $options);
            $this->resetRequest();
            return ($returnResponseBodyOnly) ? $this->getResponseBody($response) : $response;
        } catch (ClientException $e) {
            throw new Exception($e->getResponse()->getReasonPhrase(), $e->getResponse()->getStatusCode());
        } catch (BadResponseException $e) {
            throw new Exception($e->getResponse()->getReasonPhrase(), $e->getResponse()->getStatusCode());
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

        /** @var MessageInterface $socketRequest */
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

        fwrite($socket, str($socketRequest));
        fclose($socket);

        $this->resetRequest();
        return true;
    }

    /**
     *  reset request
     * @return void
     */
    public function resetRequest()
    {
        $this->accept = 'application/json';
        $this->apiEndpoint = null;
        $this->connectionTimeout = 5;
        $this->isFireAndForget = false;
        $this->requestHeaders = [];
    }
}