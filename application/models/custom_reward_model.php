<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Custom_reward_model extends MY_Model
{

    public function retrieveCustomRewardByID($client_id, $site_id, $item_id)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('client_id', new MongoId($client_id));
        $this->mongo_db->where('site_id', new MongoId($site_id));
        $this->mongo_db->where('_id', new MongoId($item_id));
        $this->mongo_db->where('deleted', false);

        //$this->mongo_db->select(array('name', 'file_name', 'tags', 'file_id'));
        $result = $this->mongo_db->get('playbasis_custom_reward_to_client');

        return $result ? $result[0] : null;
    }

}

?>