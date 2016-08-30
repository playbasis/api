<?php

class AuthControllerTest extends PHPUnit_Framework_TestCase
{
    private $api_key;
    private $api_secret;
    private $base_url;

    public function setUp()
    {
        $CI =& get_instance();
        $CI->load->model('auth_model');
        $CI->load->library('restclient');

        $this->api_key = 'abc';
        $this->api_secret = 'abcde';
        $this->base_url = $CI->config->base_url();
    }

    public function testGetApiInfo()
    {
        $rest = new RestClient();
        $result = $rest->post($this->base_url .'Auth', array(
            'api_key' => $this->api_key,
            'api_secret' => $this->api_secret,
        ));
        print_r($result);

        $this->assertTrue($result->success);
    }
}