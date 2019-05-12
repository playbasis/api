<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Tools extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->model('content_model');
    }

    private function isValidAccessCode($code)
    {
        return $code == "123" ? true : false;
    }

    // php index.php tools setup "123"
    public function setup($accessCode)
    {
        if (!$this->isValidAccessCode($accessCode)) {
            echo "Access denied";
            return; 
        }

        echo "Hello {$accessCode}!".PHP_EOL;
    }
}