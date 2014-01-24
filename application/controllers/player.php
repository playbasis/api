<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
class Player extends REST_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('auth_model');
		$this->load->model('player_model');
		$this->load->model('tracker_model');
		$this->load->model('point_model');
		$this->load->model('action_model');
        $this->load->model('level_model');
		$this->load->model('tool/error', 'error');
		$this->load->model('tool/utility', 'utility');
		$this->load->model('tool/respond', 'resp');
		$this->load->model('tool/node_stream', 'node');
	}
	public function index_get($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		//read player information
		$player['player'] = $this->player_model->readPlayer($pb_player_id, $site_id, array(
			'username',
			'first_name',
			'last_name',
			'gender',
			'image',
			'exp',
			'level',
			'date_added AS registered',
			'birth_date'
		));
		//get last login/logout
		$player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGIN');
		$player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGOUT');
		$this->response($this->resp->setRespond($player), 200);
	}
	public function index_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		//read player information
		$player['player'] = $this->player_model->readPlayer($pb_player_id, $site_id, array(
			'username',
			'first_name',
			'last_name',
			'gender',
			'image',
            'email',
			'exp',
			'level',
			'date_added AS registered',
			'birth_date'
		));
		//get last login/logout
		$player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGIN');
		$player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGOUT');
		$this->response($this->resp->setRespond($player), 200);
	}
    /*public function list_get()
    {
        $required = $this->input->checkParam(array(
            'api_key',
            'list_player_id'
        ));
        if($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        $validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
        $site_id = $validToken['site_id'];
        $list_player_id = explode(",", $this->input->get('list_player_id'));
        //read player information
        $player['player'] = $this->player_model->readListPlayer($list_player_id, $site_id, array(
            'username',
            'first_name',
            'last_name',
            'gender',
            'image',
            'exp',
            'level',
            'date_added AS registered',
            'birth_date'
        ));

        $this->response($this->resp->setRespond($player), 200);
    }*/
    public function list_post()
    {
        $required = $this->input->checkParam(array(
            'token',
            'list_player_id'
        ));
        if($required)
            $this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
        $validToken = $this->auth_model->findToken($this->input->post('token'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_TOKEN'), 200);
        $site_id = $validToken['site_id'];
        $list_player_id = explode(",", $this->input->post('list_player_id'));
        //read player information
        $player['player'] = $this->player_model->readListPlayer($list_player_id, $site_id, array(
            'cl_player_id',
            'username',
            'first_name',
            'last_name',
            'gender',
            'image',
            'email',
            'exp',
            'level',
            'date_added AS registered',
            'birth_date'
        ));

        $this->response($this->resp->setRespond($player), 200);
    }
	public function details_get($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);

		//read player information
		$player['player'] = $this->player_model->readPlayer($pb_player_id, $site_id, array(
			'username',
			'first_name',
			'last_name',
			'gender',
			'image',
			'exp',
			'level',
			'date_added AS registered',
			'birth_date'
		));

        //percent exp of level
        $level = $this->level_model->getLevelDetail($player['player']['level'], $validToken['client_id'], $validToken['site_id']);
        $base_exp = $level['min_exp'];
        $max_exp = $level['max_exp'] - $base_exp;
        $now_exp = $player['player']['exp'] - $base_exp;
        if(isset($level['max_exp'])){
            $percent_exp = (floatval($now_exp) * floatval (100)) / floatval($max_exp);
            $player['player']['percent_of_level'] = round($percent_exp,2);
        }else{
            $player['player']['percent_of_level'] = 100;
        }

        $player['player']['badges'] = $this->player_model->getBadge($pb_player_id, $site_id);
        $points = $this->player_model->getPlayerPoints($pb_player_id, $site_id);
        foreach($points as &$point)
        {
			$point['reward_name'] = $this->point_model->getRewardNameById(array_merge($validToken, array(
                'reward_id' => $point['reward_id']
            )));
            ksort($point);
        }
        $player['player']['points'] = $points;
		//get last login/logout
		$player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGIN');
		$player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGOUT');
		$this->response($this->resp->setRespond($player), 200);
	}
	public function details_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);

		//read player information
		$player['player'] = $this->player_model->readPlayer($pb_player_id, $site_id, array(
			'username',
			'first_name',
			'last_name',
			'gender',
			'image',
            'email',
			'exp',
			'level',
			'date_added AS registered',
			'birth_date'
		));

        //percent exp of level
        $level = $this->level_model->getLevelDetail($player['player']['level'], $validToken['client_id'], $validToken['site_id']);
        $base_exp = $level['min_exp'];
        $max_exp = $level['max_exp'] - $base_exp;
        $now_exp = $player['player']['exp'] - $base_exp;
        if(isset($level['max_exp'])){
            $percent_exp = (floatval($now_exp) * floatval (100)) / floatval($max_exp);
            $player['player']['percent_of_level'] = round($percent_exp,2);
        }else{
            $player['player']['percent_of_level'] = 100;
        }

        $player['player']['badges'] = $this->player_model->getBadge($pb_player_id, $site_id);
        $points = $this->player_model->getPlayerPoints($pb_player_id, $site_id);
        foreach($points as &$point)
        {
            $point['reward_name'] = $this->point_model->getRewardNameById(array_merge($validToken, array(
                'reward_id' => $point['reward_id']
            )));
            ksort($point);
        }
        $player['player']['points'] = $points;
		//get last login/logout
		$player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGIN');
		$player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $site_id, 'LOGOUT');
		$this->response($this->resp->setRespond($player), 200);
	}
	public function register_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		$required = $this->input->checkParam(array(
			'image',
			'email',
			'username'
		));
		if(!$player_id)
			array_push($required, 'player_id');
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id > 0)
			$this->response($this->error->setError('USER_ALREADY_EXIST'), 200);
		$playerInfo = array(
			'email' => $this->input->post('email'),
			'image' => $this->input->post('image'),
			'username' => $this->input->post('username'),
			'player_id' => $player_id
		);
		$firstName = $this->input->post('first_name');
		if($firstName)
			$playerInfo['first_name'] = $firstName;
		$lastName = $this->input->post('last_name');
		if($lastName)
			$playerInfo['last_name'] = $lastName;
		$nickName = $this->input->post('nickname');
		if($nickName)
			$playerInfo['nickname'] = $nickName;
		$facebookId = $this->input->post('facebook_id');
		if($facebookId)
			$playerInfo['facebook_id'] = $facebookId;
		$twitterId = $this->input->post('twitter_id');
		if($twitterId)
			$playerInfo['twitter_id'] = $twitterId;
		$instagramId = $this->input->post('instagram_id');
		if($instagramId)
			$playerInfo['instagram_id'] = $instagramId;
		$password = $this->input->post('password');
		if($password)
			$playerInfo['password'] = $password;
		$gender = $this->input->post('gender');
		if($gender)
			$playerInfo['gender'] = $gender;
		$birthdate = $this->input->post('birth_date');
		if($birthdate)
		{
			$timestamp = strtotime($birthdate);
			$playerInfo['birth_date'] = date('Y-m-d', $timestamp);
		}
		$this->player_model->createPlayer(array_merge($validToken, $playerInfo));
		$this->response($this->resp->setRespond(), 200);
	}
	public function update_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);		
		$playerInfo = array();
		$email = $this->input->post('email');
		if($email)
			$playerInfo['email'] = $email;
		$image = $this->input->post('image');
		if($image)
			$playerInfo['image'] = $image;
		$username = $this->input->post('username');
		if($username)
			$playerInfo['username'] = $username;
		$exp = $this->input->post('exp');
		if(is_numeric($exp))
			$playerInfo['exp'] = $exp;
		$level = $this->input->post('level');
		if(is_numeric($level))
			$playerInfo['level'] = $level;
		$firstName = $this->input->post('first_name');
		if($firstName)
			$playerInfo['first_name'] = $firstName;
		$lastName = $this->input->post('last_name');
		if($lastName)
			$playerInfo['last_name'] = $lastName;
		$nickName = $this->input->post('nickname');
		if($nickName)
			$playerInfo['nickname'] = $nickName;
		$facebookId = $this->input->post('facebook_id');
		if($facebookId)
			$playerInfo['facebook_id'] = $facebookId;
		$twitterId = $this->input->post('twitter_id');
		if($twitterId)
			$playerInfo['twitter_id'] = $twitterId;
		$instagramId = $this->input->post('instagram_id');
		if($instagramId)
			$playerInfo['instagram_id'] = $instagramId;
		$password = $this->input->post('password');
		if($password)
			$playerInfo['password'] = $password;
		$gender = $this->input->post('gender');
		if($gender)
			$playerInfo['gender'] = $gender;
		$birthdate = $this->input->post('birth_date');
		if($birthdate)
		{
			$timestamp = strtotime($birthdate);
			$playerInfo['birth_date'] = date('Y-m-d', $timestamp);
		}
		$this->player_model->updatePlayer($pb_player_id, $validToken['site_id'], $playerInfo);
		$this->response($this->resp->setRespond(), 200);
	}
	public function delete_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		$this->player_model->deletePlayer($pb_player_id, $validToken['site_id']);
		$this->response($this->resp->setRespond(), 200);
	}
	public function login_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		//trigger and log event
		$eventMessage = $this->utility->getEventMessage('login');
		$this->tracker_model->trackEvent('LOGIN', $eventMessage, array(
			'client_id' => $validToken['client_id'],
			'site_id' => $validToken['site_id'],
			'pb_player_id' => $pb_player_id,
			'action_log_id' => 0
		));
		//publish to node stream
		$this->node->publish(array(
			'pb_player_id' => $pb_player_id,
			'action_name' => 'login',
			'message' => $eventMessage
		), $validToken['domain_name'], $validToken['site_id']);
		$this->response($this->resp->setRespond(), 200);
	}
	public function logout_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		//trigger and log event
		$eventMessage = $this->utility->getEventMessage('logout');
		$this->tracker_model->trackEvent('LOGOUT', $eventMessage, array(
			'client_id' => $validToken['client_id'],
			'site_id' => $validToken['site_id'],
			'pb_player_id' => $pb_player_id,
			'action_log_id' => 0
		));
		//publish to node stream
		$this->node->publish(array(
			'pb_player_id' => $pb_player_id,
			'action_name' => 'logout',
			'message' => $eventMessage
		), $validToken['domain_name'], $validToken['site_id']);
		$this->response($this->resp->setRespond(), 200);
	}
	public function points_get($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		$input = array_merge($validToken, array(
			'pb_player_id' => $pb_player_id
		));
		//get player points
		$points['points'] = $this->player_model->getPlayerPoints($pb_player_id, $site_id);
		foreach($points['points'] as &$point)
		{
			$point['reward_name'] = $this->point_model->getRewardNameById(array_merge($input, array(
				'reward_id' => $point['reward_id']
			)));
			ksort($point);
		}
		$this->response($this->resp->setRespond($points), 200);
	}
	public function point_get($player_id = '', $reward = '')
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$required = array();
		if(!$player_id)
			array_push($required, 'player_id');
		if(!$reward)
			array_push($required, 'reward');
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		$input = array_merge($validToken, array(
			'reward_name' => $reward
		));
		$reward_id = $this->point_model->findPoint($input);
		if(!$reward_id)
			$this->response($this->error->setError('REWARD_NOT_FOUND'), 200);
		$point['point'] = $this->player_model->getPlayerPoint($pb_player_id, $reward_id, $site_id);
		$point['point'][0]['reward_name'] = $reward;
		ksort($point);
		$this->response($this->resp->setRespond($point), 200);
	}
	public function action_get($player_id = '', $action = '', $option = 'time')
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$required = array();
		if(!$player_id)
			array_push($required, 'player_id');
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		$actions = array();
		if($action)
		{
			$action_id = $this->action_model->findAction(array_merge($validToken, array(
				'action_name' => $action
			)));
			if(!$action_id)
				$this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
			$actions['action'] = ($option == 'time') ? $this->player_model->getActionPerform($pb_player_id, $action_id, $site_id) : $this->player_model->getActionCount($pb_player_id, $action_id, $site_id);
		}
		else //get last action performed
		{
			if($option != 'time')
				$this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
			$actions['action'] = $this->player_model->getLastActionPerform($pb_player_id, $site_id);
		}
		$this->response($this->resp->setRespond($actions), 200);
	}
	public function badge_get($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		//get player badge
		$badgeList = $this->player_model->getBadge($pb_player_id, $site_id);
		$this->response($this->resp->setRespond($badgeList), 200);
	}
	public function rank_get($ranked_by, $limit = 20)
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		if(!$ranked_by)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'ranked_by'
			)), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$leaderboard = $this->player_model->getLeaderboard($ranked_by, $limit, $validToken['client_id'], $validToken['site_id']);
		$this->response($this->resp->setRespond($leaderboard), 200);
	}
	public function ranks_get($limit = 20)
	{
		$required = $this->input->checkParam(array(
			'api_key'
		));
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
		$leaderboards = $this->player_model->getLeaderboards($limit, $validToken['client_id'], $validToken['site_id']);
		$this->response($this->resp->setRespond($leaderboards), 200);
	}
    public function level_get($level='')
    {
        $required = $this->input->checkParam(array(
            'api_key'
        ));
        if($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        if(!$level)
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'level'
            )), 200);
        $validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);

        $level= $this->level_model->getLevelDetail($level, $validToken['client_id'], $validToken['site_id']);
        $this->response($this->resp->setRespond($level), 200);
    }
    public function levels_get()
    {
        $required = $this->input->checkParam(array(
            'api_key'
        ));
        if($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        $validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);

        $level= $this->level_model->getLevelsDetail($validToken['client_id'], $validToken['site_id']);
        $this->response($this->resp->setRespond($level), 200);
    }
    public function point_history_get($player_id = '')
    {
        $offset = 0;
        if($this->input->get('offset'))
            $offset = $this->input->get('offset');

        $limit = 20;
        if($this->input->get('limit'))
            $limit = $this->input->get('limit');

        $reward = null;
        if($this->input->get('point_name'))
            $reward = $this->input->get('point_name');

        $required = $this->input->checkParam(array(
            'api_key'
        ));
        if($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        $required = array();
        if(!$player_id)
            array_push($required, 'player_id');
        if($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        $validToken = $this->auth_model->createTokenFromAPIKey($this->input->get('api_key'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
        $site_id = $validToken['site_id'];
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
            'cl_player_id' => $player_id
        )));
        if(!$pb_player_id)
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        $reward_id = true;
        if($reward){
            $data_reward = array();
            $data_reward['site_id'] = $site_id;
            $data_reward['client_id'] = $validToken['client_id'];
            $data_reward['reward_name'] = $reward;
            $reward_id = $this->point_model->findPoint($data_reward);
        }
        if(!$reward_id)
            $this->response($this->error->setError('REWARD_NOT_FOUND'), 200);

        $points['points'] = $this->player_model->getPlayerPointsLog($pb_player_id, $site_id, $reward, $offset, $limit);
        foreach($points['points'] as &$point)
        {
            $action_log = $this->player_model->getActionLog($point['action_log_id'], $site_id);
            $point['action_name'] = $action_log['action_name'];
            $point['string_filter'] = $action_log['url'];
            unset($point['action_log_id']);
        }
        $this->response($this->resp->setRespond($points), 200);
    }
	////////////////
	// DEPRECATED //
	////////////////
	public function points_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		$input = array_merge($validToken, array(
			'pb_player_id' => $pb_player_id
		));
		//get player points
		$points['points'] = $this->player_model->getPlayerPoints($pb_player_id, $site_id);
		foreach($points['points'] as &$point)
		{
			$point['reward_name'] = $this->point_model->getRewardNameById(array_merge($input, array(
				'reward_id' => $point['reward_id']
			)));
			ksort($point);
		}
		$this->response($this->resp->setRespond($points), 200);
	}
	////////////////
	// DEPRECATED //
	////////////////
	public function point_post($player_id = '', $reward = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		$required = array();
		if(!$player_id)
			array_push($required, 'player_id');
		if(!$reward)
			array_push($required, 'reward');
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		$input = array_merge($validToken, array(
			'reward_name' => $reward
		));
		$reward_id = $this->point_model->findPoint($input);
		if(!$reward_id)
			$this->response($this->error->setError('REWARD_NOT_FOUND'), 200);
		$point['point'] = $this->player_model->getPlayerPoint($pb_player_id, $reward_id, $site_id);
		$point['point'][0]['reward_name'] = $reward;
		ksort($point);
		$this->response($this->resp->setRespond($point), 200);
	}
	////////////////
	// DEPRECATED //
	////////////////
	public function action_post($player_id = '', $action = '', $option = 'time')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		$required = array();
		if(!$player_id)
			array_push($required, 'player_id');
		if($required)
			$this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		$actions = array();
		if($action)
		{
			$action_id = $this->action_model->findAction(array_merge($validToken, array(
				'action_name' => $action
			)));
			if(!$action_id)
				$this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
			$actions['action'] = ($option == 'time') ? $this->player_model->getActionPerform($pb_player_id, $action_id, $site_id) : $this->player_model->getActionCount($pb_player_id, $action_id, $site_id);
		}
		else //get last action performed
		{
			if($option != 'time')
				$this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
			$actions['action'] = $this->player_model->getLastActionPerform($pb_player_id, $site_id);
		}
		$this->response($this->resp->setRespond($actions), 200);
	}
	////////////////
	// DEPRECATED //
	////////////////
	public function badge_post($player_id = '')
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$player_id)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'player_id'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		$site_id = $validToken['site_id'];
		//get playbasis player id
		$pb_player_id = $this->player_model->getPlaybasisId(array_merge($validToken, array(
			'cl_player_id' => $player_id
		)));
		if($pb_player_id < 0)
			$this->response($this->error->setError('USER_NOT_EXIST'), 200);
		//get player badge
		$badgeList = $this->player_model->getBadge($pb_player_id, $site_id);
		$this->response($this->resp->setRespond($badgeList), 200);
	}
	////////////////
	// DEPRECATED //
	////////////////
	public function rank_post($ranked_by, $limit = 20)
	{
		$required = $this->input->checkParam(array(
			'token'
		));
		if($required)
			$this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
		if(!$ranked_by)
			$this->response($this->error->setError('PARAMETER_MISSING', array(
				'ranked_by'
			)), 200);
		$validToken = $this->auth_model->findToken($this->input->post('token'));
		if(!$validToken)
			$this->response($this->error->setError('INVALID_TOKEN'), 200);
		$leaderboard = $this->player_model->getLeaderboard($ranked_by, $limit, $validToken['client_id'], $validToken['site_id']);
		$this->response($this->resp->setRespond($leaderboard), 200);
	}
    ////////////////
    // DEPRECATED //
    ////////////////
    public function ranks_post($limit = 20)
    {
        $required = $this->input->checkParam(array(
            'token'
        ));
        if($required)
            $this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
        $validToken = $this->auth_model->findToken($this->input->post('token'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
        $leaderboards = $this->player_model->getLeaderboards($limit, $validToken['client_id'], $validToken['site_id']);
        $this->response($this->resp->setRespond($leaderboards), 200);
    }
    ////////////////
    // DEPRECATED //
    ////////////////
    public function level_post($level='')
    {
        $required = $this->input->checkParam(array(
            'token'
        ));
        if($required)
            $this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
        if(!$level)
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'level'
            )), 200);
        $validToken = $this->auth_model->findToken($this->input->post('token'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_TOKEN'), 200);

        $level = $this->level_model->getLevelDetail($level, $validToken['client_id'], $validToken['site_id']);
        $this->response($this->resp->setRespond($level), 200);
    }
    ////////////////
    // DEPRECATED //
    ////////////////
    public function levels_post()
    {
        $required = $this->input->checkParam(array(
            'token'
        ));
        if($required)
            $this->response($this->error->setError('TOKEN_REQUIRED', $required), 200);
        $validToken = $this->auth_model->findToken($this->input->post('token'));
        if(!$validToken)
            $this->response($this->error->setError('INVALID_TOKEN'), 200);

        $level['levels'] = $this->level_model->getLevelsDetail($validToken['client_id'], $validToken['site_id']);
        $this->response($this->resp->setRespond($level), 200);
    }
}
?>