<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Timestamp extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('timestamp_model');
        $this->load->model('player_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function index_post()
    {
        $client_id = $this->validToken['client_id'];
        $site_id = $this->validToken['site_id'];

        $pb_player_id = null;
        if($this->input->post('player_id')) {
            $pb_player_id = $this->player_model->getPlaybasisId(array(
                'client_id' => $this->client_id,
                'site_id' => $this->site_id,
                'cl_player_id' => $this->input->post('player_id'),
            ));
            if (!$pb_player_id) {
                $this->response($this->error->setError('USER_NOT_EXIST'), 200);
            }
        }

        $time_stamp = new MongoDate();
        $other_data = $this->input->post();
        $private_datas = array('player_id', 'token', 'XDEBUG_SESSION_START', 'XDEBUG_TRACE');
        foreach($private_datas as $private_data) {
            if (isset($other_data[$private_data])) {
                unset($other_data[$private_data]);
            }
        }

        $this->timestamp_model->insertTimestamp($client_id, $site_id, $pb_player_id, $time_stamp, $other_data);

        $this->response($this->resp->setRespond(), 200);
    }

    public function index_get()
    {
        $client_id = $this->validToken['client_id'];
        $site_id = $this->validToken['site_id'];

        $pb_player_id = null;
        if($this->input->get('player_id')) {
            $pb_player_id = $this->player_model->getPlaybasisId(array(
                'client_id' => $this->client_id,
                'site_id' => $this->site_id,
                'cl_player_id' => $this->input->get('player_id'),
            ));
            if (!$pb_player_id) {
                $this->response($this->error->setError('USER_NOT_EXIST'), 200);
            }
        }

        $sort_order = $this->input->get('sort_order') ? $this->input->get('sort_order') : "desc";
        $query_data = $this->input->get();
        $private_datas = array('player_id', 'token', 'XDEBUG_SESSION_START', 'XDEBUG_TRACE', 'api_key', 'iodocs', 'sort_order');
        foreach($private_datas as $private_data) {
            if (isset($query_data[$private_data])) {
                unset($query_data[$private_data]);
            }
        }

        $result = $this->timestamp_model->retriveTimestamp($client_id, $site_id, $pb_player_id, $query_data, $sort_order);
        array_walk_recursive($result, array($this, "convert_mongo_object_and_optional"));

        $this->response($this->resp->setRespond($result), 200);
    }

    /**
     * Use with array_walk and array_walk_recursive.
     * Recursive iterable items to modify array's value
     * from MongoId to string and MongoDate to readable date
     * @param mixed $item this is reference
     * @param string $key
     */
    private function convert_mongo_object_and_optional(&$item, $key)
    {
        if (is_object($item)) {
            if (get_class($item) === 'MongoId') {
                $item = $item->{'$id'};
            } else {
                if (get_class($item) === 'MongoDate') {
                    $item = datetimeMongotoReadable($item);
                }
            }
        }
    }

}