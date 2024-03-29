<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Campaign_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('playbasis');
    }

    public function getCampaign($client_id, $site_id, $campaign_name=false, $tags = null)
    {
        $this->set_site_mongodb($site_id);

        $this->mongo_db->select(array('name','image','date_start','date_end','weight','tags'));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'deleted' => false
        ));
        if ($campaign_name){
            $this->mongo_db->where('name', $campaign_name);
        }

        if ($tags) {
            $this->mongo_db->where_in('tags', $tags);
        }

        $this->mongo_db->order_by(array('weight' => 'ASC', 'name' => 'ASC'));
        $result = $this->mongo_db->get('playbasis_campaign_to_client');

        return $result;
    }

    public function getActiveCampaign($client_id, $site_id, $tags = null)
    {
        $this->set_site_mongodb($site_id);
        $d = new MongoDate();
        $this->mongo_db->select(array('name','image','date_start','date_end','weight','tags'));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'deleted' => false
        ));

        if ($tags) {
            $this->mongo_db->where_in('tags', $tags);
        }

        $this->mongo_db->where(array('$and' => array( array('$or' => array(array("date_start" => null), array("date_start" => array('$lte'=> $d)))),
                                                      array('$or' => array(array("date_end" => array('$gte'=> $d)), array("date_end" => null))))));
        $this->mongo_db->order_by(array('weight' => 'desc','date_start' => 'desc', "date_end" => 'asc' , 'name' => 'asc'));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_campaign_to_client');

        return $result ? $result[0]:null;
    }
}