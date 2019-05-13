<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Custompoint extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('reward_model');
        $this->load->model('point_model');
        $this->load->model('player_model');
        $this->load->model('client_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    /**
     * @SWG\Get(
     *     tags={"Point"},
     *     path="/Point/custom/list",
     *     description="Return a list of custom points to be approved",
     *     @SWG\Parameter(
     *         name="player_list",
     *         in="query",
     *         type="string",
     *         description="List of players (e.g. John, Max, Robert)",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="query",
     *         type="string",
     *         description="Status of custom point",
     *         required=true,
     *         enum={"all", "approve", "pending", "reject"},
     *         default="all"
     *     ),
     *     @SWG\Parameter(
     *         name="from",
     *         in="query",
     *         type="string",
     *         description="From date (e.g. YYYY-MM-DD format)",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="to",
     *         in="query",
     *         type="string",
     *         description="To date (e.g. YYYY-MM-DD format)",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="offset",
     *         in="query",
     *         type="integer",
     *         description="Specify paging offset | default = 0",
     *         required=false,
     *         default=0,
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         type="integer",
     *         description="Specify paging limit | default = 20",
     *         required=false,
     *         default=20
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function list_get()
    {
        $data = $this->input->get();
        $data['client_id'] = $this->validToken['client_id'];
        $data['site_id'] = $this->validToken['site_id'];
        if (isset($data['status']) && $data['status']){
            $data['status'] = strtolower($data['status']);
            if ($data['status'] == 'all') unset($data['status']);
        }
        if (isset($data['to']) && strtotime($data['to'])){
            $data['to'] = new MongoDate(strtotime($data['to']));
        }
        if (isset($data['from']) && strtotime($data['from'])){
            $data['from'] = new MongoDate(strtotime($data['from']));
        }
        if (isset($data['player_list']) && !empty($data['player_list'])){
            $data['player_list'] = array_map('trim', explode(",",$data['player_list']));
        }
        $pending_list = $this->reward_model->listPendingRewards($data);
        foreach ($pending_list as &$item)
        {
            $item['reward_name'] = $this->reward_model->getRewardName($data,$item['reward_id']);
            $item['transaction_id'] = $item['_id']->{'$id'};
            unset($item['reward_id']);
            unset($item['_id']);
        }
        array_walk_recursive($pending_list, array($this, "convert_mongo_object"));
        $this->response($this->resp->setRespond($pending_list), 200);
    }

    /**
     * @SWG\Get(
     *     tags={"Point"},
     *     path="/Point/custom/transaction",
     *     description="Return transaction of custompoint",
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         in="query",
     *         type="string",
     *         description="Transaction ID of custompoint",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function transaction_get()
    {
        $required = $this->input->checkParam(array(
            'transaction_id',
        ));
        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }

        $data = array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id']
        );
        $transaction_id = $this->input->get('transaction_id');
        $response = null;
        try{
            $data['transaction_id'] = new MongoId($transaction_id);
            $response = $this->reward_model->getPendingRewardsById($data);
            if($response){
                $response['reward_name'] = $this->reward_model->getRewardName($data,$response['reward_id']);
                $response['transaction_id'] = $response['_id']->{'$id'};
                unset($response['reward_id']);
                unset($response['_id']);
                array_walk_recursive($response, array($this, "convert_mongo_object"));
            }
        } catch (Exception $e){
            $this->response($this->resp->setRespond($transaction_id), 200);
        }

        $this->response($this->resp->setRespond($response), 200);
    }

    /**
     * @SWG\Post(
     *     tags={"Point"},
     *     path="/Point/custom/approval",
     *     description="To approve custom point in transaction list",
     *     @SWG\Parameter(
     *         name="token",
     *         in="query",
     *         type="string",
     *         description="Access token returned by Playbasis Authentication",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="transaction_id",
     *         in="query",
     *         type="string",
     *         description="Transaction ID of custompoint",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="approve",
     *         in="query",
     *         type="boolean",
     *         description="Transaction ID of custompoint",
     *         required=true,
     *         enum={"true", "false"}
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function approval_post()
    {
        $required = $this->input->checkParam(array(
            'transaction_list',
            'approve'
        ));
        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }

        $data = array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id']
        );
        $approve = $this->input->post('approve') === "true" ? true : false;
        $transaction_list = array_map('trim', explode(",",$this->input->post('transaction_list')));
        $response = array();
        if (is_array($transaction_list)) foreach ($transaction_list as $transaction_id){
            try{
                $transaction_data = array();
                $data['transaction_id'] = new MongoId($transaction_id);
                $status = $this->reward_model->approvePendingReward($data,$approve, $transaction_data);
                //add point expire date if set
                if ($status && (isset($transaction_data['date_expire'])) && ($transaction_data['date_expire'])) {
                    $this->client_model->addPointExpireDate($data['client_id'], $data['site_id'], $transaction_data['cl_player_id'], $transaction_data['pb_player_id'],
                        $transaction_data['reward_id'], $transaction_data['reward_name'], $transaction_data['value'], time(), $transaction_data['date_expire']->sec);
                }
                array_push($response, array('transaction_id' => $transaction_id, 'status' => $status ? "success" : "Transaction ID not found"));
            } catch (Exception $e){
                array_push($response, array('transaction_id' => $transaction_id, 'status' => "Transaction ID is invalid"));
            }
        }
        $this->response($this->resp->setRespond($response), 200);
    }

    /**
     * @SWG\Get(
     *     tags={"Point"},
     *     path="/Point/custom/remaining",
     *     description="Return remaining for each points",
     *     @SWG\Parameter(
     *         name="name",
     *         in="query",
     *         type="string",
     *         description="Return remaining for each points",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function remainingPoint_get()
    {
        $data = $this->input->get();
        $data['client_id'] = $this->validToken['client_id'];
        $data['site_id'] = $this->validToken['site_id'];

        if($this->input->post('point_name')){
            $data['name'] = $this->input->post('point_name');
        }

        $remaining_point = $this->reward_model->remainingPoint($data);
        foreach ($remaining_point as &$point){
            if(!isset($point['quantity'])){
                $point['quantity'] = null;
            }
        }
        $this->response($this->resp->setRespond($remaining_point), 200);
    }

    /**
     * @SWG\Get(
     *     tags={"Point"},
     *     path="/Point/custom/log",
     *     description="Return First/Last reward custom log",
     *     @SWG\Parameter(
     *         name="player_id",
     *         in="query",
     *         type="string",
     *         description="Player ID as used in client's website",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="reward_name",
     *         in="query",
     *         type="string",
     *         description="Name of reward to be queried",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="key",
     *         in="query",
     *         type="string",
     *         description="Key name of custom parameter",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         type="string",
     *         description="Key name of custom parameter",
     *         required=true,
     *         enum={"asc", "desc"},
     *         default="asc"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function customLog_get()
    {
        $required = $this->input->checkParam(array(
            'player_id',
            'reward_name',
            'key'
        ));

        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }
        $data = $this->input->get();
        $data['client_id'] = $this->validToken['client_id'];
        $data['site_id'] = $this->validToken['site_id'];
        $data['sort'] = isset($data['sort']) && strtolower($data['sort']) == "desc" ? "desc" : "asc";
        $data['pb_player_id'] = $this->player_model->getPlaybasisId(array_merge($this->validToken, array('cl_player_id' => $data['player_id'])));
        if (!$data['pb_player_id']) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $data['reward_id'] = $this->point_model->findPoint($data);
        if (!$data['reward_id']) {
            $this->response($this->error->setError('REWARD_NOT_FOUND'), 200);
        }
        $custom_value = $this->reward_model->customLog($data);
        if($custom_value){
            $custom_value['log_id'] = $custom_value['_id']->{'$id'};
            unset($custom_value['_id']);
        }

        $this->response($this->resp->setRespond($custom_value), 200);
    }

    /**
     * @SWG\Post(
     *     tags={"Point"},
     *     path="/Point/custom/clearLog",
     *     description="To clear reward custom log",
     *     @SWG\Parameter(
     *         name="token",
     *         in="query",
     *         type="string",
     *         description="Access token returned by Playbasis Authentication",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="log_id",
     *         in="query",
     *         type="string",
     *         description="ID of reward custom log",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function clearCustomLog_post()
    {
        $required = $this->input->checkParam(array(
            'log_id',
        ));

        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }
        $data = array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id'],
            'log_id' => new MongoId($this->input->post('log_id')),
            'status' => false
        );

        $response = $this->reward_model->setCustomLog($data);
        $this->response($this->resp->setRespond(), 200);
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