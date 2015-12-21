<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Store_org extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('store_org_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function playerRegister_post($node_id, $player_id)
    {
        $this->benchmark->mark('start');

        if (empty($node_id) || empty($player_id)) {
            $this->response($this->error->setError('PARAMETER_MISSING', array('node_id')), 200);
        }

        if (!MongoId::isValid($node_id)) {
            $this->response($this->error->setError('PARAMETER_INVALID', array('node_id')), 200);
        }

        if (!$this->validClPlayerId($player_id)) {
            $this->response($this->error->setError('USER_ID_INVALID'), 200);
        }

        $node_id = new MongoId($node_id);
        $node = $this->store_org_model->retrieveNodeById($this->site_id, $node_id);
        if ($node === null) {
            $this->response($this->error->setError('STORE_ORG_NODE_NOT_FOUND'), 200);
        }

        $this->load->model('player_model');

        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $existed_player_organize = $this->store_org_model->retrievePlayerToNode($this->client_id, $this->site_id,
            $pb_player_id, $node_id);
        if (!$existed_player_organize) {
            $player_organize_id = $this->store_org_model->createPlayerToNode($this->client_id, $this->site_id,
                $pb_player_id, $node_id);
        }else{
            $this->response($this->error->setError('STORE_ORG_PLAYER_ALREADY_EXISTS_WITH_NODE'), 200);
        }

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('processing_time' => $t)), 200);
    }

    public function playerRemove_post($node_id, $player_id)
    {
        $this->benchmark->mark('start');

        if (empty($node_id) || empty($player_id)) {
            $this->response($this->error->setError('PARAMETER_MISSING', array('node_id')), 200);
        }

        if (!MongoId::isValid($node_id)) {
            $this->response($this->error->setError('PARAMETER_INVALID', array('node_id')), 200);
        }

        if (!$this->validClPlayerId($player_id)) {
            $this->response($this->error->setError('USER_ID_INVALID'), 200);
        }

        $node_id = new MongoId($node_id);
        $node = $this->store_org_model->retrieveNodeById($this->site_id, $node_id);
        if ($node === null) {
            $this->response($this->error->setError('STORE_ORG_NODE_NOT_FOUND'), 200);
        }

        $this->load->model('player_model');

        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $existed_player_organize = $this->store_org_model->retrievePlayerToNode($this->client_id, $this->site_id,
            $pb_player_id, $node_id);
        if ($existed_player_organize) {
            $is_deleted = $this->store_org_model->deletePlayerToNode($this->client_id, $this->site_id,
                $pb_player_id, $node_id);
        }else{
            $this->response($this->error->setError('STORE_ORG_PLAYER_NOT_EXISTS_WITH_NODE'), 200);
        }

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('processing_time' => $t)), 200);
    }

    /**
     * Use with array_walk and array_walk_recursive.
     * Recursive iterable items to modify array's value
     * from MongoId to string and MongoDate to readable date
     * @param mixed $item this is reference
     * @param string $key
     */
    private function convert_mongo_object(&$item, $key)
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

    private function validClPlayerId($cl_player_id)
    {
        return (!preg_match("/^([-a-z0-9_-])+$/i", $cl_player_id)) ? false : true;
    }
}