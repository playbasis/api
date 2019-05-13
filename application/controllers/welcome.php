<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Welcome extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('weblogger');
    }

    public function index()
    {
        $this->weblogger->log('Welcome index()');
        $this->load->view('welcome_message');
    }

    public function playbasis()
    {
        $this->weblogger->log('Welcome playbasis()');
        $this->load->view('playbasis/apiinfo');
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */