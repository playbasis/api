<?php
defined('BASEPATH') OR exit('No direct script access allowed');

function cmp1($a, $b)
{
    if ($a['_id'] == $b['_id']) {
        return 0;
    }
    return ($a['_id'] < $b['_id']) ? -1 : 1;
}

function change_key_for_getpoint_from_datetime($obj)
{
    $_id = $obj['_id'];
    unset($obj['_id']);
    $obj['reward_id'] = $_id->{'$id'};

    $value = $obj['sum'];
    unset($obj['sum']);
    $obj['value'] = $value;

    return $obj;
}

class Player_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('playbasis');
        $this->load->library('mongo_db');
    }

    public function createPlayer($data, $limit = null)
    {
        try {
            $this->checkClientUserLimitWarning(
                $data['client_id'], $data['site_id'], $limit);
        } catch (Exception $e) {
            if ($e->getMessage() == "USER_EXCEED") {
                return false;
            } else {
                throw new Exception($e->getMessage());
            }
        }
        $this->set_site_mongodb($data['site_id']);
        $mongoDate = new MongoDate(time());
        return $this->mongo_db->insert('playbasis_player', array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'cl_player_id' => $data['player_id'],
            'image' => $data['image'],
            'email' => $data['email'],
            'username' => $data['username'],
            'exp' => intval(0),
            'level' => intval(1),
            'status' => true,
            'phone_number' => (isset($data['phone_number'])) ? $data['phone_number'] : null,
            'first_name' => (isset($data['first_name'])) ? $data['first_name'] : $data['username'],
            'last_name' => (isset($data['last_name'])) ? $data['last_name'] : null,
            'nickname' => (isset($data['nickname'])) ? $data['nickname'] : null,
            'facebook_id' => (isset($data['facebook_id'])) ? $data['facebook_id'] : null,
            'twitter_id' => (isset($data['twitter_id'])) ? $data['twitter_id'] : null,
            'instagram_id' => (isset($data['instagram_id'])) ? $data['instagram_id'] : null,
            'device_id' => (isset($data['device_id'])) ? $data['device_id'] : null,
            'password' => (isset($data['password'])) ? $data['password'] : null,
            'gender' => (isset($data['gender'])) ? intval($data['gender']) : 0,
            'tags' => (isset($data['tags'])) ? $data['tags'] : null,
            'birth_date' => (isset($data['birth_date'])) ? new MongoDate(strtotime($data['birth_date'])) : null,
            'approve_status' => (isset($data['approve_status'])) ? $data['approve_status'] : "pending",
            'date_added' => $mongoDate,
            'date_modified' => $mongoDate,
            'anonymous' => (isset($data['anonymous']) && $data['anonymous']),
        ));
    }

    public function bulkRegisterPlayer($batch_data, $data, $limit = null)
    {

        try {
            $this->checkClientUserLimitWarning(
                $data['client_id'], $data['site_id'], $limit);
        } catch (Exception $e) {
            if ($e->getMessage() == "USER_EXCEED") {
                return false;
            } else {
                throw new Exception($e->getMessage());
            }
        }
        $this->set_site_mongodb($data['site_id']);
        $mongoDate = new MongoDate(time());

        foreach ($batch_data as &$dataAdded){

            $dataAdded['client_id']      = $data['client_id'];
            $dataAdded['site_id']        = $data['site_id'];
            $dataAdded['cl_player_id']   = (isset($dataAdded['cl_player_id'])) ? $dataAdded['cl_player_id'] : null;
            $dataAdded['image']          = (isset($dataAdded['image'])) ? $dataAdded['image'] : null;
            $dataAdded['email']          = (isset($dataAdded['email'])) ? $dataAdded['email'] : null;
            $dataAdded['username']       = (isset($dataAdded['username'])) ? $dataAdded['username'] : null;
            $dataAdded['exp']            = (isset($dataAdded['exp'])) ? $dataAdded['exp'] : intval(0);
            $dataAdded['level']          = (isset($dataAdded['level'])) ? $dataAdded['level'] : intval(0);
            $dataAdded['status']         = (isset($dataAdded['status'])) ? $dataAdded['status'] : null;
            $dataAdded['phone_number']   = (isset($dataAdded['phone_number'])) ? $dataAdded['phone_number'] : null;
            $dataAdded['first_name']     = (isset($dataAdded['first_name'])) ? $dataAdded['first_name'] : null;
            $dataAdded['last_name']      = (isset($dataAdded['last_name'])) ? $dataAdded['last_name'] : null;
            $dataAdded['nickname']       = (isset($dataAdded['nickname'])) ? $dataAdded['nickname'] : null;
            $dataAdded['facebook_id']    = (isset($dataAdded['facebook_id'])) ? $dataAdded['facebook_id'] : null;
            $dataAdded['twitter_id']     = (isset($dataAdded['twitter_id'])) ? $dataAdded['twitter_id'] : null;
            $dataAdded['instagram_id']   = (isset($dataAdded['instagram_id'])) ? $dataAdded['instagram_id'] : null;
            $dataAdded['device_id']      = (isset($dataAdded['device_id'])) ? $dataAdded['device_id'] : null;
            $dataAdded['password']       = (isset($dataAdded['password'])) ? $dataAdded['password'] : null;
            $dataAdded['gender']         = (isset($dataAdded['gender'])) ? $dataAdded['gender'] : null;
            $dataAdded['birth_date']     = (isset($dataAdded['birth_date'])) ? $dataAdded['birth_date'] : null;
            $dataAdded['approve_status'] = (isset($dataAdded['approve_status'])) ? $dataAdded['approve_status'] : null;
            $dataAdded['date_added']     = $mongoDate;
            $dataAdded['date_modified']  = $mongoDate;
            $dataAdded['anonymous']      = (isset($dataAdded['anonymous'])) ? $dataAdded['anonymous'] : null;
        }

        if (!empty($batch_data) && is_array($batch_data)) {
            try {
                return $this->mongo_db->batch_insert('playbasis_player', $batch_data,
                    array("w" => 0, "j" => false, "continueOnError" => true));

            } catch (Exception $e) {
                var_dump($e);
            }

        }
        return false;
    }

    public function readPlayer($id, $site_id, $fields = null)
    {
        if (!$id) {
            return array();
        }
        $this->set_site_mongodb($site_id);
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where('_id', $id);
        $result = $this->mongo_db->get('playbasis_player');
        if (!$result) {
            return $result;
        }
        $result = $result[0];
        if (isset($result['date_added'])) {
            // $result['registered'] = date('Y-m-d H:i:s', $result['date_added']->sec);
            $result['registered'] = datetimeMongotoReadable($result['date_added']);
            unset($result['date_added']);
        }
        if (isset($result['birth_date']) && $result['birth_date']) {
            $result['birth_date'] = date('Y-m-d', $result['birth_date']->sec);
        }
        return $result;
    }

    public function checkPlayerPassword($data)
    {
        $this->mongo_db->where('client_id', $data['client_id']);
        $this->mongo_db->where('site_id', $data['site_id']);
        $this->mongo_db->where('_id', $data['pb_player_id']);
        $this->mongo_db->where('password', $data['password']);

        $result = $this->mongo_db->get('playbasis_player');
        return $result ? $result[0] : array();
    }

    public function readListPlayer($list_id, $site_id, $fields)
    {
        if (empty($list_id)) {
            return array();
        }
        $this->set_site_mongodb($site_id);
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where_in('cl_player_id', $list_id);
        $this->mongo_db->where('site_id', $site_id);
        $result = $this->mongo_db->get('playbasis_player');
        return $result;
    }

    public function readPlayers($site_id, $fields, $offset = 0, $limit = 10)
    {
        $this->set_site_mongodb($site_id);
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        $this->mongo_db->limit($limit, $offset);
        $result = $this->mongo_db->get('playbasis_player');
        return $result;
    }

    public function readPlayersWithFilter($site_id, $fields, $filter = array())
    {
        $this->set_site_mongodb($site_id);
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        if(isset($filter['tags']) && is_array($filter['tags']) && $filter['tags']){
            $this->mongo_db->where_in('tags', $filter['tags']);
        }
        $result = $this->mongo_db->get('playbasis_player');
        return $result;
    }

    public function updatePlayer($id, $site_id, $fieldData)
    {
        if (!$id) {
            return false;
        }

        if (isset($fieldData['gender'])) {
            $fieldData['gender'] = intval($fieldData['gender']);
        }
        if (isset($fieldData['birth_date'])) {
            $fieldData['birth_date'] = new MongoDate(strtotime($fieldData['birth_date']));
        }

        $fieldData['date_modified'] = new MongoDate(time());
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('_id', $id);
        $this->mongo_db->set($fieldData);
        return $this->mongo_db->update('playbasis_player');
    }

    public function setPlayerExp($client_id, $site_id, $pb_player_id, $value)
    {
        $d = new MongoDate(time());
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            '_id' => $pb_player_id,
        ));
        $this->mongo_db->set('exp', $value);
        $this->mongo_db->set('date_modified', $d);
        print $this->mongo_db->update('playbasis_player');
    }

    public function deletePlayer($id, $site_id)
    {
        if (!$id) {
            return false;
        }
        $player = $this->readPlayer($id, $site_id, 'anonymous');
        if ($player['anonymous'] !== null && $player['anonymous']) {
            $this->set_site_mongodb($site_id);
            $this->mongo_db->where('pb_player_id', $id);
            $this->mongo_db->delete_all('playbasis_action_log');

            $this->set_site_mongodb($site_id);
            $this->mongo_db->where('pb_player_id', $id);
            $this->mongo_db->delete_all('playbasis_event_log');

            $this->set_site_mongodb($site_id);
            $this->mongo_db->where('pb_player_id', $id);
            $this->mongo_db->delete_all('playbasis_validated_action_log');
        }
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('_id', $id);
        $this->mongo_db->delete('playbasis_player');

        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('pb_player_id', $id);
        $this->mongo_db->delete_all('playbasis_goods_to_player');

        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('pb_player_id', $id);
        $this->mongo_db->delete_all('playbasis_quest_to_player');

        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('pb_player_id', $id);
        $this->mongo_db->delete_all('playbasis_reward_to_player');

        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('pb_player_id', $id);
        $this->mongo_db->delete_all('playbasis_redeem_to_player');

        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('pb_player_id', $id);
        $this->mongo_db->delete_all('playbasis_quiz_to_player');

        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('pb_player_id', $id);
        $this->mongo_db->delete_all('playbasis_store_organize_to_player');

        return true;
    }

    public function getPlaybasisId($clientData)
    {
        if (!$clientData) {
            return null;
        }
        $this->set_site_mongodb($clientData['site_id']);
        $this->mongo_db->select(array('_id'));
        $this->mongo_db->where(array(
            'client_id' => $clientData['client_id'],
            'site_id' => $clientData['site_id'],
            'cl_player_id' => $clientData['cl_player_id']
        ));
        $this->mongo_db->limit(1);
        $id = $this->mongo_db->get('playbasis_player');
        return ($id) ? $id[0]['_id'] : null;
    }

    public function getPbAndCilentIdByGoodsId($clientData, $goods_id=false, $goods_list=false, $quantity=false)
    {
        if (!$clientData) {
            return null;
        }
        $this->set_site_mongodb($clientData['site_id']);
        $this->mongo_db->select(array(
            'pb_player_id',
            'cl_player_id'
        ));
        $this->mongo_db->where(array(
            'client_id' => $clientData['client_id'],
            'site_id' => $clientData['site_id'],
        ));
        if($goods_id){
            $this->mongo_db->where('goods_id', $goods_id);
        }
        if($goods_list){
            $this->mongo_db->where_in('goods_id', $goods_list);
        }
        if($quantity){
            $this->mongo_db->where_gt('value', 0);
        }
        $this->mongo_db->limit(1);
        $id = $this->mongo_db->get('playbasis_goods_to_player');
        return ($id) ? $id[0] : null;
    }

    public function getClientPlayerId($pb_player_id, $site_id)
    {
        if (!$pb_player_id) {
            return null;
        }
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('cl_player_id'));
        $this->mongo_db->where('_id', $pb_player_id);
        $id = $this->mongo_db->get('playbasis_player');
        return ($id) ? $id[0]['cl_player_id'] : null;
    }

    public function find_player_with_nin($client_id, $site_id, $nin, $limit)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('cl_player_id'));
        $this->mongo_db->where('client_id', $client_id);
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where_not_in('_id', $nin);
        $this->mongo_db->limit($limit);
        return $this->mongo_db->get('playbasis_player');
    }

    public function findPlayerFromService($validToken, $player_id, $service)
    {
        $this->set_site_mongodb($validToken['site_id']);
        $this->mongo_db->where('client_id', $validToken['client_id']);
        $this->mongo_db->where('site_id', $validToken['site_id']);
        $this->mongo_db->where('player_id', $player_id);
        $this->mongo_db->where('service', $service);
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('playbasis_player_service');
        return $results ? $results[0] : null;
    }

    public function insertPlayerService($validToken, $pb_player_id, $player_id, $service)
    {
        $this->set_site_mongodb($validToken['site_id']);
        $mongoDate = new MongoDate(time());
        return $this->mongo_db->insert('playbasis_player_service', array(
            'client_id' => $validToken['client_id'],
            'site_id' => $validToken['site_id'],
            'pb_player_id' => $pb_player_id,
            'player_id' => $player_id,
            'service' => $service,
            'date_added' => $mongoDate,
            'date_modified' => $mongoDate,
        ));
    }

    public function getExpiredPlayerReward($client_id, $site_id, $pb_player_id, $reward_id, $time_now = null)
    {
        $mongo_date = $time_now ? $time_now : new MongoDate();
        $this->mongo_db->where('client_id' , new MongoId($client_id));
        $this->mongo_db->where('site_id' , new MongoId($site_id));
        $this->mongo_db->where('pb_player_id' , new MongoId($pb_player_id));
        $this->mongo_db->where('reward_id' , new MongoId($reward_id));
        $this->mongo_db->where_lte('date_expire' , $mongo_date);
        $result = $this->mongo_db->get('playbasis_reward_expiration_to_player');
        return $result;
    }

    public function getRewardNameById($client_id, $site_id, $reward_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('name'));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'reward_id' => $reward_id
        ));
        $result = $this->mongo_db->get('playbasis_reward_to_client');
        return ($result) ? $result[0]['name'] : $result;
    }

    public function getPlayerPoints($client_id, $site_id, $pb_player_id)
    {
        $this->set_site_mongodb($site_id);
        $time_now = new MongoDate();
        $this->mongo_db->select(array(
            'reward_id',
            'value'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'badge_id' => null,
        ));
        $results = $this->mongo_db->get('playbasis_reward_to_player');
        if($results){
            foreach ($results as &$result){
                $result['reward_name'] = $this->getRewardNameById($client_id, $site_id, $result['reward_id']);
                $reward_expire = $this->getExpiredPlayerReward($client_id, $site_id, $pb_player_id, $result['reward_id'], $time_now);
                if ($reward_expire) {
                    $expire_sum = array_sum(array_column($reward_expire, 'current_value'));
                    $expire_value = $expire_sum ? $expire_sum : 0;
                    $result['value'] = $result['value'] - $expire_value;
                }
                $result['reward_id'] = $result['reward_id'] . "";
            }
        }
        return $results;
    }

    public function getPlayerPoint($client_id, $site_id, $pb_player_id, $reward_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'reward_id',
            'value'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'reward_id' => $reward_id
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_reward_to_player');
        if($result){
            $reward_expire = $this->getExpiredPlayerReward($client_id, $site_id, $pb_player_id, $reward_id);
            if ($reward_expire) {
                $expire_sum = array_sum(array_column($reward_expire, 'current_value'));
                $expire_value = $expire_sum ? $expire_sum : 0;
                $result[0]['value'] = $result[0]['value'] - $expire_value;
            }
        }

        return $result;
    }

    public function getPlayerPointFromDateTime($pb_player_id, $reward_id, $site_id, $starttime = "", $endtime = "")
    {
        $this->set_site_mongodb($site_id);

        $datecondition = array();
        $datestartcondition = array();
        $dateendcondition = array();

        $reset = $this->getResetRewardEvent($site_id, new MongoId($reward_id));
        if ($reset) {
            $reset_time = array_values($reset);
            if ($starttime != '') {
                if ($reset_time[0] > $starttime) {
                    $starttime = $reset_time[0];
                }
            } else {
                $starttime = $reset_time[0];
            }
        }

        if ($starttime != '') {
            $datestartcondition = array('date_added' => array('$gt' => $starttime));
        }
        if ($endtime != '') {
            $dateendcondition = array('date_added' => array('$lte' => $endtime));
        }

        if ($datestartcondition && $dateendcondition) {
            $datecondition = array('$and' => array($datestartcondition, $dateendcondition));
        } else {
            if ($datestartcondition) {
                $datecondition = $datestartcondition;
            } else {
                $datecondition = $dateendcondition;
            }
        }

        $condition = array_merge($datecondition, array('reward_id' => $reward_id, 'pb_player_id' => $pb_player_id));

        $query = array(
            array('$match' => $condition),
            array('$group' => array("_id" => '$reward_id', "sum" => array('$sum' => '$value'))),
        );
        $result = $this->mongo_db->aggregate('playbasis_event_log', $query);

        $result = $result['result'];
        $result = array_map('change_key_for_getpoint_from_datetime', $result);

        return $result;
    }

    public function getLastUsedPoint($pb_player_id, $reward_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'reward_id',
            'date_modified'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'pb_player_id' => $pb_player_id,
            'reward_id' => $reward_id,
            'site_id' => $site_id
        ));
        $this->mongo_db->limit(1);
        return $this->mongo_db->get('playbasis_reward_to_player');
    }

    public function getLastCronModifiedPoint($pb_player_id, $reward_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'reward_id',
            'date_cron_modified'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'pb_player_id' => $pb_player_id,
            'reward_id' => $reward_id,
            'site_id' => $site_id
        ));
        $this->mongo_db->limit(1);
        return $this->mongo_db->get('playbasis_reward_to_player');
    }

    public function getLastActionPerform($pb_player_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'action_id',
            'action_name',
            'date_added'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $this->mongo_db->order_by(array('date_added' => 'desc'));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_action_log');
        if (!$result) {
            return $result;
        }
        $result = $result[0];
        $result['action_id'] = $result['action_id'] . "";
        $result['time'] = datetimeMongotoReadable($result['date_added']);
        unset($result['date_added']);
        return $result;
    }

    public function getActionPerform($pb_player_id, $action_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'action_id',
            'action_name',
            'date_added'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'pb_player_id' => $pb_player_id,
            'action_id' => $action_id
        ));
        $this->mongo_db->order_by(array('date_added' => 'desc'));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_action_log');
        if (!$result) {
            if($site_id == new MongoId("57aab56572d3e1e0418b456a") || $site_id == new MongoId("5825a0d5be120b84688b4c17")){
                $result['time'] = datetimeMongotoReadable(new MongoDate(strtotime("-1 days")));
            }
            return $result;
        }
        $result = $result[0];
        $result['action_id'] = $result['action_id'] . "";
        $result['time'] = datetimeMongotoReadable($result['date_added']);
        unset($result['date_added']);
        return $result;
    }

    public function getActionCount($pb_player_id, $action_id, $site_id, $key = array(), $value = array())
    {
        $fields = array(
            'pb_player_id' => $pb_player_id,
            'action_id' => $action_id
        );
        if ($key && $value){
            foreach ($key as $index => $k){
                $this->mongo_db->where('parameters.'.$k, $value[$index]);
            }
        }
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where($fields);
        $count = $this->mongo_db->count('playbasis_action_log');
        $this->mongo_db->select(array(
            'action_id',
            'action_name'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where($fields);
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_action_log');
        $result = ($result) ? $result[0] : array();
        if ($result) {
            $result['action_id'] = $result['action_id'] . "";
        }
        $result['count'] = $count;
        return $result;
    }

    public function getActionCountFromDatetime(
        $pb_player_id,
        $action_id,
        $action_filter,
        $filtered_param,
        $site_id,
        $starttime = "",
        $endtime = ""
    ) {
        $fields = array(
            'pb_player_id' => $pb_player_id,
            'action_id' => $action_id,
            'site_id' => $site_id
        );
        if (is_array($filtered_param)) {
            foreach ($filtered_param as $param_name => $param) {
                if (isset($param['completion_string']) && $param['completion_string']) {
                    switch ($param['operation']) {
                        case "=":
                            $fields['parameters' . '.' . $param_name] = $param['completion_string'];
                            break;
                        case ">":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$gt' => (int)$param['completion_string']);
                            break;
                        case ">=":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$gte' => (int)$param['completion_string']);
                            break;
                        case "<":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$lt' => (int)$param['completion_string']);
                            break;
                        case "<=":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$lte' => (int)$param['completion_string']);
                            break;
                    }
                }
            }
        } elseif (!empty($action_filter)) {
            $fields['url'] = $action_filter;
        }
        $datecondition = array();
        if ($starttime != '') {
            $datecondition = array_merge($datecondition, array('$gt' => $starttime));
        }
        if ($endtime != '') {
            $datecondition = array_merge($datecondition, array('$lte' => $endtime));
        }

        $this->mongo_db->where($fields);
        if ($starttime != '' || $endtime != '') {
            $this->mongo_db->where('date_added', $datecondition);
        }
        $count = $this->mongo_db->count('playbasis_validated_action_log');

        $this->mongo_db->select(array(
            'action_id',
            'action_name'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where($fields);
        if ($starttime != '' || $endtime != '') {
            $this->mongo_db->where('date_added', $datecondition);
        }
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_validated_action_log');
        $result = ($result) ? $result[0] : array();
        $result['count'] = $count;

        return $result;
    }

    public function getActionHistoryDetail($data)
    {
        $this->mongo_db->select(array(
            'action_name',
            'parameters',
            'date_added'
        ));
        $this->mongo_db->where('client_id', new MongoID($data['client_id']));
        $this->mongo_db->where('site_id', new MongoID($data['site_id']));
        $this->mongo_db->where('cl_player_id', $data['cl_player_id']);
        if(isset($data['action_name']) && is_array($data['action_name'])){
            $this->mongo_db->where_in('action_name', $data['action_name']);
        }
        if(isset($data['date_added'])){
            $this->mongo_db->where('date_added', $data['date_added']);
        }
        if(isset($data['offset'])){
            $this->mongo_db->offset((int)$data['offset']);
        }
        if(isset($data['limit'])){
            $this->mongo_db->limit((int)$data['limit']);
        }
        return $this->mongo_db->get('playbasis_validated_action_log');
    }
    
    public function getActionSumFromDatetime(
        $pb_player_id,
        $action_id,
        $action_filter,
        $filtered_param,
        $site_id,
        $starttime = "",
        $endtime = ""
    ) {
        $fields = array(
            'pb_player_id' => $pb_player_id,
            'action_id' => $action_id,
            'site_id' => $site_id
        );
        if (is_array($filtered_param)) {
            foreach ($filtered_param as $param_name => $param) {
                if (isset($param['completion_string']) && $param['completion_string']) {
                    switch ($param['operation']) {
                        case "=":
                            $fields['parameters' . '.' . $param_name] = $param['completion_string'];
                            break;
                        case ">":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$gt' => (int)$param['completion_string']);
                            break;
                        case ">=":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$gte' => (int)$param['completion_string']);
                            break;
                        case "<":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$lt' => (int)$param['completion_string']);
                            break;
                        case "<=":
                            $fields['parameters' . '.' . $param_name . POSTFIX_NUMERIC_PARAM] = array('$lte' => (int)$param['completion_string']);
                            break;
                    }
                }
            }
        }
        $datecondition = array();
        if ($starttime != '') {
            $datecondition = array_merge($datecondition, array('$gt' => $starttime));
        }
        if ($endtime != '') {
            $datecondition = array_merge($datecondition, array('$lte' => $endtime));
        }

        if ($starttime != '' || $endtime != '') {
            $fields = array_merge($fields, array('date_added' => $datecondition));
        }
        $raw_result = $this->mongo_db->aggregate('playbasis_validated_action_log', array(
            array(
                '$match' => $fields,
            ),
            array(
                '$group' => array(
                    '_id' => array('pb_player_id' => '$pb_player_id'),
                    'total' => array('$sum' => '$parameters.' . $action_filter . POSTFIX_NUMERIC_PARAM)
                )
            ),
        ));

        $sum = isset($raw_result['result'][0]['total']) ? $raw_result['result'][0]['total'] : 0;
        $this->mongo_db->select(array(
            'action_id',
            'action_name'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where($fields);

        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_validated_action_log');
        $result = ($result) ? $result[0] : array();

        $result['sum'] = $sum;

        return $result;
    }

    public function getBadge($pb_player_id, $site_id, $tags = null,$exclude_invisible_badge = false)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'badge_id',
            'value'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $this->mongo_db->where_ne('badge_id', null);
        $badges = $this->mongo_db->get('playbasis_reward_to_player');
        if (!$badges) {
            return array();
        }
        $playerBadges = array();

        foreach ($badges as $badge) {
            //get badge data
            $this->mongo_db->select(array(
                'image',
                'name',
                'description',
                'hint',
                'tags'
            ));
            $this->mongo_db->select(array(), array('_id'));
            $this->mongo_db->where(array(
                'badge_id' => $badge['badge_id'],
                'site_id' => $site_id,
//                'deleted' => false
            ));
            if($exclude_invisible_badge){
                $this->mongo_db->where_ne('visible',false);
            }
            if ($tags){
                $this->mongo_db->where_in('tags', $tags);
            }
            $this->mongo_db->limit(1);
            $result = $this->mongo_db->get('playbasis_badge_to_client');
            if (!$result) {
                continue;
            }

            $result = $result[0];
            $badge['badge_id'] = $badge['badge_id'] . "";
            $badge['image'] = $this->config->item('IMG_PATH') . $result['image'];
            $badge['name'] = $result['name'];
            $badge['description'] = $result['description'];
            $badge['amount'] = $badge['value'];
            $badge['hint'] = $result['hint'];
            $badge['tags'] = isset($result['tags']) ? $result['tags'] : null;
            unset($badge['value']);
            array_push($playerBadges, $badge);
        }
        return $playerBadges;
    }

    public function getBadgeCount($site_id, $pb_player_id, $badge_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('value'));
        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $this->mongo_db->where('badge_id', $badge_id);
        $badges = $this->mongo_db->get('playbasis_reward_to_player');
        return $badges ? $badges[0]['value'] : 0;
    }

    public function getLastEventTime($pb_player_id, $site_id, $eventType)
    {
        $this->set_site_mongodb($site_id);

        $reset = $this->getResetRewardEvent($site_id);

        $this->mongo_db->select(array('date_added'));
        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $this->mongo_db->where('event_type', $eventType);
        if ($reset) {
            $reset_where = array();
            $reset_not_id = array();
            foreach ($reset as $k => $v) {
                $reset_not_id[] = new MongoId($k);
                $reset_where[] = array('reward_id' => new MongoId($k), 'date_added' => array('$gte' => $v));
            }
            $reset_where[] = array('reward_id' => array('$nin' => $reset_not_id));

            $this->mongo_db->where(array('$or' => $reset_where));
        }
        $this->mongo_db->order_by(array('date_added' => 'desc'));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_event_log');

        if ($result) {
            return datetimeMongotoReadable($result[0]['date_added']);
        }
        return '0000-00-00 00:00:00';
    }

    public function completeObjective($pb_player_id, $objective_id, $client_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $mongoDate = new MongoDate(time());
        return $this->mongo_db->insert('playbasis_objective_to_player', array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'objective_id' => $objective_id,
            'date_added' => $mongoDate,
            'date_modified' => $mongoDate
        ));
    }

    private function removeDeletedPlayers($results, $limit, $rankedBy)
    {
        $total = count($results);
        $c = 0;
        for ($i = 0; $i < $total; $i++) {
            if ($c < $limit) {
                $this->mongo_db->select(array('cl_player_id','first_name','last_name','image'));
                if (isset($results[$i]['_id']['pb_player_id'])) {
                    $results[$i]['pb_player_id'] = $results[$i]['_id']['pb_player_id'];
                    unset($results[$i]['_id']);
                }
                $this->mongo_db->where(array('_id' => $results[$i]['pb_player_id']));
                $p = $this->mongo_db->get('playbasis_player');
                if ($p) {
                    $p = $p[0];
                    $results[$i]['player_id'] = $p['cl_player_id'];
                    $results[$i]['first_name'] = $p['first_name'];
                    $results[$i]['last_name'] = $p['last_name'];
                    $results[$i]['image'] = $p['image'];
                    $results[$i][$rankedBy] = $results[$i]['value'];
                    unset($results[$i]['pb_player_id']);
                    unset($results[$i]['cl_player_id']);
                    unset($results[$i]['value']);
                    $c++;
                } else {
                    unset($results[$i]);
                }
            } else {
                unset($results[$i]);
            }
        }
        return array_values($results);
    }

    private function getRewardIdByName($client_id, $site_id, $name)
    {
        $this->mongo_db->select(array('reward_id'));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'name' => $name
        ));
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('playbasis_reward_to_client');
        return $results ? $results[0]['reward_id'] : null;
    }

    private function getTotalDays($year, $month)
    {
        $t = strtotime($year . '-' . (strlen($month) < 2 ? '0' : '') . $month . '-15 00:00:00');
        $next_month = strtotime('+1 month', $t);
        $first = date('Y-m-01 00:00:00', $next_month);
        $d = strtotime('-1 day', strtotime($first));
        return intval(date('d', $d));
    }

    private function getWeek($d, $daysPerWeek = 7)
    {
        for ($w = 0; $w < 4; $w++) {
            if ($d < ($w + 1) * $daysPerWeek + 1) {
                return $w;
            }
        }
        return 1;
    }

    public function getLeaderboardByLevel($limit, $client_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('cl_player_id', 'first_name', 'last_name', 'username', 'image', 'exp', 'level'));
        $this->mongo_db->where(array(
            'status' => true,
            'site_id' => $site_id,
            'client_id' => $client_id
        ));
        $this->mongo_db->order_by(array('level' => -1, 'exp' => -1));
        $this->mongo_db->limit($limit);
        $result = $this->mongo_db->get('playbasis_player');
        $ret = array();
        foreach ($result as $i => $each) {
            $ret[] = array(
                'player_id' => $result[$i]['cl_player_id'],
                'first_name' => $result[$i]['first_name'],
                'last_name' => $result[$i]['last_name'],
                'image' => $result[$i]['image'],
                'level' => $result[$i]['level'],
            );
        }
        return $ret;
    }

    public function getLeaderboard($ranked_by, $limit, $client_id, $site_id)
    {
        $limit = intval($limit);
        $this->set_site_mongodb($site_id);
        /* get reward_id */
        $reward_id = $this->getRewardIdByName($client_id, $site_id, $ranked_by);
        /* list top players */
        $this->mongo_db->select(array(
            'pb_player_id',
            'cl_player_id',
            'value'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'reward_id' => $reward_id,
            'client_id' => $client_id,
            'site_id' => $site_id
        ));
        $this->mongo_db->order_by(array('value' => 'desc'));
        $this->mongo_db->limit($limit + 5);
        $result1 = $this->mongo_db->get('playbasis_reward_to_player');
        return $this->removeDeletedPlayers($result1, $limit, $ranked_by);
    }

    public function getWeeklyLeaderboard($ranked_by, $limit, $client_id, $site_id)
    {
        $limit = intval($limit);
        $this->set_site_mongodb($site_id);
        /* get reward_id */
        $reward_id = $this->getRewardIdByName($client_id, $site_id, $ranked_by);
        /* get latest RESET event for that reward_id (if exists) */
        $reset = $this->getResetRewardEvent($site_id, $reward_id);
        $resetTime = null;
        if ($reset) {
            $reset_time = array_values($reset);
            $resetTime = $reset_time[0]->sec;
        }
        /* list top players */
        $now = time();
        $totalDays = $this->getTotalDays(date('Y', $now), date('m', $now));
        $daysPerWeek = round($totalDays / 4.0);
        $d = intval(date('d', $now));
        $w = $this->getWeek($d, $daysPerWeek);
        $d = $w * $daysPerWeek + 1;
        $first = date('Y-m-' . ($d < 10 ? '0' : '') . $d, $now);
        $from = strtotime($first . ' 00:00:00');
        if ($resetTime && $resetTime > $from) {
            $from = $resetTime;
        }
        $results = $this->mongo_db->aggregate('playbasis_event_log', array(
            array(
                '$match' => array(
                    'event_type' => 'REWARD',
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($from)),
                ),
            ),
            array(
                '$group' => array(
                    '_id' => array('pb_player_id' => '$pb_player_id'),
                    'value' => array('$sum' => '$value')
                )
            ),
            array(
                '$sort' => array('value' => -1),
            ),
            array(
                '$limit' => $limit + 5,
            ),
        ));
        return $results ? $this->removeDeletedPlayers($results['result'], $limit, $ranked_by) : array();
    }

    public function getMonthlyLeaderboard($ranked_by, $limit, $client_id, $site_id)
    {
        $limit = intval($limit);
        $this->set_site_mongodb($site_id);
        /* get reward_id */
        $reward_id = $this->getRewardIdByName($client_id, $site_id, $ranked_by);
        /* get latest RESET event for that reward_id (if exists) */
        $reset = $this->getResetRewardEvent($site_id, $reward_id);
        $resetTime = null;
        if ($reset) {
            $reset_time = array_values($reset);
            $resetTime = $reset_time[0]->sec;
        }
        /* list top players */
        $now = time();
        $first = date('Y-m-01', $now);
        $from = strtotime($first . ' 00:00:00');
        if ($resetTime && $resetTime > $from) {
            $from = $resetTime;
        }
        $results = $this->mongo_db->aggregate('playbasis_event_log', array(
            array(
                '$match' => array(
                    'event_type' => 'REWARD',
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($from)),
                ),
            ),
            array(
                '$group' => array(
                    '_id' => array('pb_player_id' => '$pb_player_id'),
                    'value' => array('$sum' => '$value')
                )
            ),
            array(
                '$sort' => array('value' => -1),
            ),
            array(
                '$limit' => $limit + 5,
            ),
        ));
        return $results ? $this->removeDeletedPlayers($results['result'], $limit, $ranked_by) : array();
    }

    public function sortPlayersByReward($client_id, $site_id, $reward_id, $limit = null)
    {
        $this->mongo_db->select(array(
            'cl_player_id',
            'value'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'reward_id' => $reward_id,
            'client_id' => $client_id,
            'site_id' => $site_id
        ));
        $this->mongo_db->order_by(array('value' => 'desc'));
        if ($limit) {
            $this->mongo_db->limit($limit);
        }
        return $this->mongo_db->get('playbasis_reward_to_player');
    }

    public function getLeaderboards($limit, $client_id, $site_id)
    {
        //get all rewards
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'reward_id',
            'name'
        ));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'group' => 'POINT'
        ));
        $rewards = $this->mongo_db->get('playbasis_reward_to_client');
        if (!$rewards) {
            return array();
        }
        $result = array();
        foreach ($rewards as $reward) {
            //get points for the reward id
            $reward_id = $reward['reward_id'];
            $name = $reward['name'];
            $this->mongo_db->select(array(
                'pb_player_id',
                'cl_player_id',
                'value'
            ));
            $this->mongo_db->select(array(), array('_id'));
            $this->mongo_db->where(array(
                'reward_id' => $reward_id,
                'client_id' => $client_id,
                'site_id' => $site_id
            ));
            $this->mongo_db->order_by(array('value' => 'desc'));
            $this->mongo_db->limit($limit + 5);
            $ranking = $this->mongo_db->get('playbasis_reward_to_player');
            $result[$name] = $this->removeDeletedPlayers($ranking, $limit, $name);
        }
        return $result;
    }

    public function getWeeklyLeaderboards($limit, $client_id, $site_id)
    {
        /* get all rewards */
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'reward_id',
            'name'
        ));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'group' => 'POINT'
        ));
        $rewards = $this->mongo_db->get('playbasis_reward_to_client');
        if (!$rewards) {
            return array();
        }
        $now = time();
        $totalDays = $this->getTotalDays(date('Y', $now), date('m', $now));
        $daysPerWeek = round($totalDays / 4.0);
        $d = intval(date('d', $now));
        $w = $this->getWeek($d, $daysPerWeek);
        $d = $w * $daysPerWeek + 1;
        $first = date('Y-m-' . ($d < 10 ? '0' : '') . $d, $now);
        $from = strtotime($first . ' 00:00:00');
        $result = array();
        foreach ($rewards as $reward) {
            $reward_id = $reward['reward_id'];
            $name = $reward['name'];
            /* get latest RESET event for that reward_id (if exists) */
            $reset = $this->getResetRewardEvent($site_id, $reward_id);
            $resetTime = null;
            if ($reset) {
                $reset_time = array_values($reset);
                $resetTime = $reset_time[0]->sec;
            }
            /* list top players */
            if ($resetTime && $resetTime > $from) {
                $from = $resetTime;
            }
            $results = $this->mongo_db->aggregate('playbasis_event_log', array(
                array(
                    '$match' => array(
                        'event_type' => 'REWARD',
                        'site_id' => $site_id,
                        'reward_id' => $reward_id,
                        'date_added' => array('$gte' => new MongoDate($from)),
                    ),
                ),
                array(
                    '$group' => array(
                        '_id' => array('pb_player_id' => '$pb_player_id'),
                        'value' => array('$sum' => '$value')
                    )
                ),
                array(
                    '$sort' => array('value' => -1),
                ),
                array(
                    '$limit' => $limit + 5,
                ),
            ));
            $result[$name] = $results ? $this->removeDeletedPlayers($results['result'], $limit, $name) : array();
        }
        return $result;
    }

    public function getMonthlyLeaderboards($limit, $client_id, $site_id)
    {
        /* get all rewards */
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'reward_id',
            'name'
        ));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'group' => 'POINT'
        ));
        $rewards = $this->mongo_db->get('playbasis_reward_to_client');
        if (!$rewards) {
            return array();
        }
        $now = time();
        $first = date('Y-m-01', $now);
        $from = strtotime($first . ' 00:00:00');
        $result = array();
        foreach ($rewards as $reward) {
            $reward_id = $reward['reward_id'];
            $name = $reward['name'];
            /* get latest RESET event for that reward_id (if exists) */
            $reset = $this->getResetRewardEvent($site_id, $reward_id);
            $resetTime = null;
            if ($reset) {
                $reset_time = array_values($reset);
                $resetTime = $reset_time[0]->sec;
            }
            /* list top players */
            if ($resetTime && $resetTime > $from) {
                $from = $resetTime;
            }
            $results = $this->mongo_db->aggregate('playbasis_event_log', array(
                array(
                    '$match' => array(
                        'event_type' => 'REWARD',
                        'site_id' => $site_id,
                        'reward_id' => $reward_id,
                        'date_added' => array('$gte' => new MongoDate($from)),
                    ),
                ),
                array(
                    '$group' => array(
                        '_id' => array('pb_player_id' => '$pb_player_id'),
                        'value' => array('$sum' => '$value')
                    )
                ),
                array(
                    '$sort' => array('value' => -1),
                ),
                array(
                    '$limit' => $limit + 5,
                ),
            ));
            $result[$name] = $results ? $this->removeDeletedPlayers($results['result'], $limit, $name) : array();
        }
        return $result;
    }

    public function getWeeklyPlayerReward($client_id, $site_id, $reward_id, $pb_player_id)
    {
        $this->set_site_mongodb($site_id);
        /* get latest RESET event for that reward_id (if exists) */
        $reset = $this->getResetRewardEvent($site_id, $reward_id);
        $resetTime = null;
        if ($reset) {
            $reset_time = array_values($reset);
            $resetTime = $reset_time[0]->sec;
        }
        /* list top players */
        $now = time();
        $totalDays = $this->getTotalDays(date('Y', $now), date('m', $now));
        $daysPerWeek = round($totalDays / 4.0);
        $d = intval(date('d', $now));
        $w = $this->getWeek($d, $daysPerWeek);
        $d = $w * $daysPerWeek + 1;
        $first = date('Y-m-' . ($d < 10 ? '0' : '') . $d, $now);
        $from = strtotime($first . ' 00:00:00');
        if ($resetTime && $resetTime > $from) {
            $from = $resetTime;
        }
        $results = $this->mongo_db->aggregate('playbasis_event_log', array(
            array(
                '$match' => array(
                    'event_type' => 'REWARD',
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($from)),
                    'pb_player_id' => $pb_player_id,
                ),
            ),
            array(
                '$group' => array('_id' => null, 'value' => array('$sum' => '$value'))
            ),
        ));
        return $results && isset($results['result'][0]) ? $results['result'][0]['value'] : 0;
    }

    public function getMonthlyPlayerReward($client_id, $site_id, $reward_id, $pb_player_id)
    {
        $this->set_site_mongodb($site_id);
        /* get latest RESET event for that reward_id (if exists) */
        $reset = $this->getResetRewardEvent($site_id, $reward_id);
        $resetTime = null;
        if ($reset) {
            $reset_time = array_values($reset);
            $resetTime = $reset_time[0]->sec;
        }
        /* list top players */
        $now = time();
        $first = date('Y-m-01', $now);
        $from = strtotime($first . ' 00:00:00');
        if ($resetTime && $resetTime > $from) {
            $from = $resetTime;
        }
        $results = $this->mongo_db->aggregate('playbasis_event_log', array(
            array(
                '$match' => array(
                    'event_type' => 'REWARD',
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($from)),
                    'pb_player_id' => $pb_player_id,
                ),
            ),
            array(
                '$group' => array('_id' => null, 'value' => array('$sum' => '$value'))
            ),
        ));
        return $results && isset($results['result'][0]) ? $results['result'][0]['value'] : 0;
    }

    public function countWeeklyPlayersHigherReward($client_id, $site_id, $reward_id, $value)
    {
        $this->set_site_mongodb($site_id);
        /* get latest RESET event for that reward_id (if exists) */
        $reset = $this->getResetRewardEvent($site_id, $reward_id);
        $resetTime = null;
        if ($reset) {
            $reset_time = array_values($reset);
            $resetTime = $reset_time[0]->sec;
        }
        /* list top players */
        $now = time();
        $totalDays = $this->getTotalDays(date('Y', $now), date('m', $now));
        $daysPerWeek = round($totalDays / 4.0);
        $d = intval(date('d', $now));
        $w = $this->getWeek($d, $daysPerWeek);
        $d = $w * $daysPerWeek + 1;
        $first = date('Y-m-' . ($d < 10 ? '0' : '') . $d, $now);
        $from = strtotime($first . ' 00:00:00');
        if ($resetTime && $resetTime > $from) {
            $from = $resetTime;
        }
        $results = $this->mongo_db->aggregate('playbasis_event_log', array(
            array(
                '$match' => array(
                    'event_type' => 'REWARD',
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($from)),
                ),
            ),
            array(
                '$group' => array(
                    '_id' => array('pb_player_id' => '$pb_player_id'),
                    'value' => array('$sum' => '$value')
                )
            ),
            array(
                '$match' => array(
                    'value' => array('$gt' => $value),
                ),
            ),
            array(
                '$group' => array('_id' => null, 'value' => array('$sum' => 1))
            ),
        ));
        return $results && isset($results['result'][0]) ? $results['result'][0]['value'] : 0;
    }

    public function countMonthlyPlayersHigherReward($client_id, $site_id, $reward_id, $value)
    {
        $this->set_site_mongodb($site_id);
        /* get latest RESET event for that reward_id (if exists) */
        $reset = $this->getResetRewardEvent($site_id, $reward_id);
        $resetTime = null;
        if ($reset) {
            $reset_time = array_values($reset);
            $resetTime = $reset_time[0]->sec;
        }
        /* list top players */
        $now = time();
        $first = date('Y-m-01', $now);
        $from = strtotime($first . ' 00:00:00');
        if ($resetTime && $resetTime > $from) {
            $from = $resetTime;
        }
        $results = $this->mongo_db->aggregate('playbasis_event_log', array(
            array(
                '$match' => array(
                    'event_type' => 'REWARD',
                    'site_id' => $site_id,
                    'reward_id' => $reward_id,
                    'date_added' => array('$gte' => new MongoDate($from)),
                ),
            ),
            array(
                '$group' => array(
                    '_id' => array('pb_player_id' => '$pb_player_id'),
                    'value' => array('$sum' => '$value')
                )
            ),
            array(
                '$match' => array(
                    'value' => array('$gt' => $value),
                ),
            ),
            array(
                '$group' => array('_id' => null, 'value' => array('$sum' => 1))
            ),
        ));
        return $results && isset($results['result'][0]) ? $results['result'][0]['value'] : 0;
    }

    private function checkClientUserLimitWarning($client_id, $site_id, $limit)
    {
        if (!$limit) {
            return;
        } //client has no user limit

        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'site_name',
            /* 'limit_users', */  // use plan instead
            'last_send_limit_users'
        ));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            '_id' => $site_id
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_client_site');
        assert($result);
        $result = $result[0];
        $domain_name_client = $result['site_name'];

        $last_send = $result['last_send_limit_users'] ? $result['last_send_limit_users']->sec : null;
        $next_send = $last_send + (7 * 24 * 60 * 60); //next week from last send

        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id
        ));
        $usersCount = $this->mongo_db->count('playbasis_player');
        if ($usersCount > ($limit * 0.95)) {
            if (time() > $next_send) {
                $this->mongo_db->select(array('user_id'));
                $this->mongo_db->where(array(
                    'client_id' => $client_id
                ));
                $result = $this->mongo_db->get('user_to_client');
                $user_id_list = array();
                foreach ($result as $r) {
                    array_push($user_id_list, $r['user_id']);
                }
                $this->mongo_db->select(array('email'));
                $this->mongo_db->where_in(
                    'user_id', $user_id_list
                );
                $result = $this->mongo_db->get('user');
                $email_list = array();
                foreach ($result as $r) {
                    array_push($email_list, $r['email']);
                }

                //$this->load->library('email');
                $this->load->library('parser');
                $data = array(
                    'user_left' => ($limit - $usersCount),
                    'user_count' => $usersCount,
                    'user_limit' => $limit,
                    'domain_name_client' => $domain_name_client,
                );
                $config['mailtype'] = 'html';
                $config['charset'] = 'utf-8';
                $email = $email_list;
                $subject = "Playbasis user limit alert";
                $htmlMessage = $this->parser->parse('limit_user_alert.html', $data, true);

                //email client to upgrade account
                /*$this->email->initialize($config);
                $this->email->clear();
                $this->email->from(EMAIL_FROM, 'Playbasis');
    //            $this->email->to($email);
                $this->email->to('cscteam@playbasis.com','devteam@playbasis.com');
    //            $this->email->bcc('cscteam@playbasis.com');
                $this->email->subject($subject);
                $this->email->message($htmlMessage);
                $this->email->send();*/

                $this->amazon_ses->from(EMAIL_FROM, 'Playbasis');
                $this->amazon_ses->to('cscteam@playbasis.com,devteam@playbasis.com');
                $this->amazon_ses->subject($subject);
                $this->amazon_ses->message($htmlMessage);
                $this->amazon_ses->send();

                $this->updateLastAlertLimitUser($client_id, $site_id);
            }

            if ($usersCount >= $limit) {
                throw new Exception("USER_EXCEED");
            }
        }
    }

    private function updateLastAlertLimitUser($client_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $mongoDate = new MongoDate(time());

        $this->mongo_db->where(array(
            "client_id" => $client_id,
            "_id" => $site_id
        ))->set(array(
            "last_send_limit_users" => $mongoDate
        ))->update("playbasis_client_site");
        return $mongoDate;
    }

    public function getPointHistoryFromPlayerID($pb_player_id, $site_id, $reward_id, $offset, $limit, $order = null)
    {

        $this->set_site_mongodb($site_id);

        if ($reward_id) {
            $reset = $this->getResetRewardEvent($site_id, $reward_id);

            if ($reset) {
                $reset_time = array_values($reset);
                $starttime = $reset_time[0];

                $this->mongo_db->where('date_added', array('$gt' => $starttime));
            }

            $this->mongo_db->where('reward_id', $reward_id);
        } else {

            $reset = $this->getResetRewardEvent($site_id, $reward_id);

            if ($reset) {
                $reset_where = array();
                $reset_not_id = array();
                foreach ($reset as $k => $v) {
                    $reset_not_id[] = new MongoId($k);
                    $reset_where[] = array('reward_id' => new MongoId($k), 'date_added' => array('$gte' => $v));
                }
                $reset_where[] = array('reward_id' => array('$nin' => $reset_not_id));

                $this->mongo_db->where(array('$or' => $reset_where));
            }

            $this->mongo_db->where_ne('reward_id', null);
        }
        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('event_type', 'REWARD');
        $this->mongo_db->where_gt('value', 0);
        $this->mongo_db->limit((int)$limit);
        $this->mongo_db->offset((int)$offset);
        $this->mongo_db->select(array(
            'reward_id',
            'reward_name',
            'value',
            'message',
            'date_added',
            'action_log_id',
            'quest_id',
            'mission_id',
            'goods_id',
            'event_type',
            'quiz_id'
        ));
        $this->mongo_db->select(array(), array('_id'));

        if (mb_strtolower($order) == 'asc') {
            $order = 1;
        } else {
            $order = -1;
        }
        $this->mongo_db->order_by(array('date_added' => $order));

        $event_log = $this->mongo_db->get('playbasis_event_log');

        foreach ($event_log as &$event) {
            $action = $this->getActionLogDetail($event['action_log_id']);

            $event['date_added'] = datetimeMongotoReadable($event['date_added']);
            if ($action) {
                $event['action_name'] = $action['action_name'];
                $event['action_parameters'] = $action['parameters'];
                $event['action_time'] = datetimeMongotoReadable($action['date_added']);
                $event['string_filter'] = (isset($action['parameters']['url']) ? $action['parameters']['url'] : '') . "";
            }
            if (isset($event['quest_id']) && $event['quest_id']) {
                if (isset($event['mission_id']) && $event['mission_id']) {
                    $event['action_name'] = 'mission_reward';
                } else {
                    $event['action_name'] = 'quest_reward';
                }
                $event['action_icon'] = 'fa-trophy';
            }
            if (isset($event['goods_id']) && $event['goods_id']) {
                $event['action_name'] = 'redeem_goods';
                $event['action_icon'] = 'fa-gift';
            }
            if (isset($event['quiz_id']) && $event['quiz_id']) {
                $event['action_name'] = 'quiz_reward';
                $event['action_icon'] = 'fa-bar-chart';
            }
            if ($event['event_type'] == 'LOGIN') {
                $event['action_name'] = 'login';
                $event['action_icon'] = 'fa-sign-in';
            }
            unset($event['action_log_id']);
            unset($event['quest_id']);
            unset($event['mission_id']);
            unset($event['goods_id']);
            unset($event['quiz_id']);
            unset($event['event_type']);

            $event['reward_id'] = $event['reward_id'] . "";
        }

        return $event_log;
    }

    public function getGoods($client_id, $site_id, $pb_player_id, $tags = null, $status = null)
    {
        $this->set_site_mongodb($site_id);
        $playerGoods = array();

        $this->mongo_db->where(array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'pb_player_id' => new MongoId($pb_player_id)

        ));

        $goods_data = $this->mongo_db->distinct('goods_id', 'playbasis_goods_log');

        foreach ($goods_data as $good){
            $this->mongo_db->select(array(
                'image',
                'name',
                'group',
                'description',
                'code',
                'custom_param',
                'date_expired_coupon',
                'tags'
            ));
            $this->mongo_db->select(array(), array('_id'));
            $this->mongo_db->where(array(
                'site_id' => $site_id,
                'goods_id' => $good,
            ));
            if($tags){
                $this->mongo_db->where_in('tags',$tags);
            }
            $this->mongo_db->limit(1);
            $goods_detail = $this->mongo_db->get('playbasis_goods_to_client');
            if(!$goods_detail){
                continue;
            }
            $goods_detail = $goods_detail[0];
            $this->mongo_db->select(array(
                'goods_id',
                'value',
                'date_expire',
                'gifted'
            ));
            $this->mongo_db->select(array(), array('_id'));
            $this->mongo_db->where(array(
                'pb_player_id' => $pb_player_id,
                'goods_id' => $good,
            ));
            $goods_player = $this->mongo_db->get('playbasis_goods_to_player');
            if ($goods_player) {
                $goods_player = $goods_player[0];
                if($goods_player['value'] > 0){
                    $goods_player['status'] = "active";
                }else{
                    if(isset($goods_player['gifted']) && $goods_player['gifted']){
                        $goods_player['status'] = "gifted";
                    }else{
                        $goods_player['status'] = "used";
                    }
                }
            } else {
                $goods_player = array();
                $goods_player['value'] = 0;
                $goods_player['status'] = "expired";
            }

            if ($status && ($status != $goods_player['status'])){
                continue;
            }
            $goods_player['goods_id'] = $good . "";
            $goods_player['image'] = $this->config->item('IMG_PATH') . $goods_detail['image'];
            $goods_player['name'] = $goods_detail['name'];
            $goods_player['description'] = $goods_detail['description'];
            $goods_player['code'] = $goods_detail['code'];
            $goods_player['custom_param'] = isset($goods_detail['custom_param']) ? $goods_detail['custom_param'] : array();
            $goods_player['tags'] = isset($goods_detail['tags']) && !empty($goods_detail['tags']) ? $goods_detail['tags'] : null;
            if(isset($goods_detail['group']) && $goods_detail['group']) {
                $goods_player['group'] = $goods_detail['group'];
                $this->mongo_db->where(array(
                    'client_id' => new MongoId($client_id),
                    'site_id' => new MongoId($site_id),
                    'pb_player_id' => new MongoId($pb_player_id),
                    'goods_id' => $good
                ));
                $this->mongo_db->limit(1);
                $goods_log_data = $this->mongo_db->get('playbasis_goods_log');
                $goods_player['date_expire'] = isset($goods_log_data[0]['date_expire']) ? datetimeMongotoReadable($goods_log_data[0]['date_expire']) : null;;

            }else{
                $goods_player['date_expire'] = isset($goods_detail['date_expired_coupon']) ? datetimeMongotoReadable($goods_detail['date_expired_coupon']) : null;
            }
            $goods_player['amount'] = $goods_player['value'];
            unset($goods_player['value']);
            array_push($playerGoods, $goods_player);
        }
        return $playerGoods;
    }

    public function setFavoriteGoods($client_id, $site_id, $pb_player_id, $goods_id, $status){

        $this->mongo_db->where(array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'pb_player_id' => new MongoId($pb_player_id),
            'goods_id' => new MongoId($goods_id)
        ));

        $this->mongo_db->limit(1);

        $this->mongo_db->set('status',(bool)$status);
        $result = $this->mongo_db->findAndModify('playbasis_goods_to_player_favorite', array('upsert' => true));

        return $result;
    }

    public function getFavoriteGoods($client_id, $site_id, $pb_player_id, $goods_id){

        $this->mongo_db->select(array(
            'status'
        ));

        $this->mongo_db->where(array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'pb_player_id' => new MongoId($pb_player_id),
            'goods_id' => new MongoId($goods_id)
        ));

        $this->mongo_db->limit(1);

        $result = $this->mongo_db->get('playbasis_goods_to_player_favorite');

        return $result ? $result[0]['status'] : false;
    }

    public function getGoodsCount($client_id, $site_id, $pb_player_id, $tags = null, $status = null)
    {
        $this->set_site_mongodb($site_id);

        $match_condition = array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'pb_player_id' => new MongoId($pb_player_id)
        );

        $query_array = array(
            array(
                '$match' => $match_condition
            ),
            array(
                '$group' => array('_id' => '$goods_id',
                    'date_expire' => array('$push' => '$date_expire'),
                    'current' => array('$sum' => 1))
            )
        );
        $results = $this->mongo_db->aggregate('playbasis_goods_log', $query_array);
        if (!$results) {
            return 0;
        }

        $playerGoods = array();
        foreach ($results["result"] as $goods) {
            if (isset($goods['_id'])) {
                //get goods data
                $this->mongo_db->select(array(
                    'image',
                    'name',
                    'description',
                    'code',
                    'group',
                    'tags'
                ));
                $this->mongo_db->select(array(), array('_id'));
                $this->mongo_db->where(array(
                    'goods_id' => $goods['_id'],
                    'site_id' => $site_id,
                ));
                if($tags){
                    $this->mongo_db->where_in('tags',$tags);
                }
                $this->mongo_db->limit(1);
                $result = $this->mongo_db->get('playbasis_goods_to_client');

                if (!$result) {
                    continue;
                }
                $result = $result[0];

                $this->mongo_db->select(array(
                    'goods_id',
                    'value',
                    'gifted',
                    'date_expire'
                ));
                $this->mongo_db->select(array(), array('_id'));
                $this->mongo_db->where(array(
                    'client_id' => new MongoId($client_id),
                    'site_id' => new MongoId($site_id),
                    'pb_player_id' => $pb_player_id,
                    'goods_id' => $goods['_id'],
                ));
                $goods_data = $this->mongo_db->get('playbasis_goods_to_player');
                if ($goods_data) {
                    $goods_data = $goods_data[0];
                    if(isset($goods_data['date_expire'])) $goods_data['date_expire'] = datetimeMongotoReadable($goods_data['date_expire']);
                    if($goods_data['value'] > 0){
                        $goods_data['status'] = "active";
                    }else{
                        if(isset($goods_data['gifted']) && $goods_data['gifted']){
                            $goods_data['status'] = "gifted";
                        }else{
                            $goods_data['status'] = "used";
                        }
                    }
                    $goods_data['date_expire'] = isset($goods_data['date_expire']) ? $goods_data['date_expire'] : null;
                } else {
                    $goods_data = array();
                    $goods_data['value'] = 0;
                    $goods_data['status'] = "expired";
                    $goods_data['date_expire'] = isset($goods['date_expire'][0]) ? datetimeMongotoReadable($goods['date_expire'][0]) : null;
                }
                if ($status && $status != $goods_data['status']){
                    continue;
                }
                $goods_data['goods_id'] = $goods['_id'] . "";
                $goods_data['image'] = $this->config->item('IMG_PATH') . $result['image'];
                $goods_data['name'] = $result['name'];
                $goods_data['description'] = $result['description'];
                $goods_data['code'] = $result['code'];
                $goods_data['tags'] = isset($result['tags']) && !empty($result['tags']) ? $result['tags'] : null;
                if (isset($result['group'])) {
                    $goods_data['group'] = $result['group'];
                }

                $goods_data['amount'] = $goods_data['value'];
                unset($goods_data['value']);
                array_push($playerGoods, $goods_data);
            }
        }
        return count($playerGoods);
    }

    public function getGoodsByGoodsId($pb_player_id, $site_id, $goods_id=false, $goods_list=false, $quantity=false)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'goods_id',
            'value'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where('pb_player_id', $pb_player_id);
        if($goods_id){
            $this->mongo_db->where('goods_id', $goods_id);
        }
        if($goods_list){
            $this->mongo_db->where_in('goods_id', $goods_list);
        }
        if($quantity) {
            $this->mongo_db->where_gt('value', 0);
        }
        $this->mongo_db->limit(1);
        $goods = $this->mongo_db->get('playbasis_goods_to_player');

        if (!$goods) {
            return array();
        }

        $goods = $goods[0];

        if (isset($goods['goods_id'])) {
            //get goods data
            $this->mongo_db->select(array(
                'image',
                'name',
                'description',
                'group'
            ));
            $this->mongo_db->select(array(), array('_id'));
            $this->mongo_db->where(array(
                'goods_id' => $goods['goods_id'],
                'site_id' => $site_id,
            ));
            $this->mongo_db->limit(1);
            $result = $this->mongo_db->get('playbasis_goods_to_client');

            if (!$result) {
                return array();
            }
            $result = $result[0];
            $goods['goods_id'] = $goods['goods_id'] . "";
            $goods['image'] = $this->config->item('IMG_PATH') . $result['image'];
            $goods['name'] = $result['name'];
            $goods['description'] = $result['description'];
            $goods['amount'] = $goods['value'];
            unset($goods['value']);
            $goods['group'] = $result['group'];
        }
        return $goods;
    }

    public function markUsedGoodsFromPlayer($client_id, $site_id, $pb_player_id, $goods_id)
    {
        $this->set_site_mongodb($site_id);

        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'goods_id' => $goods_id
        ));
        $this->mongo_db->set('value', 0);
        $this->mongo_db->unset_field("date_expire");
        $this->mongo_db->set('date_modified', new MongoDate());
        $result = $this->mongo_db->update('playbasis_goods_to_player');

        return $result;
    }

    public function deductNormalGoodsFromPlayer($client_id, $site_id, $pb_player_id, $goods_id, $amount)
    {
        $this->set_site_mongodb($site_id);

        $this->mongo_db->where(array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'pb_player_id' => new MongoId($pb_player_id),
            'goods_id' => new MongoId($goods_id),
        ));
        $player_value = $this->mongo_db->get('playbasis_goods_to_player');
        
        $this->mongo_db->where(array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'pb_player_id' => new MongoId($pb_player_id),
            'goods_id' => new MongoId($goods_id)
        ));
        $this->mongo_db->dec('value', $amount);
        if($player_value[0]['value'] == $amount) {
            $this->mongo_db->unset_field("date_expire");
        }
        $this->mongo_db->set('date_modified', new MongoDate());
        $result = $this->mongo_db->update('playbasis_goods_to_player');

        return $result;
    }

    private function getActionLogDetail($action_log_id)
    {
        $this->mongo_db->select(array('action_name', 'parameters', 'date_added'));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where('action_log_id', new MongoID($action_log_id));
        $returnThis = $this->mongo_db->get('playbasis_validated_action_log');
        return ($returnThis) ? $returnThis[0] : array();
    }

    public function new_registration1($data, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        $map = new MongoCode("function() { this.date_added.setTime(this.date_added.getTime()-(-7*60*60*1000)); emit(this.date_added.getFullYear()+'-'+('0'+(this.date_added.getMonth()+1)).slice(-2)+'-'+('0'+this.date_added.getDate()).slice(-2), 1); }");
        $reduce = new MongoCode("function(key, values) { return Array.sum(values); }");
        $query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id'], 'status' => true);
        if ($from || $to) {
            $query['date_added'] = array();
        }
        if ($from) {
            $query['date_added']['$gte'] = $this->new_mongo_date($from);
        }
        if ($to) {
            $query['date_added']['$lte'] = $this->new_mongo_date($to, '23:59:59');
        }
        $result = $this->mongo_db->command(array(
            'mapReduce' => 'playbasis_player',
            'map' => $map,
            'reduce' => $reduce,
            'query' => $query,
            'out' => array('inline' => 1),
        ));
        $result = $result ? $result['results'] : array();
        if ($from && (!isset($result[0]['_id']) || $result[0]['_id'] != $from)) {
            array_unshift($result, array('_id' => $from, 'value' => 0));
        }
        if ($to && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to)) {
            array_push($result, array('_id' => $to, 'value' => 0));
        }
        return $result;
    }

    public function new_registration($data, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        $action_id = $this->findAction(array_merge($data, array('action_name' => 'register')));
        if (!$action_id) {
            return array();
        }
        $match = array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'action_id' => $action_id,
        );
        if (($from || $to) && !isset($match['date_added'])) {
            $match['date_added'] = array();
        }
        if ($from) {
            $match['date_added']['$gte'] = new MongoDate(strtotime($from . ' 00:00:00'));
        }
        if ($to) {
            $match['date_added']['$lte'] = new MongoDate(strtotime($to . ' 23:59:59'));
        }
        $_result = $this->mongo_db->aggregate('playbasis_player_dau', array(
            array(
                '$match' => $match,
            ),
            array(
                '$group' => array('_id' => '$date_added', 'value' => array('$sum' => 1))
            ),
        ));
        $_result = $_result ? $_result['result'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => date('Y-m-d', $value['_id']->sec), 'value' => $value['value']));
            }
        }
        usort($result, 'cmp1');
        if ($from && (!isset($result[0]['_id']) || $result[0]['_id'] != $from)) {
            array_unshift($result, array('_id' => $from, 'value' => 0));
        }
        if ($to && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to)) {
            array_push($result, array('_id' => $to, 'value' => 0));
        }
        return $result;
    }

    public function findAction($data)
    {
        $this->set_site_mongodb($data['site_id']);
        $this->mongo_db->select(array('action_id'));
        $this->mongo_db->where(array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'name' => strtolower($data['action_name'])
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_action_to_client');
        return $result ? $result[0]['action_id'] : array();
    }

    /*public function daily_active_user_per_day($data, $from=null, $to=null) {
        return $this->active_user_per_day($data, 1, $from, $to);
    }*/

    public function daily_active_user_per_day($data, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        $match = array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
        );
        if (($from || $to) && !isset($match['date_added'])) {
            $match['date_added'] = array();
        }
        if ($from) {
            $match['date_added']['$gte'] = new MongoDate(strtotime($from . ' 00:00:00'));
        }
        if ($to) {
            $match['date_added']['$lte'] = new MongoDate(strtotime($to . ' 23:59:59'));
        }
        $_result = $this->mongo_db->aggregate('playbasis_player_dau', array(
            array(
                '$match' => $match,
            ),
            array(
                '$group' => array('_id' => '$date_added', 'value' => array('$sum' => '$count'))
            ),
        ));
        $_result = $_result ? $_result['result'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => date('Y-m-d', $value['_id']->sec), 'value' => $value['value']));
            }
        }
        usort($result, 'cmp1');
        if ($from && (!isset($result[0]['_id']) || $result[0]['_id'] != $from)) {
            array_unshift($result, array('_id' => $from, 'value' => 0));
        }
        if ($to && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to)) {
            array_push($result, array('_id' => $to, 'value' => 0));
        }
        return $result;
    }

    /*public function monthy_active_user_per_day($data, $from=null, $to=null) {
        return $this->active_user_per_day($data, 30, $from, $to);
    }*/

    public function monthy_active_user_per_day($data, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        $match = array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
        );
        if (($from || $to) && !isset($match['date_added'])) {
            $match['date_added'] = array();
        }
        if ($from) {
            $match['date_added']['$gte'] = new MongoDate(strtotime($from . ' 00:00:00'));
        }
        if ($to) {
            $match['date_added']['$lte'] = new MongoDate(strtotime($to . ' 23:59:59'));
        }
        $_result = $this->mongo_db->aggregate('playbasis_player_mau', array(
            array(
                '$match' => $match,
            ),
            array(
                '$group' => array('_id' => '$date_added', 'value' => array('$sum' => 1))
            ),
        ));
        $_result = $_result ? $_result['result'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => date('Y-m-d', $value['_id']->sec), 'value' => $value['value']));
            }
        }
        usort($result, 'cmp1');
        if ($from && (!isset($result[0]['_id']) || $result[0]['_id'] != $from)) {
            array_unshift($result, array('_id' => $from, 'value' => 0));
        }
        if ($to && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to)) {
            array_push($result, array('_id' => $to, 'value' => 0));
        }
        return $result;
    }

    /*public function monthy_active_user_per_week($data, $from=null, $to=null) {
        return $this->active_user_per_week($data, 30, $from, $to);
    }*/

    public function monthy_active_user_per_week($data, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        // http://stackoverflow.com/questions/15968465/mongo-map-reduce-error
        $map = new MongoCode("function() {
            this.date_added.setTime(this.date_added.getTime()-(-7*60*60*1000));
            var get_number_of_days = function(year, month) {
                var monthStart = new Date(year, month, 1);
                var monthEnd = new Date(year, month+1, 1);
                return (monthEnd-monthStart)/(1000*60*60*24);
            };
            var days = get_number_of_days(this.date_added.getFullYear(), this.date_added.getMonth());
            var week = Math.ceil(this.date_added.getDate()/7.0);
            if (week > 4) week = 4;
            var d = (week-1)*7+1;
            emit(this.date_added.getFullYear()+'-'+('0'+(this.date_added.getMonth()+1)).slice(-2)+'-'+('0'+d).slice(-2), {a: [this.pb_player_id.toString()]});
        }");
        $reduce = new MongoCode("function(key, values) {
            result = {a: []};
            check = {};
            values.forEach(function (v) {
                v.a.forEach(function (e) {
                    if (!(e in check)) {
                        result.a.push(e);
                        check[e] = true;
                    }
                })
            });
            return result;
        }");
        $match = array('client_id' => $data['client_id'], 'site_id' => $data['site_id']);
        if ($from || $to) {
            $match['date_added'] = array();
        }
        if ($from) {
            $match['date_added']['$gte'] = new MongoDate(strtotime($from . ' 00:00:00'));
        }
        if ($to) {
            $match['date_added']['$lte'] = new MongoDate(strtotime($to . ' 23:59:59'));
        }
        $_result = $this->mongo_db->command(array(
            'mapReduce' => 'playbasis_player_mau',
            'map' => $map,
            'reduce' => $reduce,
            'query' => $match,
            'out' => array('inline' => 1),
        ));
        $_result = $_result ? $_result['results'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => $value['_id'], 'value' => count($value['value']['a'])));
            }
        }
        usort($result, 'cmp1');
        $from2 = $from ? MY_Model::date_to_startdate_of_week($from) : null;
        $to2 = $to ? MY_Model::date_to_startdate_of_week($to) : null;
        if ($from2 && (!isset($result[0]['_id']) || $result[0]['_id'] != $from2)) {
            array_unshift($result, array('_id' => $from2, 'value' => 0));
        }
        if ($to2 && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to2)) {
            array_push($result, array('_id' => $to2, 'value' => 0));
        }
        return $result;
    }

    /*public function monthy_active_user_per_month($data, $from=null, $to=null) {
        return $this->active_user_per_month($data, 30, $from, $to);
    }*/

    public function monthy_active_user_per_month($data, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        // http://stackoverflow.com/questions/15968465/mongo-map-reduce-error
        $map = new MongoCode("function() {
            this.date_added.setTime(this.date_added.getTime()-(-7*60*60*1000));
            emit(this.date_added.getFullYear()+'-'+('0'+(this.date_added.getMonth()+1)).slice(-2), {a: [this.pb_player_id.toString()]});
        }");
        $reduce = new MongoCode("function(key, values) {
            result = {a: []};
            check = {};
            values.forEach(function (v) {
                v.a.forEach(function (e) {
                    if (!(e in check)) {
                        result.a.push(e);
                        check[e] = true;
                    }
                })
            });
            return result;
        }");
        $match = array('client_id' => $data['client_id'], 'site_id' => $data['site_id']);
        if ($from || $to) {
            $match['date_added'] = array();
        }
        if ($from) {
            $match['date_added']['$gte'] = new MongoDate(strtotime($from . ' 00:00:00'));
        }
        if ($to) {
            $match['date_added']['$lte'] = new MongoDate(strtotime($to . ' 23:59:59'));
        }
        $_result = $this->mongo_db->command(array(
            'mapReduce' => 'playbasis_player_mau',
            'map' => $map,
            'reduce' => $reduce,
            'query' => $match,
            'out' => array('inline' => 1),
        ));
        $_result = $_result ? $_result['results'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => $value['_id'], 'value' => count($value['value']['a'])));
            }
        }
        usort($result, 'cmp1');
        $from2 = $from ? MY_Model::get_year_month($from) : null;
        $to2 = $to ? MY_Model::get_year_month($to) : null;
        if ($from2 && (!isset($result[0]['_id']) || $result[0]['_id'] != $from2)) {
            array_unshift($result, array('_id' => $from2, 'value' => 0));
        }
        if ($to2 && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to2)) {
            array_push($result, array('_id' => $to2, 'value' => 0));
        }
        return $result;
    }

    private function active_user_per_day($data, $ndays, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        $str = $from ? explode('-', $from, 3) : "";
        $var_from = $from ? "var from = new Date(" . $str[0] . ", " . (intval($str[1]) - 1) . ", " . $str[2] . ", 0, 0, 0);" : "";
        $str = $to ? explode('-', $to, 3) : "";
        $var_to = $to ? "var to = new Date(" . $str[0] . ", " . (intval($str[1]) - 1) . ", " . $str[2] . ", 23, 59, 59);" : "";
        $check_from = $from ? "if (tmp.getTime() < from.getTime()) continue;" : "";
        $check_to = $to ? "if (tmp.getTime() > to.getTime()) break;" : "";
        // http://stackoverflow.com/questions/15968465/mongo-map-reduce-error
        $map = new MongoCode("function() {
            this.date_added.setTime(this.date_added.getTime()-(-7*60*60*1000));
            var tmp = new Date();
            $var_from
            $var_to
            for (var i = 0; i < " . $ndays . "; i++) {
                tmp.setTime(this.date_added.getTime()+i*86400000);
                $check_from
                $check_to
                emit(tmp.getFullYear()+'-'+('0'+(tmp.getMonth()+1)).slice(-2)+'-'+('0'+tmp.getDate()).slice(-2), {a: [this.pb_player_id.toString()]});
            }
        }");
        $reduce = new MongoCode("function(key, values) {
            result = {a: []};
            check = {};
            values.forEach(function (v) {
                v.a.forEach(function (e) {
                    if (!(e in check)) {
                        result.a.push(e);
                        check[e] = true;
                    }
                })
            });
            return result;
        }");
        $query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id']);
        if ($from || $to) {
            $query['date_added'] = array();
        }
        if ($from) {
            $query['date_added']['$gte'] = $this->new_mongo_date($ndays > 1 ? date('Y-m-d',
                strtotime('-' . $ndays . ' day', strtotime($from))) : $from);
        }
        if ($to) {
            $query['date_added']['$lte'] = $this->new_mongo_date($to, '23:59:59');
        }
        $_result = $this->mongo_db->command(array(
            'mapReduce' => 'playbasis_action_log',
            'map' => $map,
            'reduce' => $reduce,
            'query' => $query,
            'out' => array('inline' => 1),
        ));
        $_result = $_result ? $_result['results'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => $value['_id'], 'value' => count($value['value']['a'])));
            }
        }
        usort($result, 'cmp1');
        if ($from && (!isset($result[0]['_id']) || $result[0]['_id'] != $from)) {
            array_unshift($result, array('_id' => $from, 'value' => 0));
        }
        if ($to && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to)) {
            array_push($result, array('_id' => $to, 'value' => 0));
        }
        return $result;
    }

    private function active_user_per_week($data, $ndays, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        $str = $from ? explode('-', $from, 3) : "";
        $var_from = $from ? "var from = new Date(" . $str[0] . ", " . (intval($str[1]) - 1) . ", " . $str[2] . ", 0, 0, 0);" : "";
        $str = $to ? explode('-', $to, 3) : "";
        $var_to = $to ? "var to = new Date(" . $str[0] . ", " . (intval($str[1]) - 1) . ", " . $str[2] . ", 23, 59, 59);" : "";
        $check_from = $from ? "if (tmp.getTime() < from.getTime()) continue;" : "";
        $check_to = $to ? "if (tmp.getTime() > to.getTime()) break;" : "";
        // http://stackoverflow.com/questions/15968465/mongo-map-reduce-error
        $map = new MongoCode("function() {
            this.date_added.setTime(this.date_added.getTime()-(-7*60*60*1000));
            var get_number_of_days = function(year, month) {
                var monthStart = new Date(year, month, 1);
                var monthEnd = new Date(year, month+1, 1);
                return (monthEnd-monthStart)/(1000*60*60*24);
            };
            var days,days_per_week,week,d;
            var tmp = new Date();
            $var_from
            $var_to
            for (var i = 0; i < " . $ndays . "; i++) {
                tmp.setTime(this.date_added.getTime()+i*86400000);
                $check_from
                $check_to
                days = get_number_of_days(tmp.getFullYear(), tmp.getMonth());
                week = Math.ceil(tmp.getDate()/7.0);
                if (week > 4) week = 4;
                d = (week-1)*7+1;
                emit(tmp.getFullYear()+'-'+('0'+(tmp.getMonth()+1)).slice(-2)+'-'+('0'+d).slice(-2), {a: [this.pb_player_id.toString()]});
            }
        }");
        $reduce = new MongoCode("function(key, values) {
            result = {a: []};
            check = {};
            values.forEach(function (v) {
                v.a.forEach(function (e) {
                    if (!(e in check)) {
                        result.a.push(e);
                        check[e] = true;
                    }
                })
            });
            return result;
        }");
        $query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id']);
        if ($from || $to) {
            $query['date_added'] = array();
        }
        if ($from) {
            $query['date_added']['$gte'] = $this->new_mongo_date($ndays > 1 ? date('Y-m-d',
                strtotime('-' . $ndays . ' day', strtotime($from))) : $from);
        }
        if ($to) {
            $query['date_added']['$lte'] = $this->new_mongo_date($to, '23:59:59');
        }
        $_result = $this->mongo_db->command(array(
            'mapReduce' => 'playbasis_action_log',
            'map' => $map,
            'reduce' => $reduce,
            'query' => $query,
            'out' => array('inline' => 1),
        ));
        $_result = $_result ? $_result['results'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => $value['_id'], 'value' => count($value['value']['a'])));
            }
        }
        usort($result, 'cmp1');
        $from2 = $from ? MY_Model::date_to_startdate_of_week($from) : null;
        $to2 = $to ? MY_Model::date_to_startdate_of_week($to) : null;
        if ($from2 && (!isset($result[0]['_id']) || $result[0]['_id'] != $from2)) {
            array_unshift($result, array('_id' => $from2, 'value' => 0));
        }
        if ($to2 && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to2)) {
            array_push($result, array('_id' => $to2, 'value' => 0));
        }
        return $result;
    }

    private function active_user_per_month($data, $ndays, $from = null, $to = null)
    {
        $this->set_site_mongodb($data['site_id']);
        $str = $from ? explode('-', $from, 3) : "";
        $var_from = $from ? "var from = new Date(" . $str[0] . ", " . (intval($str[1]) - 1) . ", " . $str[2] . ", 0, 0, 0);" : "";
        $str = $to ? explode('-', $to, 3) : "";
        $var_to = $to ? "var to = new Date(" . $str[0] . ", " . (intval($str[1]) - 1) . ", " . $str[2] . ", 23, 59, 59);" : "";
        $check_from = $from ? "if (tmp.getTime() < from.getTime()) continue;" : "";
        $check_to = $to ? "if (tmp.getTime() > to.getTime()) break;" : "";
        // http://stackoverflow.com/questions/15968465/mongo-map-reduce-error
        $map = new MongoCode("function() {
            this.date_added.setTime(this.date_added.getTime()-(-7*60*60*1000));
            var tmp = new Date();
            $var_from
            $var_to
            for (var i = 0; i < " . $ndays . "; i++) {
                tmp.setTime(this.date_added.getTime()+i*86400000);
                $check_from
                $check_to
                emit(tmp.getFullYear()+'-'+('0'+(tmp.getMonth()+1)).slice(-2), {a: [this.pb_player_id.toString()]});
            }
        }");
        $reduce = new MongoCode("function(key, values) {
            result = {a: []};
            check = {};
            values.forEach(function (v) {
                v.a.forEach(function (e) {
                    if (!(e in check)) {
                        result.a.push(e);
                        check[e] = true;
                    }
                })
            });
            return result;
        }");
        $query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id']);
        if ($from || $to) {
            $query['date_added'] = array();
        }
        if ($from) {
            $query['date_added']['$gte'] = $this->new_mongo_date($ndays > 1 ? date('Y-m-d',
                strtotime('-' . $ndays . ' day', strtotime($from))) : $from);
        }
        if ($to) {
            $query['date_added']['$lte'] = $this->new_mongo_date($to, '23:59:59');
        }
        $_result = $this->mongo_db->command(array(
            'mapReduce' => 'playbasis_action_log',
            'map' => $map,
            'reduce' => $reduce,
            'query' => $query,
            'out' => array('inline' => 1),
        ));
        $_result = $_result ? $_result['results'] : array();
        $result = array();
        if (is_array($_result)) {
            foreach ($_result as $key => $value) {
                array_push($result, array('_id' => $value['_id'], 'value' => count($value['value']['a'])));
            }
        }
        usort($result, 'cmp1');
        $from2 = $from ? MY_Model::get_year_month($from) : null;
        $to2 = $to ? MY_Model::get_year_month($to) : null;
        if ($from2 && (!isset($result[0]['_id']) || $result[0]['_id'] != $from2)) {
            array_unshift($result, array('_id' => $from2, 'value' => 0));
        }
        if ($to2 && (!isset($result[count($result) - 1]['_id']) || $result[count($result) - 1]['_id'] != $to2)) {
            array_push($result, array('_id' => $to2, 'value' => 0));
        }
        return $result;
    }
    public function getGoodsClient($data, $is_sponsor = false)
    {
        //get goods id
        $this->set_site_mongodb($data['site_id']);
        // $this->mongo_db->select(array('goods_id','image','name','description','quantity','redeem','date_start','date_expire','sponsor'));
        $this->mongo_db->select(array(
            'goods_id',
            'image',
            'name',
            'description',
            'quantity',
            'per_user',
            'redeem',
            'date_start',
            'date_expire',
            'days_expire',
            'date_expired_coupon',
            'sponsor',
            'sort_order',
            'group',
            'code',
            'tags',
            'organize_id',
            'organize_role'
        ));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'client_id' => $is_sponsor ? null : $data['client_id'],
            'site_id' => $is_sponsor ? null : $data['site_id'],
            'goods_id' => $data['goods_id'],
            'deleted' => false
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_goods_to_client');

        if (isset($result[0]['redeem'])) {
            if (isset($result[0]['redeem']['badge'])) {
                $redeem = array();
                foreach ($result[0]['redeem']['badge'] as $k => $v) {
                    $redeem_inside = array();
                    $redeem_inside["badge_id"] = $k;
                    $redeem_inside["badge_value"] = $v;
                    $redeem[] = $redeem_inside;
                }
                $result[0]['redeem']['badge'] = $redeem;
            }
            if (isset($result[0]['redeem']['custom'])) {
                $redeem = array();
                foreach ($result[0]['redeem']['custom'] as $k => $v) {
                    $this->mongo_db->select(array('name'));
                    $this->mongo_db->select(array(), array('_id'));
                    $this->mongo_db->where(array(
                        'client_id' => $data['client_id'],
                        'site_id' => $data['site_id'],
                        'reward_id' => new MongoId($k),
                    ));
                    $this->mongo_db->limit(1);
                    $custom = $this->mongo_db->get('playbasis_reward_to_client');
                    if (isset($custom[0]['name'])) {
                        $redeem_inside = array();
                        $redeem_inside["custom_id"] = $k;
                        $redeem_inside["custom_name"] = $custom[0]['name'];
                        $redeem_inside["custom_value"] = $v;
                        $redeem[] = $redeem_inside;
                    }
                }
                $result[0]['redeem']['custom'] = $redeem;
            }
        }
        if (isset($result[0]['goods_id'])) {
            $result[0]['goods_id'] = $result[0]['goods_id'] . "";
        }
        if (isset($result[0]['date_start'])) {
            $result[0]['date_start'] = datetimeMongotoReadable($result[0]['date_start']);
        }
        if (isset($result[0]['date_expire'])) {
            $result[0]['date_expire'] = datetimeMongotoReadable($result[0]['date_expire']);
        }
        if (isset($result[0]['date_expired_coupon'])) {
            $result[0]['date_expired_coupon'] = datetimeMongotoReadable($result[0]['date_expired_coupon']);
        }
        if (isset($result[0]['image'])) {
            $result[0]['image'] = $this->config->item('IMG_PATH') . $result[0]['image'];
        }
        return $result ? $result[0] : array();
    }

    public function giveGift($client_id, $site_id, $sent_pb_player_id, $received_pb_player_id, $received_player_id, $gift_id, $gift_type, $value, $gift_data){

        $this->set_site_mongodb($site_id);
        
        //update sent player
        $this->mongo_db->where('client_id' , $client_id);
        $this->mongo_db->where('site_id' , $site_id);
        $this->mongo_db->where('pb_player_id' , $sent_pb_player_id);
        $this->mongo_db->set('date_modified', new MongoDate());
        $this->mongo_db->dec('value', intval($value));

        if($gift_type == "BADGE"){
            $this->mongo_db->where('badge_id', $gift_id);
            $sent_rewardInfo = $this->mongo_db->update('playbasis_reward_to_player');
        } elseif ($gift_type == "CUSTOM_POINT") {
            $this->mongo_db->where('reward_id', $gift_id);
            $sent_rewardInfo = $this->mongo_db->update('playbasis_reward_to_player');
        } elseif ($gift_type == "GOODS") {
            $this->mongo_db->where('goods_id', $gift_id);
            if($gift_data['before']['value'] == intval($value)) {
                $this->mongo_db->set('gifted', true);
                $this->mongo_db->unset_field("date_expire");
            }
            $sent_rewardInfo = $this->mongo_db->update('playbasis_goods_to_player');
        }
        
        if(!$sent_rewardInfo){
            return;
        }

        $rewardInfo = array();
        $this->mongo_db->where('pb_player_id', $received_pb_player_id);
        $this->mongo_db->limit(1);

        if($gift_type == "BADGE"){
            $this->mongo_db->where('badge_id', $gift_id);
            $rewardInfo = $this->mongo_db->get('playbasis_reward_to_player');
        } elseif ($gift_type == "CUSTOM_POINT") {
            $this->mongo_db->where('reward_id', $gift_id);
            $rewardInfo = $this->mongo_db->get('playbasis_reward_to_player');
        } elseif ($gift_type == "GOODS") {
            $this->mongo_db->where('goods_id', $gift_id);
            $rewardInfo = $this->mongo_db->get('playbasis_goods_to_player');
        }

        if ($rewardInfo) {
            $this->mongo_db->where('pb_player_id', $received_pb_player_id);
            $this->mongo_db->set('date_modified', new MongoDate());
            $this->mongo_db->inc('value', intval($value));
            if($gift_type == "BADGE"){
                $this->mongo_db->where('badge_id', $gift_id);
                $receive_rewardInfo = $this->mongo_db->update('playbasis_reward_to_player');
            } elseif ($gift_type == "CUSTOM_POINT") {
                $this->mongo_db->where('reward_id', $gift_id);
                $receive_rewardInfo = $this->mongo_db->update('playbasis_reward_to_player');
            } elseif ($gift_type == "GOODS") {
                $this->mongo_db->where('goods_id', $gift_id);
                $this->mongo_db->set('gifted', false);
                if(isset($gift_data['before']['date_expire'])) $this->mongo_db->set('date_expire', $gift_data['before']['date_expire']);
                $receive_rewardInfo = $this->mongo_db->update('playbasis_goods_to_player');
            }
        } else {
            $data = array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'pb_player_id' => $received_pb_player_id,
                'cl_player_id' => $received_player_id,
                'date_added' => new MongoDate(),
                'date_modified' => new MongoDate()
            );
            $data['value'] = intval($value);
            if($gift_type == "BADGE"){
                $data['badge_id'] = $gift_id;
                $data['redeemed'] = 0;
                $receive_rewardInfo = $this->mongo_db->insert('playbasis_reward_to_player',$data);
            } elseif ($gift_type == "CUSTOM_POINT") {
                $data['reward_id'] = $gift_id;
                $receive_rewardInfo = $this->mongo_db->insert('playbasis_reward_to_player',$data);
            } elseif ($gift_type == "GOODS") {
                $data['goods_id'] = $gift_id;
                $data['is_sponsor'] = $gift_data['gift']['sponsor'];
                $data['gifted'] = false;
                if(isset($gift_data['gift']['group'])) $data['group'] = $gift_data['gift']['group'];
                if(isset($gift_data['before']['date_expire'])) $data['date_expire'] = $gift_data['before']['date_expire'];
                $receive_rewardInfo = $this->mongo_db->insert('playbasis_goods_to_player', $data);
            }
        }
        return $receive_rewardInfo;
    }
    public function checkPlayerWithEnoughGoods($client_id, $site_id, $pb_player_id, $goods_id, $n)
    {
        $this->set_site_mongodb($site_id);
        $query = array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'goods_id' => new MongoId($goods_id),
            'pb_player_id' => new MongoId($pb_player_id),
            'value' => array('$gte' => intval($n))
        );

        $this->mongo_db->where($query);
        $result = $this->mongo_db->get('playbasis_goods_to_player');
        return $result ? $result[0] : array();
    }

    public function checkPlayerWithEnoughPoint($client_id, $site_id, $pb_player_id, $reward_id, $n)
    {
        $this->set_site_mongodb($site_id);
        $query = array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'reward_id' => new MongoId($reward_id),
            'pb_player_id' => new MongoId($pb_player_id),
            'value' => array('$gte' => intval($n))
        );

        $this->mongo_db->where($query);
        $result = $this->mongo_db->get('playbasis_reward_to_player');
        return $result ? $result[0] : array();
    }

    public function checkPlayerWithEnoughBadge($client_id, $site_id, $pb_player_id, $badge_id, $n)
    {
        $this->set_site_mongodb($site_id);
        $query = array(
            'client_id' => new MongoId($client_id),
            'site_id' => new MongoId($site_id),
            'badge_id' => new MongoId($badge_id),
            'pb_player_id' => new MongoId($pb_player_id),
            'value' => array('$gte' => intval($n))
        );
        
        $this->mongo_db->where($query);
        $result = $this->mongo_db->get('playbasis_reward_to_player');
        return $result ? $result[0] : array();
    }

    public function get_reward_id_by_name($data, $name)
    {
        $this->set_site_mongodb($data['site_id']);
        $query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id'], 'name' => $name);
        $this->mongo_db->select(array('reward_id'));
        $this->mongo_db->where($query);
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('playbasis_reward_to_client');
        return $results ? $results[0]['reward_id'] : null;
    }

    public function getActiveQuests($site_id, $fields)
    {
        $this->set_site_mongodb($site_id);
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'status' => true,
        ));
        $this->mongo_db->where_ne('deleted', true);
        return $this->mongo_db->get('playbasis_quest_to_client');
    }

    public function getAllQuests($pb_player_id, $site_id, $status = "")
    {
        $this->set_site_mongodb($site_id);

        $quests = $this->getActiveQuests($site_id, array('_id'));
        $in = array_map('index_id', $quests);

        $this->mongo_db->where(array(
            'pb_player_id' => $pb_player_id,
            'site_id' => $site_id,
        ));
        $this->mongo_db->where_in('quest_id', $in);
        $this->mongo_db->where_ne('deleted', true);
        $c_status = array("join", "unjoin", "finish");
        if ($status != '' && in_array($status, $c_status)) {
            $this->mongo_db->where(array(
                'status' => $status,
            ));
        }

        return $this->mongo_db->get('playbasis_quest_to_player');
    }

    /*
     * Get all quests from _id
     * @param string $quest_id
     * @return array
     */
    public function getQuestsByID($site_id, $quest_id)
    {
        $this->set_site_mongodb($site_id);

        try {
            $quest_id = new MongoID($quest_id);
        } catch (MongoException $e) {
            return array();
        }

        $this->mongo_db->where(array(
            '_id' => $quest_id,
        ));

        $results = $this->mongo_db->get('playbasis_quest_to_client');
        if ($results) {
            for ($i = 0; $i < sizeof($results); ++$i) {
                $results[$i]["quest_id"] = $results[$i]["_id"];
            }
        } else {
            $results = array();
        }

        return $results;
    }

    public function getMission($pb_player_id, $quest_id, $mission_id, $site_id)
    {
        $this->set_site_mongodb($site_id);

        $this->mongo_db->select(array('missions.$'));
        $this->mongo_db->where(array(
            'pb_player_id' => $pb_player_id,
            'site_id' => $site_id,
            'quest_id' => $quest_id,
            'missions.mission_id' => $mission_id
        ));
        $this->mongo_db->where_ne('deleted', true);
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_quest_to_player');
        return $result ? $result[0] : array();
    }

    public function getResetRewardEvent($site_id, $reward_id = null)
    {
        $this->set_site_mongodb($site_id);

        $this->mongo_db->select(array('reward_id', 'date_added'));
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('event_type', 'RESET');
        if ($reward_id) {
            $this->mongo_db->where('reward_id', $reward_id);
            $this->mongo_db->limit(1);
        }
        $this->mongo_db->order_by(array('date_added' => 'DESC')); // use 'date_added' instead of '_id'
        $results = $this->mongo_db->get('playbasis_event_log');
        $ret = array();
        if ($results) {
            foreach ($results as $result) {
                $reward_id = $result['reward_id']->{'$id'};
                if (array_key_exists($reward_id, $ret)) {
                    continue;
                }
                $ret[$reward_id] = $result['date_added'];
            }
        }

        return $ret;
    }

    public function getById($site_id, $pb_player_id)
    {
        $this->set_site_mongodb($site_id);

        $this->mongo_db->where('_id', $pb_player_id);
        $results = $this->mongo_db->get('playbasis_player');
        return $results ? $results[0] : array();
    }

    public function getEmail($site_id, $pb_player_id)
    {
        $player = $this->getById($site_id, $pb_player_id);
        return $player && isset($player['email']) ? $player['email'] : null;
    }

    public function getPhone($site_id, $pb_player_id)
    {
        $player = $this->getById($site_id, $pb_player_id);
        return $player && isset($player['phone_number']) ? $player['phone_number'] : null;
    }

    public function findPlayerByCode($site_id, $code, $fields)
    {
        $this->set_site_mongodb($site_id);
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('code', $code);
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('playbasis_player');
        return $results ? $results[0] : array();
    }

    public function insertOrUpdateFullContact($email, $detail)
    {
        if ($detail && isset($detail['result'])) {
            $mongoDate = new MongoDate(time());
            $this->mongo_db->where('_id', $email);
            $records = $this->mongo_db->get('playbasis_player_fc');
            if (!$records) {
                $this->mongo_db->insert('playbasis_player_fc',
                    array_merge(array('_id' => $email, 'date_added' => $mongoDate, 'date_modified' => $mongoDate),
                        $detail['result']));
            } else {
                if (isset($detail['result']['status']) && $detail['result']['status'] != 200) {
                    return;
                }
                $r = $records[0];
                $this->mongo_db->where('_id', $email);
                $this->mongo_db->delete('playbasis_player_fc');
                $this->mongo_db->insert('playbasis_player_fc',
                    array_merge(array('_id' => $email, 'date_added' => $r['date_added'], 'date_modified' => $mongoDate),
                        $detail['result']));
            }
        }
    }

    public function login($client_id, $site_id, $pb_player_id, $session_id, $session_expires_in)
    {
        $this->set_site_mongodb($site_id);
        $mongoDate = new MongoDate(time());
        $date_expire = null;
        if ($session_expires_in) {
            $session_expires_in = intval($session_expires_in);
            $date_expire = new MongoDate(time() + $session_expires_in);
        }
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('session_id', $session_id);
        $c = $this->mongo_db->count('playbasis_player_session');
        if (!$c) {
            $this->mongo_db->insert('playbasis_player_session', array(
                'client_id' => $client_id,
                'site_id' => $site_id,
                'session_id' => $session_id,
                'pb_player_id' => $pb_player_id,
                'date_added' => $mongoDate,
                'date_modified' => $mongoDate,
                'date_expire' => $date_expire
            ));
        } else {
            $this->mongo_db->where('site_id', $site_id);
            $this->mongo_db->where('session_id', $session_id);
            $this->mongo_db->set('date_expire', $date_expire);
            $this->mongo_db->set('date_modified', $mongoDate);
            $this->mongo_db->update('playbasis_player_session');
        }
    }

    public function logout($client_id, $site_id, $session_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('session_id', $session_id);
        return $this->mongo_db->delete('playbasis_player_session');
    }

    public function listSessions($client_id, $site_id, $pb_player_id)
    {
        $this->set_site_mongodb($site_id);
        $mongoDate = new MongoDate(time());
        $this->mongo_db->select(array('session_id', 'date_expire'));
        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $reset_where = array(
            array('date_expire' => array('$gt' => $mongoDate)),
            array('date_expire' => null)
        );
        $this->mongo_db->where(array('$or' => $reset_where));
        $sessions = $this->mongo_db->get('playbasis_player_session');
        if ($sessions) {
            foreach ($sessions as &$session) {
                if ($session['date_expire']) {
                    $session['date_expire'] = datetimeMongotoReadable($session['date_expire']);
                }
            }
        }
        return $sessions;
    }

    public function findBySessionId($client_id, $site_id, $session_id, $active_only = true)
    {
        $this->set_site_mongodb($site_id);
        $mongoDate = new MongoDate(time());
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('session_id', $session_id);
        if ($active_only) {
            $reset_where = array(
                array('date_expire' => array('$gt' => $mongoDate)),
                array('date_expire' => null)
            );
            $this->mongo_db->where(array('$or' => $reset_where));
        }
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('playbasis_player_session');
        return $results ? $results[0] : null;
    }

    public function isAnonymous($client_id, $site_id, $cl_player_id = null, $pb_player_id = null)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('anonymous'));
        if ($cl_player_id != null) {
            $this->mongo_db->where('client_id', $client_id);
            $this->mongo_db->where('site_id', $site_id);
            $this->mongo_db->where('cl_player_id', $cl_player_id);
        }
        if ($pb_player_id != null) {
            $this->mongo_db->where('_id', $pb_player_id);
        }
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('playbasis_player');
        if ($results) {
            $result = $results[0];
            $anonymous = isset($result['anonymous']) ? $result['anonymous'] : false;
            return $anonymous;
        } else {
            return false;
        }
    }

    public function getPlayerByPlayerId($site_id, $player_id, $fields = null)
    {
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('cl_player_id', $player_id);
        $results = $this->mongo_db->get('playbasis_player');
        return $results ? $results[0] : array();
    }

    public function getPlayerByUsername($site_id, $username)
    {
        $this->mongo_db->select(array(
            '_id',
            'cl_player_id',
            'device_id',
            'phone_number',
            'approve_status',
            'login_attempt',
            'locked',
            'email_verify'
        ));
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('username', $username);
        $results = $this->mongo_db->get('playbasis_player');
        return $results ? $results[0] : array();
    }

    public function getPlayerByEmail($site_id, $email)
    {
        $this->mongo_db->select(array(
            '_id',
            'cl_player_id',
            'first_name',
            'last_name',
            'username',
            'device_id',
            'phone_number',
            'approve_status',
            'login_attempt',
            'locked',
            'email_verify'
        ));
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('email', $email);
        $results = $this->mongo_db->get('playbasis_player');
        return $results ? $results[0] : array();
    }

    public function getPlayerByPlayer($site_id, $pb_player_id ,$select=null)
    {
        if($select) $this->mongo_db->select($select);
        $this->mongo_db->where('site_id', new MongoId($site_id));
        $this->mongo_db->where('_id', new MongoId($pb_player_id));
        $results = $this->mongo_db->get('playbasis_player');
        if($results){
            unset($results[0]['_id']);
        }
        return $results ? $results[0] : array();
    }

    public function getPlayerByUsernameButNotID($site_id, $username, $pb_player_id)
    {
        $this->mongo_db->select(array('_id', 'cl_player_id'));
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where_ne('_id', $pb_player_id);
        $this->mongo_db->where('username', $username);
        $results = $this->mongo_db->get('playbasis_player');
        return $results ? $results[0] : array();
    }

    public function getPlayerByEmailButNotID($site_id, $email, $pb_player_id)
    {
        $this->mongo_db->select(array('_id', 'cl_player_id'));
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where_ne('_id', $pb_player_id);
        $this->mongo_db->where('email', $email);
        $results = $this->mongo_db->get('playbasis_player');
        return $results ? $results[0] : array();
    }

    public function authPlayer($site_id, $player_id, $password)
    {
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('_id', $player_id);
        $this->mongo_db->where('password', $password);
        $results = $this->mongo_db->count('playbasis_player');
        return $results ? true : false;
    }

    public function existsCode($code)
    {
        $this->mongo_db->where('code', $code);
        $this->mongo_db->limit(1);
        return $this->mongo_db->count('playbasis_player') > 0;
    }

    public function generateCode($pb_player_id)
    {
        $code = null;
        $length = defined('REFERRAL_CODE_LENGTH') ? REFERRAL_CODE_LENGTH : 8;

        for ($i = 0; $i < 2; $i++) {
            $code = get_random_password($length, $length, true, true);
            if (!$this->existsCode($code)) {
                break;
            }
        }
        if (!$code) {
            throw new Exception('Cannot generate unique player code');
        }
        $this->mongo_db->where('_id', $pb_player_id);
        $this->mongo_db->set('code', $code);
        $this->mongo_db->update('playbasis_player');
        return $code;
    }

    public function existsPasswordResetCode($code)
    {
        $this->mongo_db->where('code', $code);
        $this->mongo_db->limit(1);
        return $this->mongo_db->count('playbasis_player_password_reset') > 0;
    }

    public function generatePasswordResetCode($pb_player_id)
    {
        $code = $this->genCode(8, false, true, true);

        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $records = $this->mongo_db->get('playbasis_player_password_reset');
        if (!$records) {
            $this->mongo_db->insert('playbasis_player_password_reset', array(
                'pb_player_id' => $pb_player_id,
                'code' => $code,
                'date_expire' => new MongoDate(strtotime("+1 day")),
            ));
        } else {
            $this->mongo_db->where('pb_player_id', $pb_player_id);
            $this->mongo_db->set('code', $code);
            $this->mongo_db->set('date_expire', new MongoDate(strtotime("+1 day")));
            $this->mongo_db->update('playbasis_player_password_reset');
        }
        return $code;
    }

    public function generateEmailVerifyCode($pb_player_id)
    {
        $code = $this->genCode(8, false, true, true);

        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $records = $this->mongo_db->get('playbasis_player_email_verify');
        if (!$records) {
            $this->mongo_db->insert('playbasis_player_email_verify', array(
                'pb_player_id' => $pb_player_id,
                'code' => $code,
                'date_expire' => new MongoDate(strtotime("+1 day")),
            ));
        } else {
            $this->mongo_db->where('pb_player_id', $pb_player_id);
            $this->mongo_db->set('code', $code);
            $this->mongo_db->set('date_expire', new MongoDate(strtotime("+1 day")));
            $this->mongo_db->update('playbasis_player_email_verify');
        }
        return $code;
    }

    public function existsOTPCode($code)
    {
        $this->mongo_db->where('code', $code);
        $this->mongo_db->limit(1);
        return $this->mongo_db->count('playbasis_player_otp_to_player') > 0;
    }

    private function genCode($length, $use_lower_case, $use_upper_case,  $use_numbers ){
        $code = null;
        for ($i = 0; $i < 2; $i++) {
            $random_code = get_random_code($length, $use_lower_case, $use_upper_case, $use_numbers);
            if (!$this->existsOTPCode($random_code)) {
                $code = $random_code;
                break;
            }
        }
        if (!$code) {
            throw new Exception('Cannot generate unique player code');
        }
        return $code;
    }

    public function generateOTPCode($pb_player_id, $deviceInfo)
    {
        $code = $this->genCode(SMS_VERIFICATION_CODE_LENGTH, false, false, true);

        $this->mongo_db->where('pb_player_id', $pb_player_id);
        $records = $this->mongo_db->get('playbasis_player_otp_to_player');
        if (!$records) {
            $this->mongo_db->insert('playbasis_player_otp_to_player', array(
                'pb_player_id' => $pb_player_id,
                'code' => $code,
                'phone_number'=>$deviceInfo['phone_number'],
                'device_token'=>$deviceInfo['device_token'],
                'device_description'=>$deviceInfo['device_description'],
                'device_name'=>$deviceInfo['device_name'],
                'os_type'=>$deviceInfo['os_type'],
                'date_expire' => new MongoDate(time() + SMS_VERIFICATION_TIMEOUT_IN_SECONDS),
            ));
        } else {
            $this->mongo_db->where('pb_player_id', $pb_player_id);
            $this->mongo_db->set('code', $code);
            $this->mongo_db->set('phone_number', $deviceInfo['phone_number']);
            $this->mongo_db->set('device_token', $deviceInfo['device_token']);
            $this->mongo_db->set('device_description', $deviceInfo['device_description']);
            $this->mongo_db->set('device_name', $deviceInfo['device_name']);
            $this->mongo_db->set('os_type', $deviceInfo['os_type']);
            $this->mongo_db->set('date_expire', new MongoDate(time() + SMS_VERIFICATION_TIMEOUT_IN_SECONDS));
            $this->mongo_db->update('playbasis_player_otp_to_player');
        }
        return $code;
    }

    public function getPlayerOTPCode($pb_player_id, $code)
    {
        $this->mongo_db->where('pb_player_id', new MongoId($pb_player_id));
        $this->mongo_db->where('code', $code);
//        Move to check on controller code
//        $this->mongo_db->where('date_expire', array('$gt' => new MongoDate()));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_player_otp_to_player');

        return ($result) ? $result[0] : false;
    }

    public function deleteOTPCode($code)
    {
        $this->mongo_db->where('code', $code);
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->delete('playbasis_player_otp_to_player');
        return $result;
    }

    public function storeDeviceToken($data)
    {
        $this->mongo_db->select(null);
        $this->mongo_db->where(array(
            'client_id' => new MongoId($data['client_id']),
            'site_id' => new MongoId($data['site_id']),
            'pb_player_id' => new MongoId($data['pb_player_id']),
            'device_token' => $data['device_token']
        ));
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('playbasis_player_device');
        if (!$results) {
            $result = $this->mongo_db->insert('playbasis_player_device', array(
                'client_id' => new MongoId($data['client_id']),
                'site_id' => new MongoId($data['site_id']),
                'pb_player_id' => new MongoId($data['pb_player_id']),
                'device_token' => $data['device_token'],
                'device_description' => $data['device_description'],
                'device_name' => $data['device_name'],
                'os_type' => $data['os_type'],
                'status' => true,
                'date_added' => new MongoDate(),
                'date_modified' => new MongoDate(),
            ));
        } else {
            $this->mongo_db->where('client_id', new MongoId($data['client_id']));
            $this->mongo_db->where('site_id', new MongoId($data['site_id']));
            $this->mongo_db->where('pb_player_id', new MongoId($data['pb_player_id']));
            $this->mongo_db->where('device_token', $data['device_token']);

            $this->mongo_db->set('device_description', $data['device_description']);
            $this->mongo_db->set('device_name', $data['device_name']);
            $this->mongo_db->set('os_type', $data['os_type']);
            $this->mongo_db->set('status', true);
            $this->mongo_db->set('date_modified', new MongoDate());

            $result = $this->mongo_db->update("playbasis_player_device");
        }
        return $result ? true : false;
    }

    public function listDevices($client_id, $site_id, $pb_player_id, $fields = null)
    {
        if ($fields) {
            $this->mongo_db->select($fields);
        }
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
        ));
        $this->mongo_db->where_ne('deleted', true);
        return $this->mongo_db->get('playbasis_player_device');
    }

    public function deRegisterDevices($client_id, $site_id, $pb_player_id = null,$device_token = null)
    {
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
        ));

        if ($pb_player_id) {
            $this->mongo_db->where('pb_player_id', $pb_player_id);
        }
        if ($device_token) {
            $this->mongo_db->where('device_token', $device_token);
        }
        $this->mongo_db->set('deleted' , true);
        return $this->mongo_db->update_all('playbasis_player_device');
    }

    public function getDeviceByToken($client_id, $site_id, $device_token)
    {
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'device_token' => $device_token
        ));

        $this->mongo_db->where_ne('deleted', true);
        $result =  $this->mongo_db->get('playbasis_player_device');
        return $result ? $result : null;
    }
    
    public function getMonthLeaderboardsByCustomParameter($input, $client_id, $site_id)
    {

        $rankBy = $input['param'];
        $limit = $input['limit'];
        $group_by = $input['group_by'];
        $param_str = "$" . "parameters" . "." . $rankBy;
        $group_by_str = "$" . $group_by;

        // default is present month
        if (isset($input['year']) && isset($input['month'])) {
            $selected_time = strtotime($input['year'] . "-" . $input['month']);
        } else {
            $selected_time = time();
        }

        // Aggregate the data
        $first = date('Y-m-01', $selected_time);
        $from = strtotime($first . ' 00:00:00');

        $last = date('Y-m-t', $selected_time);
        $to = strtotime($last . ' 23:59:59');
        $raw_result = $this->mongo_db->aggregate('playbasis_validated_action_log', array(
            array(
                '$match' => array(
                    'action_name' => $input['action_name'],
                    'site_id' => $site_id,
                    'client_id' => $client_id,
                    'date_added' => array('$gte' => new MongoDate($from), '$lte' => new MongoDate($to))
                ),
            ),
            array(
                '$group' => array(
                    '_id' => array($group_by => $group_by_str),
                    $rankBy => array('$push' => $param_str)
                )
            ),
            array(
                '$sort' => array($rankBy => -1),
            ),
            array(
                '$limit' => $limit + 20,
            )
        ));
        // This function will remove the deleted player and also name key to $rankBy
        //$raw_result = $raw_result ? $this->removeDeletedPlayers($raw_result['result'], $limit, $rankBy) : array();

        // Sort the leader !
        $result = array();
        foreach ($raw_result['result'] as $key => $raw) {
            $result[$key][$group_by] = $raw['_id'][$group_by];

            $temp_name[$key] = $raw['_id'][$group_by];
            if ($input['mode'] == "sum") {
                $temp_value[$key] = array_sum($raw[$rankBy]);
            } else {
                $temp_value[$key] = count($raw[$rankBy]);
            }
            $result[$key][$rankBy] = $temp_value[$key];
        }
        if (isset($temp_value) && isset($temp_name)) {
            array_multisort($temp_value, SORT_DESC, $temp_name, SORT_ASC, $result);
        }

        return $result;
    }

    public function getSecuritySetting($client_id, $site_id)
    {
        $this->mongo_db->where('client_id', $client_id);
        $this->mongo_db->where('site_id', $site_id);

        $results = $this->mongo_db->get("playbasis_setting");
        $results = $results ? $results[0] : null;

        if ($results['password_policy_enable'] == false) {
            unset($results['password_policy']);
        }

        return $results;
    }

    public function increaseLoginAttempt($site_id, $pb_player_id)
    {
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('_id', $pb_player_id);
        $this->mongo_db->inc('login_attempt', 1);
        $this->mongo_db->update("playbasis_player");
    }

    public function resetLoginAttempt($site_id, $pb_player_id)
    {
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('_id', $pb_player_id);
        $this->mongo_db->set('login_attempt', 0);
        $this->mongo_db->update("playbasis_player");
    }

    public function lockPlayer($site_id, $pb_player_id)
    {
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('_id', $pb_player_id);
        $this->mongo_db->set('locked', true);
        $this->mongo_db->update("playbasis_player");
    }

    public function unlockPlayer($site_id, $pb_player_id)
    {
        $this->mongo_db->where('site_id', $site_id);
        $this->mongo_db->where('_id', $pb_player_id);
        $this->mongo_db->set('locked', false);
        $this->mongo_db->set('login_attempt', 0);
        $this->mongo_db->update("playbasis_player");
    }

    public function getActionHistory(
        $client_id,
        $site_id,
        $player_id,
        $action,
        $parameter,
        $month = null,
        $year = null,
        $count
    ) {
        $result = array();

        // default is present month/year
        if (!isset($month)) {
            $month = date("m", time());
        }
        if (!isset($year)) {
            $year = date("Y", time());
        }

        $this_month_time = strtotime($year . "-" . $month);

        $first = date('Y-m-01', strtotime('-' . ($count) . ' month', $this_month_time));
        $from = strtotime($first . ' 00:00:00');

        $last = date('Y-m-t', $this_month_time);
        $to = strtotime($last . ' 23:59:59');

        $status = $this->mongo_db->aggregate('playbasis_validated_action_log', array(

            array(
                '$match' => array(
                    'action_name' => $action,
                    'site_id' => $site_id,
                    'client_id' => $client_id,
                    'date_added' => array('$gte' => new MongoDate($from), '$lte' => new MongoDate($to)),
                    'cl_player_id' => $player_id
                ),
            ),
            array(
                '$group' => array(
                    '_id' => array(
                        "year" => array('$year' => '$date_added'),
                        "month" => array('$month' => '$date_added')
                    ),
                    $parameter => array('$push' => '$parameters.' . $parameter)
                )
            ),
            array(
                '$sort' => array('_id' => -1),
            )
        ));

        array_push($status['result'], 0);
        $gap = 0;
        for ($index = 0; $index < $count; $index++) {
            $current_month = date("m", strtotime('-' . ($index) . ' month', $this_month_time));
            $current_year = date("Y", strtotime('-' . ($index) . ' month', $this_month_time));

            if ($status['result'][$index - $gap]['_id']['month'] != $current_month || $status['result'][$index - $gap]['_id']['year'] != $current_year) {
                $result[$current_year][$current_month] = array($parameter => 0);
                $gap++;
            } else {
                $result[$current_year][$current_month] = array($parameter => array_sum($status['result'][$index - $gap][$parameter]));
            }

        }

        return $result;
    }

    public function find_player_with_in($client_id, $site_id, $optionalsParam=null)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where('client_id', $client_id);
        $this->mongo_db->where('site_id', $site_id);

        if (isset($optionalsParam['source'])){
            $this->mongo_db->where('custom.source', $optionalsParam['source']);
        }elseif (isset($optionalsParam['email'])){
            $regex = new MongoRegex("/" . preg_quote(mb_strtolower($optionalsParam['email'])) . "/i");
            $this->mongo_db->where('email', $regex);
            if (isset($optionalsParam['not_source'])){
                $this->mongo_db->where_ne('custom.source', $optionalsParam['not_source']);
            }
        }else{
            if (isset($optionalsParam['not_email'])){
                $regex = new MongoRegex("/.*" . preg_quote(mb_strtolower($optionalsParam['not_email'])) . ".*/i");
                $this->mongo_db->where(array(
                    'email' => array(
                        '$not' => $regex
                    )
                ));
            }
            if (isset($optionalsParam['not_source'])){
                $this->mongo_db->where_ne('custom.source', $optionalsParam['not_source']);
            }
        }

        return $this->mongo_db->distinct('_id', 'playbasis_player');
    }

}

function index_id($obj)
{
    return $obj['_id'];
}

?>