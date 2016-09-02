<?php

class AuthTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        new GlobalSetup();
    }

    public function testGetApiInfo()
    {
        $auth = new Auth_model();
        $res = $auth->getApiInfo(array(
            'key' => $_ENV['API_KEY'],
            'secret' => $_ENV['API_SECRET'],
        ));

        $this->assertCount(4, $res);
        $this->assertArrayHasKey('client_id', $res);
        $this->assertArrayHasKey('site_id', $res);
        $this->assertArrayHasKey('platform_id', $res);
        $this->assertArrayHasKey('site_name', $res);
    }
}