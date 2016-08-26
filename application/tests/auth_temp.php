<?php

//require('vendor/autoload.php');

class AuthTest extends PHPUnit_Framework_TestCase
{
    protected $client;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://mybookstore.com'
        ]);
    }

    public function testGet_ValidInput_BookObject()
    {
        $response = $this->client->get('/books', [
            'query' => [
                'bookId' => 'hitchhikers-guide-to-the-galaxy'
            ]
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody(), true);

        $this->assertArrayHasKey('bookId', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertEquals(42, $data['price']);
    }
}