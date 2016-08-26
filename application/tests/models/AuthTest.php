<?php

class AuthTest extends PHPUnit_Framework_TestCase
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
            'key' => 'abc',
            'secret' => 'abcde'
        ));
        //print_r($res);

        $this->assertCount(4, $res);
        $this->assertArrayHasKey('client_id', $res);
        $this->assertArrayHasKey('site_id', $res);
        $this->assertArrayHasKey('platform_id', $res);
        $this->assertArrayHasKey('site_name', $res);
    }
}
?>