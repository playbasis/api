<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';
define('ACTION_GIVETOKEN', 'givetoken');
define('ACTION_TRANSFER', 'transfer');
define('IS_GLOBAL', true);
define('DOLLAR_REWARD', 'dollar');
define('TOKEN_REWARD', 'token');

class Custom extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('action_model');
        $this->load->model('campaign_model');
        $this->load->model('player_model');
        $this->load->model('reward_model');
        $this->load->model('tool/utility', 'utility');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }
 
    public function giveToken_post()
    {
        $required = $this->input->checkParam(array(
            'player_id',
            'receiver_id',
            'amount',
            'lang'
        ));
        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }
 
        $player_id = $this->input->post('player_id');
        $receiver_id = $this->input->post('receiver_id');
        $amount = $this->input->post('amount');
        $token = $this->input->post('token');
 
        $action_count = 0;
        $action_count2 = 0;
 
        $action_id = $this->action_model->findAction(array('client_id' => $this->client_id, 'site_id' => $this->site_id, 'action_name' => ACTION_TRANSFER));
 
        //check campaign
        $campaign = $this->campaign_model->getActiveCampaign($this->client_id, $this->site_id);
        if(!$campaign){
            //return our of campaign
            $this->response($this->error->setError('OUT_OF_CAMPAIGN'), 200);
        }
 
        //check A -> B
        $pb_player_id = $this->player_model->getPlaybasisId(array('client_id' => $this->client_id, 'site_id' => $this->site_id, 'cl_player_id' => $player_id));
        if($pb_player_id){
            if(IS_GLOBAL){
                $action_count = $this->player_model->getActionCount($pb_player_id, $action_id, $this->site_id, array('to') , array($receiver_id));
            } else {
                $action_count = $this->player_model->getActionCount($pb_player_id, $action_id, $this->site_id, array('to', 'campaign_name') , array($receiver_id, $campaign['name']));
            }
            $action_count = $action_count['count'];
        }
 
        //check B -> A
        $pb_player_id2 = $this->player_model->getPlaybasisId(array('client_id' => $this->client_id, 'site_id' => $this->site_id, 'cl_player_id' => $receiver_id));
        if($pb_player_id2){
            if(IS_GLOBAL){
                $action_count2 = $this->player_model->getActionCount($pb_player_id2, $action_id, $this->site_id, array('to') , array($player_id));
            } else {
                $action_count2 = $this->player_model->getActionCount($pb_player_id2, $action_id, $this->site_id, array('to', 'campaign_name') , array($player_id, $campaign['name']));
            }
            $action_count2 = $action_count2['count'];
        }
 
        if($action_count+$action_count2 > 0){
            //return already play/transfer
            $this->response($this->error->setError('PLAYER_HAS_TRANSFERRED'), 200);
        }
 
        //check token
        $remaining_token = $this->reward_model->remainingPoint(array('client_id' => $this->client_id, 'site_id' => $this->site_id, 'name' => TOKEN_REWARD));
        if(isset($remaining_token[0]['name']) && $remaining_token[0]['name'] == TOKEN_REWARD){
            if($remaining_token[0]['quantity'] <= 0){
                //return token not enough
                $this->response($this->error->setError('TOKEN_NOT_ENOUGH'), 200);
            }
        } else {
            // return token not exist
            $this->response($this->error->setError('TOKEN_NOT_EXIST'), 200);
                    }
 
        //check dollar
        $remaining_dollar = $this->reward_model->remainingPoint(array('client_id' => $this->client_id, 'site_id' => $this->site_id, 'name' => DOLLAR_REWARD));
        if(isset($remaining_dollar[0]['name']) && $remaining_dollar[0]['name'] == DOLLAR_REWARD){
            if($remaining_dollar[0]['quantity'] <= 0){
                //return dollar not enough
                $this->response($this->error->setError('DOLLAR_NOT_ENOUGH'), 200);
            }
        } else {
            // return dollar not exist
            $this->response($this->error->setError('DOLLAR_NOT_EXIST'), 200);
        }
        //give token
        $response = $this->curl_request('Engine/rule', array(
            'token' => $token,
            'player_id' => $player_id,
            'receiver_id' => $receiver_id,
            'action' => ACTION_GIVETOKEN));
        $response = json_decode($response);

        if(!isset($response->response->events[0]->reward_type) || $response->response->events[0]->reward_type != TOKEN_REWARD){
            $this->response($this->error->setError('DEFAULT_ERROR'), 200);
        }

        //log transfer
        $this->curl_request('Engine/rule', array(
            'token' => $token,
            'player_id' => $player_id,
            'to' => $receiver_id,
            'amount' => $amount,
            'campaign_name' => $campaign['name'],
            'action' => ACTION_TRANSFER));

        //has pending?
        $pending_list = $this->reward_model->listPendingRewards(array('client_id' => $this->client_id, 'site_id' => $this->site_id, 'player_list' => array($player_id), 'status' => 'pending'));
        if($pending_list && is_array($pending_list)){
            foreach ($pending_list as &$item)
            {
                $item['reward_name'] = $this->reward_model->getRewardName(array('client_id' => $this->client_id, 'site_id' => $this->site_id),$item['reward_id']);
                $item['transaction_id'] = $item['_id']->{'$id'};
                unset($item['reward_id']);
                unset($item['_id']);
            }
            array_walk_recursive($pending_list, array($this, "convert_mongo_object"));
            $this->response($this->resp->setRespond($pending_list), 200);
        } else {
            $this->response($this->resp->setRespond(), 200);
        }
        $this->response($this->error->setError('DEFAULT_ERROR'), 200);
    }
 
    private function curl_request($url, $postData, $method="post"){
        $posts = http_build_query($postData);
        $base_url = $this->config->base_url();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $base_url.$url);
        if($method == 'post'){
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$posts);
        } else if($method == 'get'){
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        }
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        $response = curl_exec($ch);
        $response = $response ? $response: curl_error($ch);
        curl_close ($ch);
        return $response;
    }
 
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
}
