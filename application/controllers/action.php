<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';
class Action extends REST_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('auth_model');
		$this->load->model('action_model');
		$this->load->model('tool/error', 'error');
		$this->load->model('tool/respond', 'resp');
	}
	public function index_get()
	{
		/* GET */
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		/* main */
		$action = array();
		foreach ($this->action_model->listActions($validToken) as $key => $value) {
			array_push($action, $value['name']);
		}
		$this->response($this->resp->setRespond($action), 200);
	}

	public function log_get()
	{
		/* GET */
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		/* main */
		$log = array();
		$prev = null;
		foreach ($this->action_model->listActions($validToken) as $key => $v) {
			$action_name = $v['name'];
			foreach ($this->action_model->actionLog($validToken, $action_name, $this->input->get('from'), $this->input->get('to')) as $key => $value) {
				$key = $value['_id'];
				if ($prev) {
					$d = $prev;
					while (strtotime($d) <= strtotime($key)) {
						if (!array_key_exists($d, $log)) $log[$d] = array('' => true); // force output to be "{}" instead of "[]"
						$d = date('Y-m-d', strtotime('+1 day', strtotime($d)));
					}
				}
				$prev = $key;
				if ($value['value'] != 'SKIP') {
					if (array_key_exists($key, $log)) {
						$log[$key][$action_name] = $value['value'];
					} else {
						$log[$key] = array($action_name => $value['value']);
					}
					if (array_key_exists('', $log[$key])) unset($log[$key]['']);
				}
			}
		}
		ksort($log);
		$log2 = array();
		if (!empty($log)) foreach ($log as $key => $value) {
			array_push($log2, array($key => $value));
		}
		$this->response($this->resp->setRespond($log2), 200);
	}
	public function test_get()
	{
		echo '<pre>';
		$credential = array(
			'key' => 'abc',
			'secret' => 'abcde'
			);
		$token = $this->auth_model->getApiInfo($credential);
		echo '<br>findAction:<br>';
		$result = $this->action_model->findAction(array_merge($token, array('action_name'=>'like')));
		print_r($result);
		echo '</pre>';
	}
}
?>