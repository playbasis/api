<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Model extends CI_Model
{
	protected $dbs = null;
	protected $dbGroups = null;
	protected $site = 0;
	
	//array of database groups to load for each site_id
	private static $dblist = array(
		0 => 'developer',
		1 => 'developer'
	);
	
	//mongodb setup
	private static $mongoBDsNames = array(
		0 => 'pbapp',
		1 => 'demo'
	);
	private static $mongoDBs = array(
		0 => 0,
		1 => 0,
		2 => 0
	);
	protected $mongoSite = 0;
	
	public function __construct()
	{
		parent::__construct();
		$this->multi_db_load($this);
	}
	public function set_site($site_id)
	{
		$this->site = (isset($this->dbs[$site_id]) && $this->dbs[$site_id]) ? $site_id : 0;
	}
	public function set_site_mongodb($site_id)
	{
		$currDB = self::$mongoDBs[$this->mongoSite];
		$this->mongoSite = isset(self::$mongoDBs[$site_id]) ? $site_id : 0;
		$newDB = self::$mongoDBs[$this->mongoSite];
		if($currDB == $newDB)
			return; //no need to switch
		$this->mongo_db->switch_db(self::$mongoBDsNames[$newDB]);
	}
	public function site_db()
	{
		return $this->dbs[$this->site];
	}
	//load all databases
	private function multi_db_load($mdl)
	{
		$this->dbs = array();
		$this->dbGroups = array();
		foreach(self::$dblist as $key => $value)
		{
			$this->dbs[$key] = $mdl->load->database($value, TRUE);
			$this->dbGroups[$key] = $value;
		}
	}
}

