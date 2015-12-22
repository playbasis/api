<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Store_org_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('mongo_db');
    }

    public function retrieveNodeById($site_id, $id)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('site_id', new MongoId($site_id));

        $this->mongo_db->where('_id', new MongoId($id));
        $this->mongo_db->where('deleted', false);
        $c = $this->mongo_db->get("playbasis_store_organize_to_client");

        if ($c) {
            return $c[0];
        } else {
            return null;
        }
    }

    public function createPlayerToNode($client_id, $site_id, $pb_player_id, $node_id)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $insert_data = array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'node_id' => $node_id,

        );

        $insert = $this->mongo_db->insert('playbasis_store_organize_to_player', $insert_data);

        return $insert;
    }

    public function retrievePlayerToNode($client_id, $site_id, $pb_player_id, $node_id, $role_name = null)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('client_id', new MongoId($client_id));
        $this->mongo_db->where('site_id', new MongoId($site_id));

        $this->mongo_db->where('pb_player_id', new MongoId($pb_player_id));
        $this->mongo_db->where('node_id', new MongoId($node_id));

        if (isset($role_name)) {
            $this->mongo_db->where_exists('roles.'.$role_name, true);
        }

        $c = $this->mongo_db->get("playbasis_store_organize_to_player");

        if ($c) {
            return $c[0];
        } else {
            return null;
        }
    }

    public function deletePlayerToNode($client_id, $site_id, $pb_player_id, $node_id)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('client_id', new MongoId($client_id));
        $this->mongo_db->where('site_id', new MongoId($site_id));

        $this->mongo_db->where('pb_player_id', new MongoId($pb_player_id));
        $this->mongo_db->where('node_id', new MongoId($node_id));
        $c = $this->mongo_db->delete("playbasis_store_organize_to_player");

        if ($c) {
            return $c[0];
        } else {
            return null;
        }
    }

    public function setPlayerRoleToNode($client_id, $site_id, $pb_player_id, $node_id, $role)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('client_id', new MongoId($client_id));
        $this->mongo_db->where('site_id', new MongoId($site_id));
        $this->mongo_db->where('pb_player_id', new MongoId($pb_player_id));
        $this->mongo_db->where('node_id', new MongoId($node_id));

        $this->mongo_db->set('roles.'.$role['name'], $role['value']);

        $update = $this->mongo_db->update('playbasis_store_organize_to_player');

        return $update;
    }

    public function unsetPlayerRoleToNode($client_id, $site_id, $pb_player_id, $node_id, $role_name_to_unset)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where('client_id', new MongoId($client_id));
        $this->mongo_db->where('site_id', new MongoId($site_id));
        $this->mongo_db->where('pb_player_id', new MongoId($pb_player_id));
        $this->mongo_db->where('node_id', new MongoId($node_id));

        $this->mongo_db->unset_field('roles.'.$role_name_to_unset);

        $update = $this->mongo_db->update('playbasis_store_organize_to_player');

        return $update;
    }

    public function findAdjacentChildNode($client_id, $site_id, $node_id){
        $this->mongo_db->select(array(
            'name',
            'organize',

        ));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'parent' => $node_id,
        ));
        $result = $this->mongo_db->get('playbasis_store_organize_to_client');
        if(empty($result)){
            return null;
        }else{
            return $result;
        }

    }

    public function getSaleHistoryOfNode($client_id, $site_id, $node_list, $action, $parameter, $month=null, $year=null,$count){
        $result = array();

        $node_to_match = array();
        foreach($node_list as $node){
            array_push($node_to_match, array('node_id'=>new MongoId($node)));
        }
        // default is present month/year
        if(!isset($month)){
            $month = date("m",time());
        }
        if(!isset($year)){
            $year = date("Y",time());
        }

        $this_month_time = strtotime($year."-".$month);

        $first = date('Y-m-01', strtotime('-'.($count).' month', $this_month_time));
        $from = strtotime($first.' 00:00:00');

        $last = date('Y-m-t', $this_month_time);
        $to   = strtotime($last.' 23:59:59');

        $status = $this->mongo_db->aggregate('playbasis_validated_action_log', array(

            array(
                '$match' => array(
                    'action_name' => $action,
                    'site_id' => $site_id,
                    'client_id' => $client_id,
                    'date_added' => array('$gte' => new MongoDate($from),'$lte' => new MongoDate($to)),
                    '$or' => $node_to_match
                ),
            ),
            array(
                '$group' => array(
                    '_id' => array("year"=>array('$year' => '$date_added'),"month"=> array('$month' => '$date_added')),
                    $parameter => array('$push' => '$parameters.'.$parameter))
            ),
            array(
                '$sort' => array('_id' => -1),
            )
        ));

        array_push($status['result'],0);
        $gap=0;
        for($index = 0; $index < $count; $index++){
            $current_month = date("m",strtotime('-'.($index).' month', $this_month_time));
            $current_year = date("Y",strtotime('-'.($index).' month', $this_month_time));

            if($status['result'][$index-$gap]['_id']['month']!=$current_month || $status['result'][$index-$gap]['_id']['year']!=$current_year){
                $result[$current_year][$current_month]=array($parameter=>0);
                $gap++;
            }else{
                $result[$current_year][$current_month]= array($parameter=>array_sum($status['result'][$index-$gap][$parameter]));
            }

        }

        return $result;
    }
}