<?php
class GlobalSetup extends PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $CI =& get_instance();
        $CI->load->model('auth_model');
        $CI->load->model('badge_model');

        $CI->load->library('restclient');
    }

    public function getClientSite($clientSite = null)
    {
        $auth = new Auth_model();
        $res = $auth->getApiInfo(array(
            'key' => $_ENV['API_KEY'],
            'secret' => $_ENV['API_SECRET'],
        ));

        if ($clientSite === 'client_id'){
            return $res['client_id'];
        }elseif ($clientSite === 'site_id'){
            return $res['site_id'];
        }else{
            return $res;
        }
    }
}