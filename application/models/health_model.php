<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Health_Model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('playbasis');
        $this->load->library('mongo_db');
    }

    public function testAction()
    {
        return 'test action';
    }
    public function storeHealthLog($data)
    {
        $mongoDate = new MongoDate(time());

        return $this->mongo_db->insert('health_log', array(

            'type' => $data['type'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'datetime_start' => (isset($data['datetime_start'])) ? new MongoDate(strtotime($data['datetime_start'])) : null,
            'datetime_end' => (isset($data['datetime_end'])) ? new MongoDate(strtotime($data['datetime_end'])) : null,
            'description' => $data['description'],
            'create_date' => $mongoDate

        ));

    }
    public function getHealthLog($data)
    {
        $mongoDate = new MongoDate(time());

        $this->mongo_db->select(null);
        $this->mongo_db->where(array(
            'type' => $data['type'],
            'player_id'=> new MongoId($data['player_id'])
        ));
        $log = $this->mongo_db->get('health_log');

        return $log;

    }
    public function storeHealthInfo($data)
    {
        $mongoDate = new MongoDate(time());

        return $this->mongo_db->insert('health_info', array(
            'player_id' => new MongoId($data['player_id']),
            'weight' => $data['weight'],
            'height' => $data['height'],
            'birth_date' => (isset($data['birth_date'])) ? new MongoDate(strtotime($data['birth_date'])) : null,
            'medical_condition' => $data['medical_condition'],
            'medical_note' => $data['medical_note'],
            'allergies_reaction' => $data['allergies_reaction'],
            'blood_type' => $data['blood_type'],
            'sex' => $data['sex'],
            'create_date' => $mongoDate

        ));

    }
    public function getHealthInfo($player)
    {
        $mongoDate = new MongoDate(time());

        $this->mongo_db->select(null);
        $this->mongo_db->where_in('_id',$player);
        $info = $this->mongo_db->get('health_info');
        return $info;

    }
}