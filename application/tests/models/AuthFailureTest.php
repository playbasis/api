<?php

class AuthFailureTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $CI =& get_instance();
        $CI->load->model('auth_model');
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