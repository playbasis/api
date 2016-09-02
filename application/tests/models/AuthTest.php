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
        $return = $auth->getApiInfo(array(
            'key' => $_ENV['API_KEY'],
            'secret' => $_ENV['API_SECRET'],
        ));

        $this->assertCount(4, $return);
        $this->assertArrayHasKey('client_id', $return);
        $this->assertArrayHasKey('site_id', $return);
        $this->assertArrayHasKey('platform_id', $return);
        $this->assertArrayHasKey('site_name', $return);
    }
}