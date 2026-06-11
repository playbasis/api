<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Client extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('client_model');
        $this->load->model('player_model');
        $this->load->model('point_model');
        $this->load->model('badge_model');
        $this->load->model('tool/error_model', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function test_get()
    {
        show_404('Client/test');
    }
}

?>
