<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Instagram extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('social_model');
        $this->load->model('tool/respond', 'resp');
    }

    public function feed_get()
    {
        $hub_mode = $this->input->get('hub_mode');
        if (is_scalar($hub_mode) && (string)$hub_mode === 'subscribe') {
            $challenge = $this->input->get('hub_challenge');
            if (!is_scalar($challenge)) {
                $this->output->set_status_header('400');
                return;
            }
            echo (string)$challenge;
        } else {
            echo 'playbasis <3 instagram';
        }
    }

    public function feed_post()
    {
        $jsonArray = json_decode(file_get_contents('php://input'), true);
        if (!is_array($jsonArray)) {
            $this->response($this->resp->setRespond(), 200);
            return;
        }

        $this->social_model->saveInstagramFeedData($jsonArray);
    }
}
