PHP HTTP REST Client
=======================
Simple PHP Client library that makes it easy to make REST API calls.
It uses [Guzzle Client](http://docs.guzzlephp.org/en/stable/) as a dependencies

## Installation

The recommended way to install this library is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of this library:

```bash
php composer.phar require abhilashpujari/php-restservice
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update this library using composer:

 ```bash
composer.phar update
 ```

 ## Usage

1 GET Request
 ```php
 use RestService\RestService;

 $restService = new RestService();
 $response = $restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->get('/posts/1');
 ```

2 POST Request
```php
$restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->post('/posts');
```

3 PUT Request
```php
$restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->put('/posts/1',
         [
             'id' => 1,
             'text' => 'Test'
         ]
     );
```

4 PATCH Request
```php
$restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->patch('/posts/1',
         [
             'id' => 1,
             'text' => 'Test'
         ]
     );
```

5 DELETE Request
```php
$restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->delete('/posts/1');
```

6 A fire and forget request which is useful in scenario where we fire the request and aren't
concerned of the response, it can be done by setting setIsFireAndForget(true)
```php
$restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->setIsFireAndForget(true)
     ->post('/posts');
```

7 Request with some custom headers, , it can be done by setting setRequestHeaders(headers array)
```php
$restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->setRequestHeaders([
         'auth' => 'somevalue'
     ])
     ->post('/posts');
```

8 Request in which we request the response data which includes status code, headers, body etc,
which can be done by setting request method 4th parameter to false
```php
$response = $restService
     ->setEndpoint('https://jsonplaceholder.typicode.com')
     ->get('/posts/1', [], [], false);

 var_dump($response->getHeaders());
 var_dump($response->getBody());
```