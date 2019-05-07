<?php
defined('BASEPATH') OR exit('No direct script access allowed');
define("CUSTOM_POINT_START_ID", 10000);

class User_model extends MY_Model
{
  public function __construct()
  {
    parent::__construct();
    $this->load->library('mongo_db');
  }

  public function insertSample()
  {
    $name = 'name'.mt_rand();
    $this
      ->mongo_db
      ->insert( 'user',
          [
            'name' => $name,
            'created' => new MongoDate()
          ]);
  }

  public function getAll()
  {
    return $this->mongo_db->get('user');
  }
}