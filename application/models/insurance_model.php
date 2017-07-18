<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Insurance_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function getInsuranceConfig($client_id, $site_id)
    {
        $this->mongo_db->where('client_id', new MongoID($client_id));
        $this->mongo_db->where('site_id', new MongoID($site_id));
        $result = $this->mongo_db->get('playbasis_insurance_config');
        return $result ? $result[0] : array();
    }
}