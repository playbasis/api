<?php
require_once(__DIR__.'/../CITest.php');

class AuthFailureTest extends CITestCase
{

    public function setUp()
    {

    }

    public function testGetApiInfo()
    {
        $auth = new Auth_model();
        $return = $auth->getApiInfo(array(
            'key' => 'xxxFAILUURExxx',
            'secret' => 'xxxxx'
        ));
        $this->assertCount(0, $return);

    }
}