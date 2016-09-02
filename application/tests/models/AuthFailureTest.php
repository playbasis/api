<?php

class AuthFailureTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        new GlobalSetup();
    }

    public function testGetApiInfo()
    {
        $auth = new Auth_model();
        $res = $auth->getApiInfo(array(
            'key' => 'xxxFAILUURExxx',
            'secret' => 'xxxxx'
        ));
        $this->assertCount(0, $res);

    }
}