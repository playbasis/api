<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Timestamp_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function insertTimestamp($client_id, $site_id, $pb_player_id, $timestamp, $data)
    {
        $insert_data = array(
            'client_id' => new MongoID($client_id),
            'site_id' => new MongoID($site_id),
            'pb_player_id' => is_null($pb_player_id) ? null : new MongoID($pb_player_id),
            'date_added' => $timestamp,
        );
        if(!empty($data)){
            $insert_data = $insert_data+$data;
        }
        return $this->mongo_db->insert('playbasis_timestamp_to_player', $insert_data);
    }

    public function retriveTimestamp($client_id, $site_id, $pb_player_id, $query_data, $order)
    {
        $this->mongo_db->select(array(),array('_id','client_id','site_id'));
        $query_data['client_id'] = new MongoID($client_id);
        $query_data['site_id'] = new MongoID($site_id);
        if($pb_player_id){
            $query_data['pb_player_id'] = new MongoID($pb_player_id);
        }
        $this->mongo_db->where($query_data);
        if (mb_strtolower($order) == 'asc') {
            $order = 1;
        } else {
            $order = -1;
        }
        $this->mongo_db->order_by(array('_id' => $order)); // order from the newer to older

        return $this->mongo_db->get('playbasis_timestamp_to_player');
    }
}