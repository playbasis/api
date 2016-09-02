<?php

class AuthControllerTest extends PHPUnit_Framework_TestCase
{
    private $api_key;
    private $api_secret;
    private $base_url;

    public function setUp()
    {
        new GlobalSetup();
    }

    public function testGetApiInfo()
    {
        $rest = new RestClient();
        $result = $rest->post($this->base_url .'Auth', array(
            'api_key' => $_ENV['API_KEY'],
            'api_secret' => $_ENV['API_SECRET'],
        ));

        $this->assertTrue($result->success);
    }
}