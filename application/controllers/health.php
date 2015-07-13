<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';
require_once APPPATH . '/libraries/ApnsPHP/Autoload.php';
//require_once APPPATH . '/libraries/GCM/loader.php';
class Health extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('global_player_model');
        $this->load->model('health_model');
        $this->load->model('push_model');
        $this->load->model('auth_model');
        $this->load->model('client_model');
        $this->load->model('player_model');
        $this->load->model('tracker_model');
        $this->load->model('point_model');
        $this->load->model('action_model');
        $this->load->model('level_model');
        $this->load->model('reward_model');
        $this->load->model('quest_model');
        $this->load->model('badge_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/utility', 'utility');
        $this->load->model('tool/respond', 'resp');
        $this->load->model('tool/node_stream', 'node');
    }

    public function HealthLog_post()
    {
        $HealthLog = array(
            'type' => $this->input->post('type'),
            'quantity' => $this->input->post('quantity'),
            'unit' => $this->input->post('unit'),
            'datetime_start' => $this->input->post('datetime_start'),
            'datetime_end' => $this->input->post('datetime_end'),
            'description' => $this->input->post('description')

        );
        $this->health_model->storeHealthLog($HealthLog, null);
        $this->response($this->resp->setRespond(''), 200);

    }
    public function getHealthLog_post()
    {
        $HealthLog = array(
            'type' => $this->input->post('type'),
            'player_id' => $this->input->post('player_id')

        );
        $this->health_model->getHealthLog($HealthLog, null);
        $this->response($this->resp->setRespond(''), 200);

    }
    public function HealthInfo_post()
    {
        $HealthInfo = array(
            'player_id' => $this->input->post('player_id'),
            'height' => $this->input->post('height'),
            'birth_date' => $this->input->post('birth_date'),
            'medical_condition' => $this->input->post('medical_condition'),
            'medical_note' => $this->input->post('medical_note'),
            'allergies_reaction' => $this->input->post('allergies_reaction'),
            'blood_type' => $this->input->post('blood_type'),
            'sex' => $this->input->post('sex')

        );

        $this->health_model->storeHealthInfo($HealthInfo, null);
        $this->response($this->resp->setRespond(''), 200);

    }
    public function getHealthInfo_post()
    {
        $player_id = $this->input->post('player_id');

        $this->health_model->getHealthInfo($player_id, null);
        $this->response($this->resp->setRespond(''), 200);

    }
}