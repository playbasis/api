<?php
class GlobalSetup extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $CI =& get_instance();
        $CI->load->model('auth_model');

        $CI->load->library('restclient');
    }
}