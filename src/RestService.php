<?php

namespace RestService;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
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
     * RestService constructor.
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
    protected function getRequestHeaders(): array
    {
        $headers = $this->requestHeaders;

        // set accept header --- default application/json
        if ($this->accept) {
            $headers['Accept'] = $this->accept;
        }

        return $headers;
    }

    /**
     * @param array $requestHeaders
     * @return $this
     */
    public function setRequestHeaders(array $requestHeaders): RestService
    {
        $this->requestHeaders = $requestHeaders;
        return $this;
    }

    /**
     * @param string $endpoint
     * @return $this
     */
    public function setEndpoint(string $endpoint): RestService
    {
        $this->apiEndpoint = $endpoint;
        return $this;
    }

    /**
     * @param bool $isFireAndForget
     * @param int $connectionTimeout
     * @return $this
     */
    public function setIsFireAndForget(bool $isFireAndForget = false, int $connectionTimeout = 5): RestService
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
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $returnResponseBodyOnly
     * @return PsrResponse|mixed|ResponseInterface|string
     * @throws Exception
     */
    public function delete(string $uri, array $params = [], array $headers = [], bool $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('DELETE', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $returnResponseBodyOnly
     * @return PsrResponse|mixed|ResponseInterface|string
     * @throws Exception
     */
    public function get(string $uri, array $params = [], array $headers = [], bool $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('GET', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $returnResponseBodyOnly
     * @return PsrResponse|mixed|ResponseInterface|string
     * @throws Exception
     */
    public function head(string $uri, array $params = [], array $headers = [], bool $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('HEAD', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $returnResponseBodyOnly
     * @return bool|PsrResponse|mixed|ResponseInterface|string
     * @throws SocketException
     */
    public function patch(string $uri, $params = [], array $headers = [], bool $returnResponseBodyOnly = true)
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
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $returnResponseBodyOnly
     * @return bool|PsrResponse|mixed|ResponseInterface|string
     * @throws SocketException
     */
    public function post(string $uri, $params = [], array $headers = [], bool $returnResponseBodyOnly = true)
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
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $returnResponseBodyOnly
     * @return bool|PsrResponse|mixed|ResponseInterface|string
     * @throws SocketException
     */
    public function put(string $uri, $params = [], array $headers = [], bool $returnResponseBodyOnly = true)
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
     * @param string $uri
     * @param array $params
     * @param array $headers
     * @param bool $returnResponseBodyOnly
     * @return PsrResponse|mixed|ResponseInterface|string
     * @throws Exception
     */
    public function purge(string $uri, array $params = [], array $headers = [], bool $returnResponseBodyOnly = true)
    {
        $uri = $this->apiEndpoint . $uri;
        $options = [
            'headers' => ((empty($headers)) ? $this->getRequestHeaders() : $headers),
            'query' => $params
        ];

        return $this->send('PURGE', $uri, $options, $returnResponseBodyOnly);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param bool $returnResponseBodyOnly
     * @return PsrResponse|mixed|string
     * @throws GuzzleException
     */
    protected function send(string $method, string $uri, array $options = [], bool $returnResponseBodyOnly = true)
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
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return bool
     * @throws SocketException
     */
    protected function fire(string $method, string $uri, array $options = []): bool
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

    public function resetRequest(): void
    {
        $this->accept = 'application/json';
        $this->apiEndpoint = null;
        $this->connectionTimeout = 5;
        $this->isFireAndForget = false;
        $this->requestHeaders = [];
    }
}