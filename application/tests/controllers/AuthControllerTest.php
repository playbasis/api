<?php
require_once(__DIR__.'/../CITest.php');

class AuthControllerTest extends CITestCase
{
    protected $CI;

    public function setUp()
    {
        $this->CI =& get_instance();
    }

    public function testGetApiInfo()
    {
        $rest = new RestClient();
        $response = $rest->post('Auth', array(
            'api_key' => $_ENV['API_KEY'],
            'api_secret' => $_ENV['API_SECRET'],
        ));
        $this->assertTrue($response->success);
        return $response;
    }

    /**
     * @depends testGetApiInfo
     */
    public function testGetApiInfoCon($response)
    {
        $this->CI = new Auth();
        $test = $this->getClientSite();
        $test2 = $this->getToken();
        var_dump($test2);

        //$test = $this->CI->index_post();
    }
}