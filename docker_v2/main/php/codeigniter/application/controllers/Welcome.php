<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct()
  {
    parent::__construct();
    $this->load->model('user_model');
  }

	public function index()
	{
    $this->user_model->insertSample();
    $data['docs'] = $this->user_model->getAll();
		$this->load->view('welcome_message', $data);
	}
}
