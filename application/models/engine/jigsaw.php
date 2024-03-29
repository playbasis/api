<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class jigsaw extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('playbasis');
        $this->load->library('mongo_db');
    }

    public function action($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        $data_set = $this->getActionDatasetInfo($config['action_name']);
        $required = array();
        if (is_array($data_set)) {
            foreach ($data_set as $param) {
                $isRequired = isset($param['required']) ? $param['required'] : false;
                $param_name = $param['param_name'];
                if (!isset($input[$param_name]) && ($isRequired)) {
                    array_push($required, $param_name);
                }
            }
        }
        if (!empty($required)) {
            $requiredParam = implode(", ", $required);
            try {
                throw new Exception($requiredParam);
            } catch (Exception $e) {
                throw new Exception('PARAMETER_MISSING', 0, $e);
            }
        }

        return true;
    }

    public function getActionDatasetInfo($action_name)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where(array(
            'name' => $action_name
        ));
        $results = $this->mongo_db->get("playbasis_action");
        return $results ? $results[0]['init_dataset'] : null;
    }

    public function customParameter($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['param_name']));
        assert(isset($config['param_value']));
        if (!isset($config['param_operation'])) $config['param_operation'] = '='; // default is the equal operator

        $result = false;
        $param_name = $config['param_name'];

        if (isset($input[$param_name])) {
            if ($config['param_operation'] == '=') {
                $result = ($input[$param_name] == $config['param_value']);
            } elseif ($config['param_operation'] == '!=') {
                $result = ($input[$param_name] != $config['param_value']);
            } elseif ($config['param_operation'] == '>') {
                $result = ($input[$param_name] > $config['param_value']);
            } elseif ($config['param_operation'] == '<') {
                $result = ($input[$param_name] < $config['param_value']);
            } elseif ($config['param_operation'] == '>=') {
                $result = ($input[$param_name] >= $config['param_value']);
            } elseif ($config['param_operation'] == '<=') {
                $result = ($input[$param_name] <= $config['param_value']);
            }
        } else {
            $result = false;
        }
        return $result;
    }

    private function getCustomParameterFile($client_id, $site_id, $file_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('custom_param_condition_data'));
        $this->mongo_db->where(array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            '_id' => new MongoId($file_id)
        ));
        $this->mongo_db->limit(1);
        $sequence_list = $this->mongo_db->get('playbasis_custom_param_condition_to_client');
        if (isset($sequence_list[0]['custom_param_condition_data']) && $sequence_list[0]['custom_param_condition_data']) {
            return $sequence_list[0]['custom_param_condition_data'];
        } else {
            return false;
        }
    }

    public function customParameterFile($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['param_name']));
        assert(isset($config['param_operation']));
        assert(isset($config['file_id']));

        $result = false;
        $custom_param_list = $this->getCustomParameterFile($input['client_id'],$input['site_id'],$config['file_id']);
        if($custom_param_list &&  (isset($input[$config['param_name']]))){
            if (((in_array($input[$config['param_name']], $custom_param_list)) && $config['param_operation'] == "in" )||
                (!(in_array($input[$config['param_name']], $custom_param_list)) && $config['param_operation'] == "notIn" ) ){
                $result = true;
            }

        }
        return $result;
    }

    public function level($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['type']));
        assert(isset($config['value']));

        $result = false;

        if (isset($input['level'])) {
            if ($config['type'] == '=') {
                $result = ($input['level'] == $config['value']);
            } elseif ($config['type'] == '!=') {
                $result = ($input['level'] != $config['value']);
            } elseif ($config['type'] == '>') {
                $result = ($input['level'] > $config['value']);
            } elseif ($config['type'] == '<') {
                $result = ($input['level'] < $config['value']);
            } elseif ($config['type'] == '>=') {
                $result = ($input['level'] >= $config['value']);
            } elseif ($config['type'] == '<=') {
                $result = ($input['level'] <= $config['value']);
            }
        }

        return $result;
    }

    public function userprofile($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['profile']));
        assert(isset($config['operation']));
        $now = isset($input['rule_time']) ? $input['rule_time'] : new MongoDate();
        if(!(isset($config['value']) && $config['value']) && $config['profile'] != "tag"){
            return false;
        }

        // calculate age of user
        if($config['profile'] == 'age'){
            if(isset($input['user_profile']['birth_date']) && $input['user_profile']['birth_date']){

                $now = new Datetime(datetimeMongotoReadable($now));
                $birth_date = new Datetime($input['user_profile']['birth_date']);
                $interval = $now->diff($birth_date);
                if($interval->invert == 0){
                    // $now <= birth date
                    return false;
                }
                $input['user_profile']['age'] = $interval->y;
            }else{
                return false;
            }
        }

        $result = false;

        if($config['profile'] == 'gender' ){
            if( isset($input['user_profile']['gender']) && $config['operation'] == "=") {
                if ($input['user_profile']['gender'] == 1) {
                    $male = array("male", "man", "gentleman");
                    if (in_array($config['value'], $male)) {
                        $result = true;
                    }
                } else if ($input['user_profile']['gender'] == 0) {
                    $female = array("female", "woman", "lady");
                    if (in_array($config['value'], $female)) {
                        $result = true;
                    }
                }
            }
        } elseif($config['profile'] == 'tag' ){
            if( $config['operation'] == "=" ) {
                if( ($config['value'] != "" && isset($input['user_profile']['tags']) && is_array($input['user_profile']['tags']) && in_array($config['value'], $input['user_profile']['tags'])) ||
                    ($config['value'] == "" && (!isset($input['user_profile']['tags']) || $input['user_profile']['tags'] == null || empty($input['user_profile']['tags'])))
                ) {
                    $result = true;
                }
            }elseif( $config['operation'] == "!=" ){
                if( ($config['value'] != "" && (isset($input['user_profile']['tags']) && is_array($input['user_profile']['tags']) && !in_array($config['value'], $input['user_profile']['tags']) ||
                            (!isset($input['user_profile']['tags']) || $input['user_profile']['tags'] == null || empty($input['user_profile']['tags'])))) ||
                    ($config['value'] == "" && !(!isset($input['user_profile']['tags']) || $input['user_profile']['tags'] == null || empty($input['user_profile']['tags'])))
                ) {
                    $result = true;
                }
            }
        } else {
            if (isset($input['user_profile'][$config['profile']])) {
                if ($config['operation'] == '=') {
                    $result = ($input['user_profile'][$config['profile']] == $config['value']);
                } elseif ($config['operation'] == '!=') {
                    $result = ($input['user_profile'][$config['profile']] !=  $config['value']);
                } elseif ($config['operation'] == '>') {
                    $result = ($input['user_profile'][$config['profile']] >  $config['value']);
                } elseif ($config['operation'] == '<') {
                    $result = ($input['user_profile'][$config['profile']] <  $config['value']);
                } elseif ($config['operation'] == '>=') {
                    $result = ($input['user_profile'][$config['profile']] >= $config['value']);
                } elseif ($config['operation'] == '<=') {
                    $result = ($input['user_profile'][$config['profile']] <= $config['value']);
                }
            }
        }

        return $result;
    }

    public function gameLevel($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['game_level']));

        return isset($input['game_current_stage']) && ($config['game_level'] == $input['game_current_stage']);
    }



    public function point($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['reward_id']));
        assert(isset($config['operator']));
        assert(isset($config['value']));
        $result = false;

        /* get current reward value */
        $reward_to_player = $this->player_model->getPlayerPoint($input['client_id'], $input['site_id'], $input['pb_player_id'], new MongoId($config['reward_id']));
        $point_amount = (isset($reward_to_player[0]['value']) && $reward_to_player[0]['value']) ? $reward_to_player[0]['value'] : 0;

        if(isset($config['value'])){
            if ($config['operator'] == '=') {
                $result = ($point_amount == $config['value']);
            } elseif ($config['operator'] == '!=') {
                $result = ($point_amount != $config['value']);
            } elseif ($config['operator'] == '>') {
                $result = ($point_amount > $config['value']);
            } elseif ($config['operator'] == '<') {
                $result = ($point_amount < $config['value']);
            } elseif ($config['operator'] == '>=') {
                $result = ($point_amount >= $config['value']);
            } elseif ($config['operator'] == '<=') {
                $result = ($point_amount <= $config['value']);
            }
        }

        return $result;
    }

    public function pointInDay($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['reward_id']));
        assert(isset($config['amount']));
        assert(isset($config['time_of_day']));
        $result = false;

        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $currentYMD = date("Y-m-d");
        $settingTime = (isset($config['time_of_day']) && $config['time_of_day']) ? $config['time_of_day'] : "00:00";
        $settingTime = strtotime("$currentYMD $settingTime:00");
        $currentTime = strtotime($currentYMD." " . date('H:i:s', $timeNow) );

        if ($settingTime <= $currentTime){ // action has been processed for today !
            $startTimeFilter = $settingTime;
        }else{
            $startTimeFilter =  strtotime( "-1 day" , $settingTime ) ;
        }

        $total = $this->countPlayerPointAwardInDay($input['client_id'], $input['site_id'], $input['pb_player_id'], $config['reward_id'], $startTimeFilter);
        $reject = $this->countPlayerRejectedPointInDay($input['client_id'], $input['site_id'], $input['pb_player_id'], $config['reward_id'], $startTimeFilter);

        if(($total - $reject) < $config['amount']){
            $result = true;
        }

        return $result;
    }

    private function countPlayerPointAwardInDay($client_id, $site_id, $pb_player_id, $reward_id, $startTime){

        $results = $this->mongo_db->aggregate('playbasis_custom_point_log', array(
            array(
                '$match' => array(
                    'client_id' => $client_id,
                    'site_id' => $site_id,
                    'pb_player_id' => $pb_player_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($startTime)),
                ),
            ),

            array(
                '$group' => array(
                    '_id' => null,
                    'sum' => array('$sum' => '$quantity')
                )
            )
        ));

        $total = $results['result'] ? $results['result'][0]['sum'] : 0;

        return $total;
    }

    private function countPlayerRejectedPointInDay($client_id, $site_id, $pb_player_id, $reward_id, $startTime){

        $results = $this->mongo_db->aggregate('playbasis_reward_status_to_player', array(
            array(
                '$match' => array(
                    'client_id' => $client_id,
                    'site_id' => $site_id,
                    'pb_player_id' => $pb_player_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($startTime)),
                    'status' => "reject"
                ),
            ),

            array(
                '$group' => array(
                    '_id' => null,
                    'sum' => array('$sum' => '$value')
                )
            ),
        ));

        $total = $results['result'] ? $results['result'][0]['sum'] : 0;

        return $total;
    }

    public function badgeCondition($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['badge_id']));
        assert(isset($config['value']));
        $result = false;
        $amount = 0;
        foreach ($input['player_badge'] as $key => $badge) {
            if (($badge['badge_id'] == $config['badge_id']) ) {
                $amount = $badge['amount'];
                break;
            }
        }

        if(isset($config['param_operator'])){
            if ($config['param_operator'] == '=') {
                $result = ($amount == $config['value']);
            } elseif ($config['param_operator'] == '!=') {
                $result = ($amount != $config['value']);
            } elseif ($config['param_operator'] == '>') {
                $result = ($amount > $config['value']);
            } elseif ($config['param_operator'] == '<') {
                $result = ($amount < $config['value']);
            } elseif ($config['param_operator'] == '>=') {
                $result = ($amount >= $config['value']);
            } elseif ($config['param_operator'] == '<=') {
                $result = ($amount <= $config['value']);
            }
        }else{
            $result = $amount >= $config['value'];
        }
        return $result;
    }

    private function getSequenceFile($client_id, $site_id, $sequence_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('sequence_list'));
        $this->mongo_db->where(array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            '_id' => new MongoId($sequence_id)
        ));
        $this->mongo_db->limit(1);
        $sequence_list = $this->mongo_db->get('playbasis_sequence_to_client');
        if (isset($sequence_list[0]['sequence_list']) && $sequence_list[0]['sequence_list']) {
            return $sequence_list[0]['sequence_list'];
        } else {
            return false;
        }
    }

    public function reward(&$config, $input, &$exInfo = array(), $cache = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($config["reward_id"] == null ||isset($config['reward_id']));
        assert($config["reward_name"] == null ||isset($config['reward_name']));
        assert($config["item_id"] == null || isset($config["item_id"]));
        assert(isset($config['quantity']) || isset($config['sequence_id']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);

        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        
        if(isset($config['sequence_id']) && isset($input['jigsaw_category']) && ($input['jigsaw_category'] == "REWARD_SEQUENCE") ){
            $sequence_list = $this->getSequenceFile($input['client_id'],$input['site_id'],$config['sequence_id']);
            if($sequence_list){

                $global = (isset($config["global"]) && $config["global"] === "true") ? true : false;
                $loop = (isset($config["loop"]) && $config["loop"] === "true") ? true : false;

                $index = $this->getSequenceIndex($input, array('input'),count($sequence_list) - 1,$global,$loop);

                if($index === false){
                    return false;
                }else{
                    $config['quantity'] = (int)$sequence_list[$index];
                    $exInfo['quantity'] = $config['quantity'];

                }
            }else{
                return false;
            }
        }

        if (is_null($config['item_id']) || $config['item_id'] == '') {
            //check if expired
            if((isset($config['point_expire_date'])) && ($config['point_expire_date']) && ($timeNow > strtotime($config['point_expire_date'])) ){
                return false;
            } 
            //check if reward exist
            $reward_info = $this->getRewardInfo($input['client_id'], $input['site_id'], $config['reward_id']);
            $result =  $this->checkReward($reward_info);
            if($result == true){
                //reward per user limit
                $result = $this->checkRewardLimitPerUser($input['pb_player_id'], $reward_info, $input['client_id'], $input['site_id'], $config['quantity']);
                if($result){
                    //reward available
                    $result = $this->isRewardAvailable($reward_info);
                    if($result){
                        //reward per day limit
                        $result = $this->checkRewardLimitPerDay($input['pb_player_id'], $reward_info, $input['client_id'], $input['site_id'], $config['quantity'], $timeNow);
                        if(!$result){
                            $exInfo['error'] = "ENGINE_RULE_REWARD_OUT_OF_STOCK";
                        }
                    } else {
                        $exInfo['error'] = "ENGINE_RULE_REWARD_OUT_OF_STOCK";
                    }
                } else {
                    $exInfo['error'] = "ENGINE_RULE_REWARD_EXCEED_LIMIT";
                }
            }
            return $result;
        }

        //if reward type is badge
        switch ($config['reward_name']) {
            case 'badge':
                return $this->checkBadge($config['item_id'], $input['pb_player_id'], $input['site_id'], $config['quantity'], $exInfo);
            case 'goods':
                $ret = $this->checkGoodsWithCache($cache, $config['item_id'], $input['pb_player_id'], $input['client_id'], $input['site_id'], $config['quantity'], $exInfo);
                return $ret;
            default:
                return false;
        }
    }

    public function customPointReward($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        $name = $config['reward_name'];
        $quantity = $config['quantity'];
        if (!$name && isset($input['reward']) && $input['reward']) {
            $name = $input['reward'];
        }
        if (!$quantity && isset($input['quantity']) && $input['quantity']) {
            $quantity = $input['quantity'];
        }
        $exInfo['dynamic']['reward_name'] = $name;
        $exInfo['dynamic']['quantity'] = $quantity;
        if (!$name || !$quantity) return false;
        $reward_info = $this->getRewardByName($input['client_id'], $input['site_id'], $name);
        $result =  $this->checkReward($reward_info);
        if($result == true){
            $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
            $result = $this->checkRewardLimitPerUser($input['pb_player_id'], $reward_info, $input['client_id'], $input['site_id'], $quantity);
            if($result == true){
                $result = $this->isRewardAvailable($reward_info);
                if($result == true) {
                    $result = $this->checkRewardLimitPerDay($input['pb_player_id'], $reward_info, $input['client_id'], $input['site_id'], $quantity, $timeNow);
                    if($result != true){
                        $exInfo['error'] = "ENGINE_RULE_REWARD_OUT_OF_STOCK";
                    }
                } else {
                    $exInfo['error'] = "ENGINE_RULE_REWARD_OUT_OF_STOCK";
                }
            } else {
                $exInfo['error'] = "ENGINE_RULE_REWARD_EXCEED_LIMIT";
            }
        }
        return $result;
    }

    private function getPlayerPointByName($client_id, $site_id, $pb_player_id, $reward_name)
    {
        $reward_id = $this->reward_model->findByName(array(
            'client_id' => $client_id,
            'site_id' => $site_id
        ), $reward_name);

        $reward_to_player = $this->player_model->getPlayerPoint($client_id, $site_id, $pb_player_id, $reward_id);

        $value = (isset($reward_to_player[0]['value']) && $reward_to_player[0]['value']) ? $reward_to_player[0]['value'] : 0;

        return $value;
    }

    private function getGoodsInfo($client_id, $site_id, $goods_id){
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'goods_id' => $goods_id
        ));
        $ret = $this->mongo_db->get('playbasis_goods_to_client');
        return  $ret && isset($ret[0]) ? $ret[0] : array();;
    }

    private function getPlayerAllGoods($client_id, $site_id, $pb_player_id){
        $this->mongo_db->select(array('goods_id','value'));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id
        ));
        $goods = $this->mongo_db->get('playbasis_goods_to_player');
        return $goods;
    }

    private function getPlayerGoodsQuantityByName($client_id, $site_id, $pb_player_id, $goods_name)
    {
        $value = 0;
        $found_goods = false;
        $goods_list = $this->getPlayerAllGoods($client_id, $site_id, $pb_player_id);
        foreach ($goods_list as $goods) {
            $goods_info = $this->getGoodsInfo($client_id, $site_id, $goods['goods_id']);
            if(isset($goods_info['name']) && ($goods_info['name'] == $goods_name) && !isset($goods_info['group'])){
                $value += $goods['value'];
                $found_goods = true;
            }
        }
        return $found_goods ? $value : null;
    }

    private function getPlayerGoodsGroupQuantityByName($client_id, $site_id, $pb_player_id, $goodsgroup_name)
    {
        $value = null;
        $found_goods = false;
        $goods_list = $this->getPlayerAllGoods($client_id, $site_id, $pb_player_id);
        foreach ($goods_list as $goods) {
            $goods_info = $this->getGoodsInfo($client_id, $site_id, $goods['goods_id']);
            if(isset($goods_info['group']) && $goods_info['group'] == $goodsgroup_name ){
                $value += $goods['value'];
                $found_goods = true;
            }
        }
        return $found_goods ? $value : null;
    }

    public function specialRewardCondition($config, $input, &$exInfo = array())
    {
        if(!isset($input['condition-rewardtype']) || !isset($input['condition-rewardname']) || !isset($config['param_operator']) || !isset($input['condition-quantity'])){
            return false;
        }

        $point = 0;
        if(strtolower($input['condition-rewardtype']) == "badge"){
            $badge_id = $this->badge_model->getBadgeIDByName($input['client_id'], $input['site_id'], $input['condition-rewardname']);
            if (!$badge_id) {
                return false;
            }
            foreach ($input['player_badge'] as $key => $badge) {
                if (($badge['name'] == $input['condition-rewardname']) ) {
                    $point = $badge['amount'];
                    break;
                }
            }
        }else if(strtolower($input['condition-rewardtype']) == "goods"){
            $goods_id = $this->goods_model->getGoodsIDByName($input['client_id'], $input['site_id'], $input['condition-rewardname']);
            if (!$goods_id) {
                return false;
            }
            $player_point = $this->getPlayerGoodsQuantityByName($input['client_id'], $input['site_id'],$input['pb_player_id'],$input['condition-rewardname']);
            $point = is_null($player_point) ? 0 : $player_point;
        }else if(strtolower($input['condition-rewardtype']) == "goods_group"){
            $goods_id = $this->goods_model->getGoodsIDByName($input['client_id'], $input['site_id'], null,$input['condition-rewardname']);
            if (!$goods_id) {
                return false;
            }
            $player_point = $this->getPlayerGoodsGroupQuantityByName($input['client_id'], $input['site_id'],$input['pb_player_id'],$input['condition-rewardname']);
            $point = is_null($player_point) ? 0 : $player_point;
        }else if(strtolower($input['condition-rewardtype']) == "point"){
            if($input['condition-rewardname'] == "exp"){
                $point = $input['user_profile']['exp'];
            }else{
                $point = $this->getPlayerPointByName( $input['client_id'], $input['site_id'],$input['pb_player_id'],$input['condition-rewardname']);
            }
        }

        if ($config['param_operator'] == '=') {
            $result = ($point == $input['condition-quantity']);
        } elseif ($config['param_operator'] == '!=') {
            $result = ($point != $input['condition-quantity']);
        } elseif ($config['param_operator'] == '>') {
            $result = ($point > $input['condition-quantity']);
        } elseif ($config['param_operator'] == '<') {
            $result = ($point < $input['condition-quantity']);
        } elseif ($config['param_operator'] == '>=') {
            $result = ($point >= $input['condition-quantity']);
        } elseif ($config['param_operator'] == '<=') {
            $result = ($point <= $input['condition-quantity']);
        } else {
            $result = false;
        }
        return $result;
    }

    public function specialReward($config, $input, &$exInfo = array())
    {
        return $this->customPointReward($config, $input, $exInfo);
    }

    public function counter($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['counter_value']));
        assert(isset($config['interval']));
        assert(isset($config['interval_unit']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $result = $this->getMostRecentJigsaw($input, array(
            'input',
            'date_added'
        ));
        if (!$result) {
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1;
            $exInfo['remaining_time'] = (int)$config['interval'];
            return false;
        }
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $log = $result['input'];
        if ($config['interval'] == 0) //if config time = 0 reduce counter and return false
        {
            $log['remaining_counter'] -= 1;
            if ((int)$log['remaining_counter'] == 0) {
                $exInfo['remaining_counter'] = (int)$config['counter_value'];
                $exInfo['remaining_time'] = -1; //reset timer, timer won't go down until counter triggers again
                return true;
            }
            $exInfo['remaining_counter'] = $log['remaining_counter'];
            $exInfo['remaining_time'] = $config['interval'];
            return false;
        }
        if ($config['interval'] != 0 && $log['remaining_time'] == 0) {
            $exInfo['remaining_counter'] = $log['remaining_counter'] - 1;
            $exInfo['remaining_time'] = (int)$config['interval'];
            return false;
        }
        $lastTime = $result['date_added'];
        $timeDiff = ($log['interval_unit']) == 'second' ? (int)($timeNow - $lastTime->sec) : (int)(date_diff(new DateTime("@$timeNow"),
            new DateTime(datetimeMongotoReadable($lastTime)))->d);
        $resetUnit = ($log['interval_unit'] != $config['interval_unit']);
        $remainingTime = $log['remaining_time'];
        $reset = ($remainingTime >= 0) && ($timeDiff > $remainingTime);
        if ($resetUnit || $reset) //if reset, start counter timer and decrease counter by 1
        {
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1;
            $exInfo['remaining_time'] = (int)$config['interval'];
            return false;
        }
        $log['remaining_counter'] -= 1;
        if ((int)$log['remaining_counter'] == 0) {
            $exInfo['remaining_counter'] = (int)$config['counter_value'];
            $exInfo['remaining_time'] = -1; //reset timer, timer won't go down until counter triggers again
            return true;
        } else {
            $exInfo['remaining_counter'] = $log['remaining_counter'];
            if (($remainingTime < 0) || $config['reset_timeout']) {
                $exInfo['remaining_time'] = (int)$config['interval'];
            } else {
                $exInfo['remaining_time'] = $remainingTime - $timeDiff;
            }
            return false;
        }
    }

    public function counterRange($config, $input, &$exInfo = array())
    {
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $from = (isset($config['param_from']) && $config['param_from'] ) ? (int)$config['param_from'] : 1; // default is 1
        $to = (isset($config['param_to']) && $config['param_to'] ) ? (int)$config['param_to'] : null; // default is infinity
        $jigsaw = $this->getMostRecentJigsaw($input, array('input'));

        $counter = (isset($jigsaw['input']['current_counter'])) ? ((int)$jigsaw['input']['current_counter'])+1 : 1;
        $exInfo['current_counter'] = $counter;

        if (!is_null($to)) {
            $result = ($counter >= $from && $counter <= $to);
            if($counter > $to){
                $exInfo['error'] = "ENGINE_RULE_EXCEED_COUNTERRANGE_LIMIT";
            }
        } else {
            $result = $counter >= $from;
        }
        return $result;
    }

    public function counterWithin($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['counter_value']));
        assert(isset($config['within']));
        assert(isset($config['interval_unit']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $result = $this->getMostRecentJigsaw($input, array(
            'input',
            'date_added'
        ));
        if (!$result) {
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1; // max-1
            $exInfo['beginning_time'] = $timeNow;
            if ($exInfo['remaining_counter'] == 0) {
                $exInfo['remaining_counter'] = (int)$config['counter_value']; // max
                $exInfo['beginning_time'] = -1; // unset
                return true;
            }
            return false;
        }
        $log = $result['input'];
        $within = (int)$config['within'];
        $timeDiff = ($log['interval_unit']) == 'second' ? (int)($timeNow - $within) : (int)($timeNow - strtotime('-' . $within . ' days',
                $timeNow));
        if ($timeDiff > $log['beginning_time']) { // time's up!
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1; // max-1
            $exInfo['beginning_time'] = $timeNow; // reset to "now"
        } else { // valid
            $exInfo['remaining_counter'] = (int)$log['remaining_counter'] - 1; // current-1
            $exInfo['beginning_time'] = $log['beginning_time']; // stays the same;
        }
        if ($exInfo['remaining_counter'] == 0) {
            $exInfo['remaining_counter'] = (int)$config['counter_value']; // max
            $exInfo['beginning_time'] = -1; // unset
            return true;
        }
        return false;
    }

    private function countActionWithSpecificParameter($client_id, $site_id, $action_id, $param_key, $param_value, $pb_player_id=null)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'action_id' => $action_id,
        ));

        if($pb_player_id){
            $this->mongo_db->where('pb_player_id', $pb_player_id);
        }

        $this->mongo_db->where(array('parameters.' . $param_key => $param_value));


        $temp = $this->mongo_db->count('playbasis_validated_action_log');
        return $temp;
    }

    public function counterParameter($config, $input, &$exInfo = array())
    {
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['param_key']));
        assert(isset($config['param_operator']));
        assert(isset($config['param_amount']));
        assert(isset($config['global']));
        $result = false;

        $param_value = isset($config['param_key']) && isset($input[$config['param_key']]) ? $input[$config['param_key']] : null;

        if($param_value) {
            $action_count = $this->countActionWithSpecificParameter($input['client_id'], $input['site_id'],
                $input['action_id'], $config['param_key'], $param_value, (isset($config["global"]) && $config["global"] === "true") ? null : $input['pb_player_id']);
            $action_count = $action_count+1;

            if (isset($config['param_amount'])) {
                if ($config['param_operator'] == '=') {
                    $result = ($action_count == $config['param_amount']);
                } elseif ($config['param_operator'] == '!=') {
                    $result = ($action_count != $config['param_amount']);
                } elseif ($config['param_operator'] == '>') {
                    $result = ($action_count > $config['param_amount']);
                } elseif ($config['param_operator'] == '<') {
                    $result = ($action_count < $config['param_amount']);
                } elseif ($config['param_operator'] == '>=') {
                    $result = ($action_count >= $config['param_amount']);
                } elseif ($config['param_operator'] == '<=') {
                    $result = ($action_count <= $config['param_amount']);
                }
            }
        }

        return $result;
    }

    private function countActionWithSpecificParameterInDay($client_id, $site_id, $action_id, $param_key, $param_value, $pb_player_id, $startTime)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'action_id' => $action_id,
            'pb_player_id'=> $pb_player_id,
            'date_added' => array('$gte' => new MongoDate($startTime)),
        ));

        $this->mongo_db->where(array('parameters.' . $param_key => $param_value));

        $temp = $this->mongo_db->count('playbasis_validated_action_log');
        return $temp;
    }

    public function countParamValueInDay($config, $input, &$exInfo = array())
    {
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['param_key']));
        assert(isset($config['param_amount']));
        $result = false;

        $param_value = isset($config['param_key']) && isset($input[$config['param_key']]) ? $input[$config['param_key']] : null;

        if($param_value && $config['param_amount']) {
            $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
            $currentYMD = date("Y-m-d");
            $settingTime = (isset($config['time_of_day']) && $config['time_of_day']) ? $config['time_of_day'] : "00:00";
            $settingTime = strtotime("$currentYMD $settingTime:00");
            $currentTime = strtotime($currentYMD." " . date('H:i:s', $timeNow) );

            if ($settingTime <= $currentTime){ // action has been processed for today !
                $startTimeFilter = $settingTime;
            }else{
                $startTimeFilter =  strtotime( "-1 day" , $settingTime ) ;
            }
            $action_count = $this->countActionWithSpecificParameterInDay($input['client_id'], $input['site_id'], $input['action_id'],
                                                                         $config['param_key'], $param_value,  $input['pb_player_id'], $startTimeFilter);

            if($action_count < $config['param_amount']){
                $result = true;
            }
        }

        return $result;
    }

    private function findByLink($client_id, $site_id, $link)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'status' => true,
            'deleted' => false,
            'url' => $link,
        ));

        $results = $this->mongo_db->get('playbasis_link_to_client');
        return isset($results[0]['data']) ? $results[0]['data'] : null;
    }

    public function variableFromDeeplink($config, &$input, &$exInfo = array())
    {
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['url_param']));
        assert(isset($config['deeplink_key']));
        assert(isset($config['variable']));
        $result = false;

        $url = isset($config['url_param']) && isset($input[$config['url_param']]) ? $input[$config['url_param']] : null;

        if($url) {
            $data = $this->findByLink($input['client_id'],$input['site_id'],$url);

            if(isset($data[$config['deeplink_key']])){
                $input[$config['variable']] = (string)$data[$config['deeplink_key']];
                $result = true;
            }
        }

        return $result;
    }

    public function variable($config, &$input, &$exInfo = array())
    {
        assert(isset($config['variable_name']));
        assert(isset($config['param_value']));
        $result = false;

        $variable_name = isset($config['variable_name']) && $config['variable_name'] ? $config['variable_name'] : null;
        $variable_value = isset($config['param_value']) && $config['param_value'] ? $config['param_value'] : null;

        if($variable_name && $variable_value) {

            ob_start();
            eval('$result = '.$config['param_value'].';');
            $ret = ob_get_contents();//do nothing for now
            ob_end_clean();

            $input[$variable_name] = $result ? $result."" :$variable_value;
        }

        return true;
    }

    public function data($config, $input, &$exInfo = array())
    {
        assert(isset($config['key']));
        assert(isset($config['param_value']));
        $exInfo['feedback_key'] = $config['key'];
        $exInfo['feedback_value'] =  $config['param_value'];
        return true;
    }

    public function cooldown($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['cooldown']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $result = $this->getMostRecentJigsaw($input, array(
            'input',
            'date_added'
        ));
        if (!$result) {
            $exInfo['remaining_cooldown'] = (int)$config['cooldown'];
            return true;
        }
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $log = $result['input'];
        $lastTime = $result['date_added'];
        $timeDiff = (int)($timeNow - $lastTime->sec);
        if ($timeDiff > $log['remaining_cooldown']) {
            $exInfo['remaining_cooldown'] = (int)$config['cooldown'];
            return true;
        } else {
            $exInfo['remaining_cooldown'] = (int)$log['remaining_cooldown'] - $timeDiff;
            return false;
        }
    }

    private function isInDuration($now,$start_time,$value,$unit){
        $now = new Datetime(datetimeMongotoReadable($now));
        $start_time = new Datetime(datetimeMongotoReadable($start_time));
        $start_time->modify("+".$value." ".$unit);

        $interval = $now->diff($start_time);
        if($interval->invert == 0){
            // $now <= $deadline
            return true;
        }else{
            // $now > $deadline
            return false;
        }
    }

    public function duration($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['duration_value']));
        assert(isset($config['duration_unit']));
        assert(isset($config['limit_action']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $now = isset($input['rule_time']) ? $input['rule_time'] : new MongoDate();

        $result = $this->getMostRecentJigsaw($input, array(
            'input',
        ));

        if (!$result) {
            $exInfo['start_time'] = $now;
            $exInfo['current_count'] = 1;
            if(1 <= $config['limit_action']){
                return true;
            }else{
                return false;
            }
        }else{
            $log = $result['input'];
            $start_time = $log['start_time'];

            if(!$this->isInDuration($now, $start_time, $config['duration_value'], $config['duration_unit'])){
                $current_count = 1;
                $exInfo['start_time'] = $now;

            }else{
                $current_count = $log['current_count']+1;
                $exInfo['start_time'] = $start_time;

            }
            $exInfo['current_count'] = $current_count;
            if($current_count <= $config['limit_action'] ){
                return true;
            }else{
                return false;
            }
        }
    }

    private function calculateDistanceInKilometres($lat1, $lon1, $lat2, $lon2) {
        $radlat1 = pi() * $lat1/180;
        $radlat2 = pi() * $lat2/180;
        $theta = $lon1-$lon2;
        $radtheta = pi() * $theta/180;
        $dist = sin($radlat1) * sin($radlat2) + cos($radlat1) * cos($radlat2) * cos($radtheta);
        $dist = acos($dist);
        $dist = $dist * (180/pi()) * 111.18957696 ;
        return $dist;
    }


    public function locationArea($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['latitude']));
        assert(isset($config['longitude']));
        assert(isset($config['area']));
        $latitude = isset($input['latitude']) ? $input['latitude'] : null;
        $longitude = isset($input['longitude']) ? $input['longitude'] : null;

        if($config['area']==""){
            return true;
        }elseif(is_null($latitude) || is_null($longitude) ){
            return false;
        }else{
            $distance_in_kilo = $this->calculateDistanceInKilometres($config['latitude'],$config['longitude'],$latitude,$longitude);
            if(($distance_in_kilo * 1000) <= (float)$config['area']){
                return true;
            }else{
                return false;
            }
        }
    }

    public function before($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['timestamp']));
        assert($input != false);
        assert(is_array($input));
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        return (strtotime($config['timestamp']) > $timeNow);
    }

    public function after($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['timestamp']));
        assert($input != false);
        assert(is_array($input));
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        return (strtotime($config['timestamp']) < $timeNow);
    }

    public function between($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['start_time']));
        assert(isset($config['end_time']));
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $start = $config['start_time'];
        $end = $config['end_time'];
        $start = strtotime("1970-01-01 $start:00");
        $end = strtotime("1970-01-01 $end:00");
        //check time range that crosses to the next day
        if ($end < $start) {
            $end = strtotime("1970-01-02 $end:00");
        }
        $now = strtotime("1970-01-01 " . date('H:i', $timeNow) . ":00");
        return ($start < $now && $now < $end);
    }

    public function daily($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        $result = $this->getMostRecentJigsaw($input, array(
            'date_added'
        ));
        if (!$result) {
            return true;
        }
        $lastTime = $result['date_added'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $currentYMD = date("Y-m-d");

        $settingTime = (isset($config['time_of_day']) && $config['time_of_day']) ? $config['time_of_day'] : "00:00";
        $settingTime = strtotime("$currentYMD $settingTime:00");
        $currentTime = strtotime($currentYMD." " . date('H:i', $timeNow) . ":00");

        if ($settingTime > $currentTime){
            $settingTime =  strtotime( "-1 day" , $settingTime ) ;
        }

        if ($lastTime->sec > $settingTime){
            return false;
        }else{
            return true;
        }
    }

    public function weekly($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        assert(isset($config['day_of_week']));
        $result = $this->getMostRecentJigsaw($input, array(
            'input'
        ));
        if (!$result) {
            $exInfo['next_trigger'] = strtotime("next " . $config['day_of_week'] . " " . $config['time_of_day']);
            return true;
        }
        $logInput = $result['input'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        if ($timeNow >= $logInput['next_trigger']) {
            $exInfo['next_trigger'] = strtotime("next " . $config['day_of_week'] . " " . $config['time_of_day']);
            return true;
        }
        $exInfo['next_trigger'] = $logInput['next_trigger'];
        return false;
    }

    public function monthly($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        assert(isset($config['day_of_month']));
        $result = $this->getMostRecentJigsaw($input, array(
            'input'
        ));
        if (!$result) {
            $lastDateOfMonth = date('d', strtotime("last day of next month"));
            $exInfo['next_trigger'] = $config['day_of_month'] > $lastDateOfMonth ? strtotime("last day of next month" . $config['time_of_day']) : strtotime("first day of next month " . $config['time_of_day']) + ($config['day_of_month'] - 1) * 3600 * 24;
            return true;
        }
        $logInput = $result['input'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        if ($timeNow >= $logInput['next_trigger']) {
            $lastDateOfMonth = date('d', strtotime("last day of next month"));
            $exInfo['next_trigger'] = $config['day_of_month'] > $lastDateOfMonth ? strtotime("last day of next month" . $config['time_of_day']) : strtotime("first day of next month " . $config['time_of_day']) + ($config['day_of_month'] - 1) * 3600 * 24;
            return true;
        }
        $exInfo['next_trigger'] = $logInput['next_trigger'];
        return false;
    }

    public function everyNDays($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        assert(isset($config['num_of_days']));
        $result = $this->getMostRecentJigsaw($input, array(
            'input'
        ));
        if (!$result) {
            $currentDate = new DateTime();
            $nextTrigger = $currentDate->modify("+" . $config['num_of_days'] . " day");
            assert($nextTrigger);
            $time = explode(':', $config['time_of_day']);
            $nextTrigger->setTime($time[0], $time[1]);
            assert($nextTrigger);
            $exInfo['next_trigger'] = $nextTrigger->getTimestamp();
            return true;
        }
        $logInput = $result['input'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        if ($timeNow >= $logInput['next_trigger']) {
            $nextTrigger = new DateTime();
            $nextTrigger->setTimestamp($logInput['next_trigger']);
            assert($nextTrigger);
            $nextTrigger->modify("+" . $config['num_of_days'] . " day");
            assert($nextTrigger);
            $exInfo['next_trigger'] = $nextTrigger->getTimestamp();
            return true;
        }
        $exInfo['next_trigger'] = $logInput['next_trigger'];
        return false;
    }

    public function objective($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['objective_id']));
        $objective_id = $config['objective_id'];
        assert(is_string($objective_id));
        $this->set_site_mongodb($input['site_id']);
        //check if this objective has been completed
        $this->mongo_db->where(array(
            'objective_id' => new MongoId($objective_id),
            'pb_player_id' => $input['pb_player_id'],
        ));
        $count = $this->mongo_db->count('playbasis_objective_to_player');
        if ($count > 0) {
            return true;
        }
        //objective not yet completed, check prerequisites
        $this->mongo_db->select(array(
            'prerequisites',
            'name'
        ));
        $this->mongo_db->where(array('_id' => new MongoId($objective_id)));
        $result = $this->mongo_db->get('playbasis_objective');
        assert($result);
        $result = $result[0];
        $prereqs = $result['prerequisites'];
        $objName = $result['name'];
        foreach ($prereqs as $value) {
            $this->mongo_db->where(array(
                'objective_id' => $value,
                'pb_player_id' => $input['pb_player_id'],
            ));
            $count = $this->mongo_db->count('playbasis_objective_to_player');
            if (!$count || ($count <= 0)) {
                return false;
            } //prereq objective not complete, can't complete this objective
        }
        $exInfo['objective_complete'] = array(
            'id' => $objective_id,
            'name' => $objName
        );
        return true;
    }

    public function distinct($config, $input, &$exInfo = array())
    {
        $params = array();
        $data_set = $this->getActionDatasetInfo($input['action_name']);
        if (is_array($data_set)) {
            foreach ($data_set as $param) {
                $param_name = $param['param_name'];
                if (isset($input[$param_name])) {
                    $params[$param_name] = $input[$param_name];
                }
            }
        }
        $c = $this->countActionWithParams($input['client_id'], $input['site_id'], $input['pb_player_id'],
            $input['action_id'], $params, isset($input['pb_player_id-2']) && $input['pb_player_id-2'] ? $input['pb_player_id-2']:null );
        if(isset($config['limit']) && $config['limit']){
            $result = (bool)($c < $config['limit']);
        }else{
            $result = (bool)($c == 0);
        }
        return $result;
    }

    private function countActionWithParams($client_id, $site_id, $pb_player_id, $action_id, $parameters, $pb_player_id_2=null)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'action_id' => $action_id,
        ));

        if($pb_player_id_2){
            $this->mongo_db->where('pb_player_id-2', $pb_player_id_2);
        }
        foreach ($parameters as $name => $value) {
            $this->mongo_db->where(array('parameters.' . $name => $value));
        }

        $temp = $this->mongo_db->count('playbasis_validated_action_log');
        return $temp;
    }

    public function deeplink_feedback($config, $input, &$exInfo = array())
    {
        $conf = isset($input['deeplink_config']) ? $input['deeplink_config'] : null;
        if (!$conf) return false;
        if (!isset($conf['type']) || !in_array($conf['type'], array('branch.io'))) return false;
        if (($conf['type'] == 'branch.io') && (!isset($conf['key']) || !$conf['key'])) return false;

        return true;
    }

    public function email($config, $input, &$exInfo = array())
    {
        return $this->feedback('email', $config, $input, $exInfo);
    }

    public function sms($config, $input, &$exInfo = array())
    {
        return $this->feedback('sms', $config, $input, $exInfo);
    }
    public function webhook($config, $input, &$exInfo = array())
    {
        return $this->feedback('webhook', $config, $input, $exInfo);
    }
    public function push($config, $input, &$exInfo = array())
    {
        return $this->feedback('push', $config, $input, $exInfo);
    }

    private function feedback($type, $config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $this->mongo_db->where('status', true);
        $this->mongo_db->where('site_id', $input['site_id']);
        $this->mongo_db->where('link', $type);
        $this->mongo_db->limit(1);
        if ($this->mongo_db->count('playbasis_feature_to_client') > 0) {
            $this->mongo_db->where('_id', new MongoId($config['template_id']));
            $this->mongo_db->where('status', true);
            $this->mongo_db->where('deleted', false);
            return $this->mongo_db->count('playbasis_' . $type . '_to_client') > 0;
        }
        return false;
    }

    public function random($config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $sum = 0;
        $acc = array();
        $cache = array();
        foreach ($config['group_container'] as $i => $conf) {
            // invalid goods will be excluded from randomness
            if (!(array_key_exists('reward_name', $conf) && $conf['reward_name'] == 'goods')
                || $this->checkGoodsWithCache($cache, new MongoId($conf['item_id']), $input['pb_player_id'],
                    $input['client_id'], $input['site_id'], $conf['quantity'], $exInfo)
            ) {
                $sum += intval($conf['weight']);
                $acc[$i] = $sum;
            }
        }
        if (!$acc) {
            return false;
        } // there is no valid entry
        $max = $sum;
        $ran = $max > 1 ? rand(0, $max - 1) : 0;
        foreach ($acc as $i => $value) {
            if ($ran < $value) {
                $exInfo['index'] = $i;
                $exInfo['break'] = false;
                $conf = $config['group_container'][$i];
                if (array_key_exists('reward_name', $conf)) {
                    foreach (array('item_id', 'reward_id') as $field) {
                        if (array_key_exists($field, $conf)) {
                            $conf[$field] = $conf[$field] ? ($conf[$field] != 'goods' ? new MongoId($conf[$field]) : $conf[$field]) : null;
                        }
                    }
                    $ret = $this->reward($conf, $input, $exInfo, $cache);
                    return $ret;
                } else {
                    if (array_key_exists('feedback_name', $conf) && ($conf['feedback_name'] != "data")) {
                        $ret = $this->feedback($conf['feedback_name'], $conf, $input, $exInfo);
                        return $ret;
                    }else{ // feedback response data
                        $exInfo['feedback_key'] = $conf['key'];
                        $exInfo['feedback_value'] =  $conf['param_value'];
                        return true;
                    }
                }
                return false; // should not reach this line
            }
        }
        return false; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    public function sequence($config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $global = (isset($config["global"]) && $config["global"] === "true") ? true : false;
        $loop = (isset($config["loop"]) && $config["loop"] === "true") ? true : false;

        $index = $this->getSequenceIndex($input, array('input'),count($config['group_container']) - 1,$global,$loop);

        if($index === false){
            $exInfo['break'] = true;
            return false;
        }else{
            $exInfo['index'] = $index;
            $exInfo['break'] = true;
        }

        if ($index == count($config['group_container']) - 1) {
            $exInfo['break'] = false;
        } // if this is last item in the sequence jigsaw, we allow the rule to process next jigsaw
        $conf = $config['group_container'][$index];
        if (array_key_exists('reward_name', $conf)) {
            foreach (array('item_id', 'reward_id') as $field) {
                if (array_key_exists($field, $conf)) {
                    $conf[$field] = $conf[$field] ? ($conf[$field] != 'goods' ? new MongoId($conf[$field]) : $conf[$field]) : null;
                }
            }
            return $this->reward($conf, $input, $exInfo);
        } else {
            if (array_key_exists('feedback_name', $conf) && ($conf['feedback_name'] != "data")) {
                return $this->feedback($conf['feedback_name'], $conf, $input, $exInfo);
            }else{ // feedback response data
                $exInfo['feedback_key'] = $conf['key'];
                $exInfo['feedback_value'] =  $conf['param_value'];
                return true;
            }
        }
        return false; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    public function redeem($config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $ok = true; // default is true
        foreach ($config['group_container'] as $conf) {
            $avail = false;
            if (is_null($conf['item_id']) || $conf['item_id'] == '') {
                $avail = $this->checkRedeemPoint($input['client_id'], $input['site_id'], new MongoId($conf['reward_id']),
                    $input['pb_player_id'], intval($conf['quantity']));
            } else {
                switch ($conf['reward_name']) {
                    case 'badge':
                        $avail = $this->checkRedeemBadge($input['client_id'], $input['site_id'], new MongoId($conf['item_id']),
                            $input['pb_player_id'], intval($conf['quantity']));
                        break;
                    case 'goods':
                        /* TODO: support goods */
                        break;
                    default:
                        break;
                }
            }
            if (!$avail) {
                $ok = false;
                break;
            }
        }
        if ($ok) {
            /* TODO: permissionProcess "redeem" */
            foreach ($config['group_container'] as $conf) {
                if (is_null($conf['item_id']) || $conf['item_id'] == '') {
                    if ($conf['reward_name'] == 'exp') {
                        continue;
                    } // "exp" should not be decreasing
                    $this->reward_model->deductPlayerReward($input['client_id'], $input['site_id'], $input['pb_player_id'], new MongoId($conf['reward_id']), (int)$conf['quantity']);
                    $this->client_model->updateRewardExpiration($input['client_id'], $input['site_id'], $input['pb_player_id'], new MongoId($conf['reward_id']), (int)$conf['quantity']);
                } else {
                    switch ($conf['reward_name']) {
                        case 'badge':
                            $this->updateplayerRedeemBadge($input['client_id'], $input['site_id'],
                                new MongoId($conf['item_id']), $input['pb_player_id'], $input['player_id'],
                                -1 * (int)$conf['quantity']);
                            break;
                        case 'goods':
                            /* TODO: support goods */
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return $ok;
    }

    public function getSequenceIndex($input, $fields, $last_index, $global, $loop)
    {
        assert(isset($input['site_id']));

        $this->set_site_mongodb($input['site_id']);
        $this->mongo_db->select($fields);
        $this->mongo_db->where(array(
            'client_id' => $input['client_id'],
            'site_id' => $input['site_id'],
            'rule_id' => $input['rule_id'],
            'jigsaw_id' => $input['jigsaw_id'],
            'jigsaw_index' => $input['jigsaw_index'],
        ));
        $this->mongo_db->where_lte('input.index',$last_index);
        if(!$global){
            $this->mongo_db->where('pb_player_id', $input['pb_player_id']);
        }else{
            $this->mongo_db->where('global', true);
        }

        $this->mongo_db->order_by(array(
            'date_added' => 'desc'
        ));
        $this->mongo_db->limit(1);

        $mongoDate = new MongoDate(time());

        $this->mongo_db->set(array(
                'date_added' => (isset($input['rule_time'])) ? $input['rule_time'] : $mongoDate,
                'date_modified' => $mongoDate)
        );
        $this->mongo_db->inc('input.index',1);
        $result = $this->mongo_db->findAndModify('jigsaw_log');

        $index = isset($result['input']['index']) ? $result['input']['index'] : 0;
        if(!$result){
            $this->mongo_db->select($fields);
            $this->mongo_db->where(array(
                'client_id' => $input['client_id'],
                'site_id' => $input['site_id'],
                'rule_id' => $input['rule_id'],
                'jigsaw_id' => $input['jigsaw_id'],
                'jigsaw_index' => $input['jigsaw_index'],

            ));
            $this->mongo_db->where_gt('input.index',$last_index);
            if(!$global){
                $this->mongo_db->where('pb_player_id', $input['pb_player_id']);
            }else{
                $this->mongo_db->where('global', true);
            }

            $this->mongo_db->order_by(array(
                'date_added' => 'desc'
            ));
            $this->mongo_db->limit(1);

            if($loop) {
                $this->mongo_db->set(array(
                        'input.index' => 1,
                        'date_modified' => $mongoDate)
                );
                $index = 0;
            }else{
                $this->mongo_db->set(array(
                        'date_modified' => $mongoDate)
                );
                $index = false;
            }
            $result = $this->mongo_db->findAndModify('jigsaw_log');

            if(!$result){
                $this->mongo_db->select($fields);
                $this->mongo_db->where(array(
                    'client_id' => $input['client_id'],
                    'site_id' => $input['site_id'],
                    'rule_id' => $input['rule_id'],
                    'jigsaw_id' => $input['jigsaw_id'],
                    'jigsaw_index' => $input['jigsaw_index'],
                ));

                if(!$global){
                    $this->mongo_db->where('pb_player_id', $input['pb_player_id']);
                }else{
                    $this->mongo_db->where('global', true);
                }

                $this->mongo_db->order_by(array(
                    'date_added' => 'desc'
                ));

                $this->mongo_db->limit(1);

                $this->mongo_db->set(array(
                        'input.group_container' => (isset($input['input']['group_container'])) ?$input['input']['group_container'] : array(),
                        'input.group_id' => (isset($input['input']['group_id'])) ?$input['input']['group_id'] : null,
                        'input.global' => (isset($input['input']['global'])) ?$input['input']['global'] : false,
                        'input.loop' => (isset($input['input']['loop'])) ?$input['input']['loop'] : false,
                        'client_id' => $input['client_id'],
                        'site_id' => $input['site_id'],
                        'site_name' => $input['site_name'],
                        'action_log_id' => (isset($input['action_log_id'])) ? $input['action_log_id'] : 0,
                        'action_id' => (isset($input['action_id'])) ? $input['action_id'] : 0,
                        'action_name' => (isset($input['action_name'])) ? $input['action_name'] : '',
                        'rule_id' => (isset($input['rule_id'])) ? $input['rule_id'] : 0,
                        'rule_name' => (isset($input['rule_name'])) ? $input['rule_name'] : '',
                        'jigsaw_id' => (isset($input['jigsaw_id'])) ? $input['jigsaw_id'] : 0,
                        'jigsaw_name' => (isset($input['jigsaw_name'])) ? $input['jigsaw_name'] : '',
                        'jigsaw_category' => (isset($input['jigsaw_category'])) ? $input['jigsaw_category'] : '',
                        'jigsaw_index' => (isset($input['jigsaw_index'])) ? $input['jigsaw_index'] : '',
                        'site_name' => (isset($input['site_name'])) ? $input['site_name'] : '',
                        'date_added' => (isset($input['rule_time'])) ? $input['rule_time'] : $mongoDate,
                        'date_modified' => $mongoDate)
                );
                $this->mongo_db->inc('input.index',1);
                $result = $this->mongo_db->findAndModify('jigsaw_log',array('upsert' => true));

                $index = isset($result['input']['index']) ? $result['input']['index'] : 0;
            }
        }

        return  $index;
    }

    public function getMostRecentJigsaw($input, $fields)
    {
        assert(isset($input['site_id']));
        $this->set_site_mongodb($input['site_id']);
        $this->mongo_db->select($fields);
        $this->mongo_db->where(array(
            'pb_player_id' => $input['pb_player_id'],
            'site_id' => $input['site_id'],
            'rule_id' => $input['rule_id'],
            'jigsaw_id' => $input['jigsaw_id'],
            'jigsaw_index' => $input['jigsaw_index']
        ));

        $this->mongo_db->order_by(array(
            'date_added' => 'desc'
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('jigsaw_log');
        //for backward compatibility, check again without jigsaw_index
        if (!$result) {
            $this->mongo_db->select($fields);
            $this->mongo_db->where(array(
                'pb_player_id' => $input['pb_player_id'],
                'site_id' => $input['site_id'],
                'rule_id' => $input['rule_id'],
                'jigsaw_id' => $input['jigsaw_id'],
            ));

            $this->mongo_db->order_by(array(
                'date_added' => 'desc'
            ));
            $this->mongo_db->limit(1);
            $result = $this->mongo_db->get('jigsaw_log');
        }
        return ($result) ? $result[0] : $result;
    }

    private function checkBadge($badgeId, $pb_player_id, $site_id, $quantity = 0, &$exInfo)
    {
        //get badge properties
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'stackable',
            'substract',
            'quantity',
            'per_user'
        ));
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'badge_id' => $badgeId,
            'status' => true,
            'deleted' => false
        ));
        $this->mongo_db->limit(1);
        $badgeInfo = $this->mongo_db->get('playbasis_badge_to_client');

        //badge not stackable, check if player already have the badge
        $this->mongo_db->select(array(
            'value'
        ));
        $this->mongo_db->where(array(
            'badge_id' => $badgeId,
            'pb_player_id' => $pb_player_id,
        ));
        $this->mongo_db->limit(1);
        $rewardInfo = $this->mongo_db->get('playbasis_reward_to_player');

        if (!$badgeInfo || !$badgeInfo[0]) {
            return false;
        }
        $badgeInfo = $badgeInfo[0];
        $max = (isset($badgeInfo['per_user']) && !is_null($badgeInfo['per_user'])) ? $badgeInfo['per_user']: null;
        if (!$badgeInfo['quantity'] && !is_null($badgeInfo['quantity'])) {
            $exInfo['error'] = "ENGINE_RULE_REWARD_OUT_OF_STOCK";
            return false;
        }
        /* will handle quantity in client model updateplayerBadge()
        if ($badgeInfo['quantity'] < $quantity) {
            return false;
        }
        */
        if ($badgeInfo['stackable']) {
            if($max){
                if(isset($rewardInfo[0])){
                    $rewardInfo = $rewardInfo[0];
                    if ($rewardInfo['value'] >= $max){
                        $exInfo['error'] = "ENGINE_RULE_REWARD_EXCEED_LIMIT";
                        return false;
                    }
                }
            }
            elseif (!is_null($max)){
                $exInfo['error'] = "ENGINE_RULE_REWARD_EXCEED_LIMIT";
                return false;
            }
            return true;
        }
        else{
            if(isset($rewardInfo[0])){
                return false;
            }
        }
        return true;
    }

    private function checkGoodsWithCache(&$cache, $goodsId, $pb_player_id, $client_id, $site_id, $quantity = 0, &$exInfo)
    {
        $key = $goodsId . '-' . $pb_player_id . '-' . $site_id . '-' . $quantity;
        if (!array_key_exists($key, $cache)) {
            $value = $this->checkGoods($goodsId, $pb_player_id, $client_id, $site_id, $quantity, $exInfo);
            $cache[$key] = $value;
        }
        return $cache[$key];
    }

    private function checkGoods($goodsId, $pb_player_id, $client_id, $site_id, $quantity = 0, &$exInfo)
    {
        if (!$quantity) {
            return true;
        }
        $goods = $this->getGoods($site_id, $goodsId);
        if (!$goods) {
            return false;
        }
        $total = isset($goods['group']) ? $this->getGroupQuantity($client_id, $site_id, $goods['group'], $quantity) : $goods['quantity'];
        $max = isset($goods['per_user']) ? $goods['per_user'] : null;
        $per_user_include_inactive = isset($goods['per_user_include_inactive']) ? $goods['per_user_include_inactive'] : false;
        $used = isset($goods['group']) ? $this->getPlayerGoodsGroup($client_id, $site_id, $goods['group'], $pb_player_id, $per_user_include_inactive) : $this->getPlayerGoods($client_id, $site_id, $goodsId, $pb_player_id, $per_user_include_inactive);
        if ($total === 0 || $max === 0) {
            $exInfo['error'] = "ENGINE_RULE_REWARD_OUT_OF_STOCK";
            return false;
        }
        if(!$max){
            return true;
        }
        if ($used >= $max) {
            $exInfo['error'] = "ENGINE_RULE_REWARD_EXCEED_LIMIT";
            return false;
        }
        return true;
    }

    private function checkReward($reward)
    {
        if (!$reward){
            return false;
        }else{
            if (is_null($reward['limit'])){
                return true;
            }else{
                return $reward['limit'] > 0;
            }
        }
    }

    private function isRewardAvailable($reward)
    {
        if(isset($reward['quantity']) && !is_null($reward['quantity']) && $reward['quantity'] <= 0){
            return false;
        }else{
            return true;
        }
    }

    private function getRewardByName($clientId, $siteId, $rewardName )
    {
        $this->set_site_mongodb($siteId);
        $this->mongo_db->where(array(
            'client_id' => $clientId,
            'site_id' => $siteId,
            'name' => $rewardName,
            'status' => true,
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_reward_to_client');

        return $result ? $result[0] : null;
    }

    private function checkRewardLimitPerDay($player_id, $reward, $client_id,  $site_id, $quantity, $timeNow)
    {
        $reward_id = $reward['reward_id'];
        $result = true;
        if (isset($reward['limit_per_day']) && $reward['limit_per_day']) {
            $currentYMD = date("Y-m-d");
            $settingTime = (isset($reward['limit_start_time']) && $reward['limit_start_time']) ? $reward['limit_start_time'] : "00:00";
            $settingTime = strtotime("$currentYMD $settingTime:00");
            $currentTime = strtotime($currentYMD." " . date('H:i:s', $timeNow) );

            if ($settingTime <= $currentTime){ // action has been processed for today !
                $startTimeFilter = $settingTime;
            }else{
                $startTimeFilter =  strtotime( "-1 day" , $settingTime ) ;
            }

            $counter = $this->getCustomPointCounter($client_id, $site_id, $reward_id, $startTimeFilter, $quantity); // to prevent concurrency issue.
            $rejected_point_amount = $this->countRejectedPointInDay($reward_id, $client_id, $site_id, $startTimeFilter);

            $is_reset = false;
            if( $counter > $reward['limit_per_day']){
                $total = $this->deductCustomPointCounter($client_id, $site_id, $reward_id, $startTimeFilter, $quantity);

                if($total < 0){
                    $is_reset = true;
                    $this->resetCustomPointCounter($client_id, $site_id, $reward_id, $startTimeFilter);
                }
                $result = false;
            }

            // log point counter for validation
            $this->logCustomPointCounter($client_id, $site_id, $player_id, $reward_id, $counter, $rejected_point_amount, $result, $startTimeFilter, $currentTime, $is_reset);

        }
        return $result;
    }

    private function checkRewardLimitPerUser($pb_player_id, $reward, $client_id,  $site_id, $quantity)
    {
        $reward_id = $reward['reward_id'];
        if(isset($reward['per_user']) && $reward['per_user']){
            if(isset($reward['per_user_include_deducted']) && $reward['per_user_include_deducted']){
                /* get total reward value in log*/
                $total = $this->countPlayerPointAward($client_id, $site_id, $pb_player_id, $reward_id);

                if(isset($reward['pending']) && !empty($reward['pending']) && $reward['pending'] != false){
                    $rejected_point_amount = $this->countRejectedPointOfPlayer($reward_id, $pb_player_id, $client_id, $site_id);
                }else{
                    $rejected_point_amount = 0;
                }

                if((($total - $rejected_point_amount) + $quantity) > $reward['per_user']){
                    return false;
                }

            }else{
                /* get current reward value */
                $reward_to_player = $this->player_model->getPlayerPoint($client_id, $site_id, $pb_player_id, $reward_id);
                $total = (isset($reward_to_player[0]['value']) && $reward_to_player[0]['value']) ? $reward_to_player[0]['value'] : 0;

                if(isset($reward['pending']) && !empty($reward['pending']) && $reward['pending'] != false){
                    $pending_point_amount = $this->countPendingPointToPlayer($reward_id, $pb_player_id, $client_id, $site_id);
                }else{
                    $pending_point_amount = 0;
                }

                if((($total + $pending_point_amount) + $quantity) > $reward['per_user']){
                    return false;
                }
            }
        }
        return true;
    }

    private function countPlayerPointAward($client_id, $site_id, $pb_player_id, $reward_id){

        $results = $this->mongo_db->aggregate('playbasis_custom_point_log', array(
            array(
                '$match' => array(
                    'client_id' => $client_id,
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'pb_player_id' => $pb_player_id,
                ),
            ),

            array(
                '$group' => array(
                    '_id' => null,
                    'sum' => array('$sum' => '$quantity')
                )
            )
        ));

        $total = $results['result'] ? $results['result'][0]['sum'] : 0;

        return $total;
    }

    private function countRejectedPointInDay($reward_id, $client_id, $site_id, $startTime){

        $results = $this->mongo_db->aggregate('playbasis_reward_status_to_player', array(
            array(
                '$match' => array(
                    'client_id' => $client_id,
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($startTime)),
                    'status' => "reject"
                ),
            ),

            array(
                '$group' => array(
                    '_id' => null,
                    'sum' => array('$sum' => '$value')
                )
            ),
        ));

        $total = $results['result'] ? $results['result'][0]['sum'] : 0;

        return $total;
    }

    private function countRejectedPointOfPlayer($reward_id, $pb_player_id, $client_id, $site_id){

        $results = $this->mongo_db->aggregate('playbasis_reward_status_to_player', array(
            array(
                '$match' => array(
                    'client_id' => $client_id,
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'pb_player_id' => $pb_player_id,
                    'status' => "reject"
                ),
            ),

            array(
                '$group' => array(
                    '_id' => null,
                    'sum' => array('$sum' => '$value')
                )
            ),
        ));

        $total = $results['result'] ? $results['result'][0]['sum'] : 0;

        return $total;
    }

    private function countPendingPointToPlayer($reward_id, $pb_player_id, $client_id, $site_id){

        $results = $this->mongo_db->aggregate('playbasis_reward_status_to_player', array(
            array(
                '$match' => array(
                    'client_id' => $client_id,
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'pb_player_id' => $pb_player_id,
                    'status' => "pending"
                ),
            ),

            array(
                '$group' => array(
                    '_id' => null,
                    'sum' => array('$sum' => '$value')
                )
            ),
        ));

        $total = $results['result'] ? $results['result'][0]['sum'] : 0;

        return $total;
    }

    private function getCustomPointCounter($client_id, $site_id, $reward_id, $startTimeFilter, $quantity){

        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'reward_id' => $reward_id,
            'startTimeFilter' => new MongoDate($startTimeFilter),
        ));

        $this->mongo_db->limit(1);

        $this->mongo_db->set(array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'reward_id' => $reward_id,
        ));
        $this->mongo_db->inc('counter',(int)$quantity);
        $result = $this->mongo_db->findAndModify('playbasis_custom_point_counter',array('upsert' => true, 'new' => true));

        return $total = isset($result['counter']) ? $result['counter'] : $quantity;
    }

    private function logCustomPointCounter($client_id, $site_id, $player_id, $reward_id, $counter, $total_rejected, $result, $startTimeFilter, $currentTime, $is_reset){

        $mongoDate = new MongoDate(time());
        $this->mongo_db->insert('playbasis_custom_point_counter_log', array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'player_id' => $player_id,
            'reward_id' => $reward_id,
            'counter' => intval($counter),
            'total_rejected' => intval($total_rejected),
            'result' => $result,
            'startTimeFilter' => new MongoDate($startTimeFilter),
            'currentTime' => new MongoDate($currentTime),
            'is_reset' => $is_reset,
            'date_added' => $mongoDate,
            'date_modified' => $mongoDate
        ));

    }

    private function deductCustomPointCounter($client_id, $site_id, $reward_id, $startTimeFilter, $quantity){

        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'reward_id' => $reward_id,
            'startTimeFilter' => new MongoDate($startTimeFilter),
        ));

        $this->mongo_db->limit(1);

        $this->mongo_db->dec('counter',(int)$quantity);
        $this->mongo_db->findAndModify('playbasis_custom_point_counter', array('new' => true));

        return $total = isset($result['counter']) ? $result['counter'] : $quantity;
    }

    private function resetCustomPointCounter($client_id, $site_id, $reward_id, $startTimeFilter){

        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'reward_id' => $reward_id,
            'startTimeFilter' => new MongoDate($startTimeFilter),
        ));

        $this->mongo_db->limit(1);

        $this->mongo_db->set('counter',0);
        $this->mongo_db->update('playbasis_custom_point_counter');

    }

    private function getRewardInfo($client_id, $site_id, $reward_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'reward_id' => $reward_id,
            'status' => true,
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_reward_to_client');

        return $result ? $result[0] : null;
    }

    private function getGroupQuantity($client_id, $site_id, $group, $quantity=1)
    {
        $this->set_site_mongodb($site_id);

        $d = new MongoDate();

        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'group' => $group,
            '$and' => array(
                array(
                    '$or' => array(
                        array('date_start' => array('$lte' => $d)),
                        array('date_start' => null)
                    )
                ),
                array(
                    '$or' => array(
                        array('date_expire' => array('$gte' => $d)),
                        array('date_expire' => null)
                    )
                )
            ),
            '$or' => array(
                array(
                    'date_expired_coupon' => array('$exists' => false)
                ),
                array(
                    'date_expired_coupon' => array('$gt' => $d)
                )
            ),
            'status' => true,
            'deleted' => false
        ));
        $this->mongo_db->where_gte('quantity', (int)$quantity);

        return $this->mongo_db->count('playbasis_goods_to_client');
    }

    public function getGoodsByGroup($site_id, $group)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'goods_id',
            'name',
            'description',
            'image',
            'date_start',
            'date_expire',
            'quantity',
            'per_user',
            'per_user_include_inactive',
            'redeem',
            'group',
            'code',
            'organize_id',
            'organize_role',
            'tags'
        ));
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'group' => $group,
            'deleted' => false,
            'status' => true
        ));
        $this->mongo_db->where_gt('quantity', 0);

        $this->mongo_db->where('$or',  array(array('date_expired_coupon' => array('$exists' => false)), array('date_expired_coupon' => array('$gt' => new MongoDate()))));
        return $this->mongo_db->get('playbasis_goods_to_client');
    }
    public function getGroupByID($site_id, $goodsId){
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'goods_id' => $goodsId,
            'deleted' => false,
            'status' => true
        ));
        $result = $this->mongo_db->get('playbasis_goods_to_client');
        return isset($result[0]['group']) ? $result[0]['group'] : null;

    }

    public function getGoods($site_id, $goodsId)
    {
        $group = $this->getGroupByID($site_id, $goodsId);
        $this->set_site_mongodb($site_id);
        $d = new MongoDate();
        $this->mongo_db->select(array(
            'goods_id',
            'name',
            'description',
            'image',
            'per_user',
            'per_user_include_inactive',
            'quantity',
            'date_expired_coupon',
            'group',
            'code',
            'tags'
        ));
        if(is_null($group)){
            $this->mongo_db->where('goods_id', $goodsId);
        } else {
            $this->mongo_db->where('group', $group);
        }

        $this->mongo_db->where(array(
            'site_id' => $site_id,
            '$and' => array(
                array(
                    '$or' => array(
                        array('date_start' => array('$lte' => $d)),
                        array('date_start' => null)
                    )
                ),
                array(
                    '$or' => array(
                        array('date_expire' => array('$gte' => $d)),
                        array('date_expire' => null)
                    )
                )
            ),
            'status' => true,
            'deleted' => false
        ));
        $this->mongo_db->where('$or',  array(array('date_expired_coupon' => array('$exists' => false)), array('date_expired_coupon' => array('$gt' => new MongoDate()))));
        $this->mongo_db->limit(1);
        $ret = $this->mongo_db->get("playbasis_goods_to_client");
        return $ret && isset($ret[0]) ? $ret[0] : array();
    }

    private function getPlayerGoods($client_id, $site_id, $goodsId, $pb_player_id, $include_inactive = false)
    {
        if($include_inactive){
            $this->mongo_db->where(array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'goods_id' => $goodsId,
                'pb_player_id' => $pb_player_id
            ));
            $goods = $this->mongo_db->count('playbasis_goods_log');
        }else{
            $this->mongo_db->select(array('value'));
            $this->mongo_db->where(array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'goods_id' => $goodsId,
                'pb_player_id' => $pb_player_id
            ));
            $this->mongo_db->limit(1);
            $goods = $this->mongo_db->get('playbasis_goods_to_player');
            $goods = isset($goods[0]) ? $goods[0]['value'] : null;
        }

        return $goods;
    }

    private function getPlayerGoodsGroup($client_id, $site_id, $goods_group, $pb_player_id, $include_inactive = false)
    {
        if($include_inactive){
            $this->mongo_db->where(array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'group' => $goods_group,
                'pb_player_id' => $pb_player_id
            ));

            $goods = $this->mongo_db->count('playbasis_goods_log');
        }else{
            $this->mongo_db->where(array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'group' => $goods_group,
                'pb_player_id' => $pb_player_id
            ));

            $this->mongo_db->where_gt('value' , 0);
            $goods = $this->mongo_db->count('playbasis_goods_to_player');
        }

        return $goods;
    }

    private function checkRedeemPoint($client_id, $site_id, $rewardId, $pb_player_id, $quantity = 0)
    {
        $reward_to_player = $this->player_model->getPlayerPoint($client_id, $site_id, $pb_player_id, new MongoId($rewardId));
        $value = (isset($reward_to_player[0]['value']) && $reward_to_player[0]['value']) ? $reward_to_player[0]['value'] : 0;
        return $value >= $quantity;

    }

    private function checkRedeemBadge($client_id, $site_id, $badgeId, $pb_player_id, $quantity = 0)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'badge_id' => new MongoId($badgeId),
            'pb_player_id' => $pb_player_id,
        ));
        if ($quantity) {
            $this->mongo_db->where_gte('value', $quantity);
        }
        return $this->mongo_db->count('playbasis_reward_to_player');
    }

    private function updateplayerRedeemBadge($client_id, $site_id, $badgeId, $pb_player_id, $cl_player_id, $quantity = 0)
    {
        $this->set_site_mongodb($site_id);
        $mongoDate = new MongoDate(time());

        // update player badge table
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'badge_id' => $badgeId
        ));
        $hasBadge = $this->mongo_db->count('playbasis_reward_to_player');
        if ($hasBadge) {
            $this->mongo_db->where(array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'pb_player_id' => $pb_player_id,
                'badge_id' => $badgeId
            ));
            $this->mongo_db->set('date_modified', $mongoDate);
            $this->mongo_db->inc('value', intval($quantity));
            $this->mongo_db->update('playbasis_reward_to_player');
        } else {
            $data = array(
                'pb_player_id' => $pb_player_id,
                'cl_player_id' => $cl_player_id,
                'client_id' => $client_id,
                'site_id' => $site_id,
                'badge_id' => $badgeId,
                'date_added' => $mongoDate,
                'date_modified' => $mongoDate
            );
            $data['value'] = intval($quantity);
            $this->mongo_db->insert('playbasis_reward_to_player', $data);
        }
    }

//	private function matchUrl($inputUrl, $compareUrl, $isRegEx)
    private function matchUrl($inputUrl, $compareUrl)
    {
        // return (boolean) $this->matchUrl($input['url'], $config['url'], $config['regex']);

        $urlFragment = parse_url($inputUrl);
        //check posible index page
        if (!$urlFragment['path']) {
            $inputUrl = '/';
        }
        //if($urlFragment['path'] == '/')
        //	$inputUrl = '/';
        if (preg_match('/\/index\.[a-zA-Z]{3,}$/', $urlFragment['path'])) // match all "/index.*"
        {
            $inputUrl = '/';
        }
        if (preg_match('/\/index\.[a-zA-Z]{3,}\/$/', $urlFragment['path'])) // match all "/index.*/"
        {
            $inputUrl = '/';
        }
        //check query
        if (isset($urlFragment['query']) && $urlFragment['query']) {
            $inputUrl .= '?' . $urlFragment['query'];
        }
        //check fragment
        if (isset($urlFragment['fragment']) && $urlFragment['fragment']) {
            $inputUrl .= '#' . $urlFragment['fragment'];
        }
        //compare url
//      if($isRegEx){
//          if ($compareUrl == '*') $compareUrl = '.*'; // quick-fix for handling a case of '*' pattern
//          if(!preg_match('/^\//', $compareUrl))
//              $compareUrl = "/".$compareUrl;
//          if(!preg_match('/\/$/', $compareUrl))
//              $compareUrl = $compareUrl."/";
//          $match = preg_match($compareUrl, $inputUrl);
//      }else{
//          $match = (string) $compareUrl === (string) $inputUrl;
//      }

        $match = (string)$compareUrl === (string)$inputUrl;

        return $match;
        //e.g.
        //inputurl domain/forum/hello-my-new-notebook
        //input domain/forum/test1234
        //url = domain/forum/(a-zA-Z0-9\_\-)+
    }

    public function groupOr($config, $input, &$exInfo = array())
    {
        $return_val = false;
        $condition_group = $config['condition_group_container'];
        if (is_array($condition_group)) {
            foreach ($condition_group as $con) {
                if (array_key_exists('condition_id', $con)) {
                    $jigsaw_id = new MongoId($con['condition_id']);
                    $processor = $this->getJigsawProcessor($jigsaw_id, $input['site_id']);
                    $jigsaw_result = $this->$processor($con, $input, $exInfo = array());
                    if ($jigsaw_result == true) {
                        $return_val = true;
                        break;
                    }
                }
            }
        }
        return $return_val; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    public function groupNot($config, $input, &$exInfo = array())
    {
        $return_val = true;
        $condition_group = $config['condition_group_container'];
        if (is_array($condition_group)) {
            foreach ($condition_group as $con) {
                if (array_key_exists('condition_id', $con)) {
                    $jigsaw_id = new MongoId($con['condition_id']);
                    $processor = $this->getJigsawProcessor($jigsaw_id, $input['site_id']);
                    $jigsaw_result = $this->$processor($con, $input, $exInfo = array());
                    if ($jigsaw_result == true) {
                        $return_val = false;
                        break;
                    }
                }
            }
        }
        return $return_val; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    /* copied over from client_model */
    private function getJigsawProcessor($jigsawId, $site_id)
    {
        assert($jigsawId);
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('class_path'));
        $this->mongo_db->where(array(
            'jigsaw_id' => $jigsawId
        ));
        $this->mongo_db->limit(1);
        $jigsawProcessor = $this->mongo_db->get('playbasis_game_jigsaw_to_client');
        if ($jigsawProcessor) {
            assert($jigsawProcessor);
            return $jigsawProcessor[0]['class_path'];
        } else {
            return null;
        }
    }

}

?>
