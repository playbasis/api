<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Link_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function getConfig($client_id, $site_id)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id
        ));
        $results = $this->mongo_db->get('playbasis_link_to_client_setting');
        return $results ? $results[0] : null;
    }

    public function find($client_id, $site_id, $key=null)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'status' => true,
            'deleted' => false
        ));
        if ($key) $this->mongo_db->where('key', $key);
        $results = $this->mongo_db->get('playbasis_link_to_client');
        return $results && $key ? $results[0]['url'] : null;
    }

    public function save($client_id, $site_id, $key, $url)
    {
        $d = new MongoDate();
        $this->set_site_mongodb($site_id);
        return $this->mongo_db->insert('playbasis_link_to_client', array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'key' => $key,
            'url' => $url,
            'status' => true,
            'deleted' => false,
            'date_added' => $d,
            'date_expire' => $d
        ));
    }
}

?>