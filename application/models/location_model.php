<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Location_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function getLocation($client_id, $site_id, $data)
    {
        $this->set_site_mongodb($site_id);

        $this->mongo_db->select(array(), array('_id'));
        $this->mongo_db->where(array(
            'deleted' => false,
            'client_id' => $client_id,
            'site_id' => $site_id
        ));

        if(isset($data['location_id']) && $data['location_id']){
            if ($this->isMongoIdObject($data['location_id'])) {
                $this->mongo_db->where('_id', $data['location_id']);
            } elseif ($this->isMongoId($data['location_id'])) {
                $this->mongo_db->where('_id', new MongoID($data['location_id']));
            } else {
                return array();
            }
        }

        if(isset($data['status']) && $data['status']){
            $this->mongo_db->where('status',(strtolower($data['status'])==='false') ? false : true);
        }

        $result = $this->mongo_db->get('playbasis_location_to_client');
        if ($result) {
            foreach ($result as &$location) {
                if(isset($location['image'])){
                    $location['image'] = $this->config->item('IMG_PATH') . $location['image'];
                }
            }
        }

        return $result ? $result : array();
    }

    private function isMongoId($id)
    {
        return is_string($id) && preg_match('/^[0-9a-f]{24}$/i', $id) === 1;
    }

    private function isMongoIdObject($id)
    {
        return is_object($id) && strtolower(get_class($id)) === 'mongoid';
    }
}
