<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Point_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function getRewardNameById($data)
    {
        $this->set_site_mongodb($data['site_id']);
        $this->mongo_db->select(array('name'));
        $this->mongo_db->where(array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'reward_id' => $data['reward_id']
        ));
        $result = $this->mongo_db->get('playbasis_reward_to_client');
        return ($result) ? $result[0]['name'] : $result;
    }

    public function getPlayerRewardExpiration($client_id, $site_id, $pb_player_id, $reward_id)
    {
        $this->mongo_db->where('client_id' , new MongoId($client_id));
        $this->mongo_db->where('site_id' , new MongoId($site_id));
        $this->mongo_db->where('pb_player_id' , new MongoId($pb_player_id));
        $this->mongo_db->where('reward_id' , new MongoId($reward_id));
        $this->mongo_db->where_lte('date_expire' , new MongoDate());
        $result = $this->mongo_db->get('playbasis_reward_expiration_to_player');
        return $result;
    }

    public function findPoint($data)
    {
        $this->set_site_mongodb($data['site_id']);
        $this->mongo_db->select(array('reward_id'));
        $this->mongo_db->where(array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'name' => strtolower($data['reward_name'])
        ));
        $result = $this->mongo_db->get('playbasis_reward_to_client');
        /*if($result)
            return $result[0]['reward_id'];*/
        return $result ? $result[0]['reward_id'] : array();
    }

    public function findOnlyPoint($data)
    {
        $this->set_site_mongodb($data['site_id']);
        $this->mongo_db->select(array('reward_id'));
        $this->mongo_db->where(array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'group' => 'POINT',
            'name' => strtolower($data['reward_name'])
        ));
        $result = $this->mongo_db->get('playbasis_reward_to_client');
        /*if($result)
            return $result[0]['reward_id'];*/
        return $result ? $result[0]['reward_id'] : array();
    }
}

?>