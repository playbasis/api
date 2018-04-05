<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';
require_once(APPPATH . 'controllers/engine.php');

class Player extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('client_model');
        $this->load->model('player_model');
        $this->load->model('tracker_model');
        $this->load->model('point_model');
        $this->load->model('action_model');
        $this->load->model('level_model');
        $this->load->model('reward_model');
        $this->load->model('quest_model');
        $this->load->model('badge_model');
        $this->load->model('goods_model');
        $this->load->model('energy_model');
        $this->load->model('email_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/utility', 'utility');
        $this->load->model('tool/respond', 'resp');
        $this->load->model('tool/node_stream', 'node');
        $this->load->model('store_org_model');
        $this->load->library('form_validation');
        $this->load->library('parser');
        $this->load->model('sms_model');
    }

    public function index_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        //read player information
        $player['player'] = $this->player_model->readPlayer($pb_player_id, $this->site_id, array(
            'username',
            'first_name',
            'last_name',
            'gender',
            'tags',
            'image',
            'exp',
            'level',
            'date_added',
            'birth_date'
        ));

        //get last login/logout
        $player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id, 'LOGIN');
        $player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id,
            'LOGOUT');
        $player['player']['cl_player_id'] = $player_id;
        $this->response($this->resp->setRespond($player), 200);
    }

    public function index_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        //read player information
        $player['player'] = $this->player_model->readPlayer($pb_player_id, $this->site_id, array(
            'username',
            'first_name',
            'last_name',
            'gender',
            'tags',
            'image',
            'email',
            'phone_number',
            'exp',
            'level',
            'date_added',
            'birth_date'
        ));

        //get last login/logout
        $player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id, 'LOGIN');
        $player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id,
            'LOGOUT');
        $player['player']['cl_player_id'] = $player_id;
        $this->response($this->resp->setRespond($player), 200);
    }

    /*public function list_get()
    {
        $required = $this->input->checkParam(array(
            'list_player_id'
        ));
        if($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        $list_player_id = explode(",", $this->input->get('list_player_id'));
        //read player information
        $player['player'] = $this->player_model->readListPlayer($list_player_id, $this->site_id, array(
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
        if(isset($_POST['list_player_id']) && $_POST['list_player_id']) {
            $list_player_id = explode(",", $this->input->post('list_player_id'));
        }else {
            $filter['tags'] = explode(",", $this->input->post('tags'));
            $player_list = $this->player_model->readPlayersWithFilter( $this->site_id, array('cl_player_id'), $filter);
            $list_player_id = array();
            foreach($player_list as $player){
                $list_player_id[] = $player['cl_player_id'];
            }
        }
        $player['player'] = array();
        //read player information
        for ($i = 0; $i < count($list_player_id); $i++) {
            $data = array(
                'client_id' => $this->validToken['client_id'],
                'site_id' => $this->site_id,
                'cl_player_id' => $list_player_id[$i]
            );
            $pb_player_id = $this->player_model->getPlaybasisId($data);

            $player['player'][] = $this->player_model->readPlayer($pb_player_id, $this->site_id, array(
                'cl_player_id',
                'username',
                'first_name',
                'last_name',
                'gender',
                'tags',
                'image',
                'email',
                'phone_number',
                'exp',
                'level',
                'date_added',
                'birth_date'
            ));
            if ($player['player'][$i]) {
                $player['player'][$i]['last_login'] = $this->player_model->getLastEventTime($pb_player_id,
                    $this->site_id, 'LOGIN');
                $player['player'][$i]['last_logout'] = $this->player_model->getLastEventTime($pb_player_id,
                    $this->site_id, 'LOGOUT');
            } else {
                $player['player'][$i]['message'] = "User '" . $list_player_id[$i] . "' doesn't exist";
            }
        }

        $this->response($this->resp->setRespond($player), 200);
    }

    public function details_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        //read player information
        $player['player'] = $this->player_model->readPlayer($pb_player_id, $this->site_id, array(
            'username',
            'first_name',
            'last_name',
            'gender',
            'tags',
            'image',
            'exp',
            'level',
            'date_added',
            'birth_date'
        ));
        //percent exp of level
//        $level = $this->level_model->getLevelDetail($player['player']['level'], $this->validToken['client_id'], $this->validToken['site_id']);
        $level = $this->level_model->getLevelByExp($player['player']['exp'], $this->validToken['client_id'],
            $this->validToken['site_id']);
        if ($level) {
            $base_exp = $level['min_exp'];
            $max_exp = $level['max_exp'] - $base_exp;
        } else {
            $base_exp = 0;
            $max_exp = 0;
        }
        $now_exp = $player['player']['exp'] - $base_exp;
        if (isset($level['max_exp']) && $max_exp != 0) {
            $percent_exp = (floatval($now_exp) * floatval(100)) / floatval($max_exp);
            $player['player']['percent_of_level'] = round($percent_exp, 2);
            if ($player['player']['percent_of_level'] >= 100) {
                $player['player']['percent_of_level'] = 99;
            }
        } else {
            $player['player']['percent_of_level'] = 100;
        }
        $player['player']['level'] = $level['level'];
        $player['player']['level_title'] = $level['level_title'];
        $player['player']['level_image'] = $level['level_image'];

        $player['player']['badges'] = $this->player_model->getBadge($pb_player_id, $this->site_id, null, true);
        $player['player']['goods'] = $this->player_model->getGoods($pb_player_id, $this->site_id);
        $points = $this->player_model->getPlayerPoints($pb_player_id, $this->site_id);
        foreach ($points as &$point) {
            $point['reward_name'] = $this->point_model->getRewardNameById(array_merge($this->validToken, array(
                'reward_id' => $point['reward_id']
            )));
            $point['reward_id'] = $point['reward_id'] . "";
            $reward_expire = $this->point_model->getPlayerRewardExpiration($this->validToken['client_id'], $this->validToken['site_id'], $pb_player_id, $point['reward_id']);
            if($reward_expire){
                $expire_sum = array_sum(array_column($reward_expire, 'current_value'));
                $expire_value = $expire_sum ? $expire_sum : 0;
                $point['value'] = $point['value'] - $expire_value;
            }
            ksort($point);
        }
        $player['player']['points'] = $points;
        //get last login/logout
        $player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id, 'LOGIN');
        $player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id,
            'LOGOUT');
        $this->response($this->resp->setRespond($player), 200);
    }

    public function details_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        //read player information
        $player['player'] = $this->player_model->readPlayer($pb_player_id, $this->site_id, array(
            'username',
            'first_name',
            'last_name',
            'gender',
            'tags',
            'image',
            'email',
            'phone_number',
            'exp',
            'level',
            'date_added',
            'birth_date'
        ));

        //percent exp of level
//        $level = $this->level_model->getLevelDetail($player['player']['level'], $this->validToken['client_id'], $this->validToken['site_id']);
        $level = $this->level_model->getLevelByExp($player['player']['exp'], $this->validToken['client_id'],
            $this->validToken['site_id']);
        if ($level) {
            $base_exp = $level['min_exp'];
            $max_exp = $level['max_exp'] - $base_exp;
        } else {
            $base_exp = 0;
            $max_exp = 0;
        }
        $now_exp = $player['player']['exp'] - $base_exp;
        if (isset($level['max_exp']) && $max_exp != 0) {
            $percent_exp = (floatval($now_exp) * floatval(100)) / floatval($max_exp);
            $player['player']['percent_of_level'] = round($percent_exp, 2);
        } else {
            $player['player']['percent_of_level'] = 100;
        }
        $player['player']['level'] = $level['level'];
        $player['player']['level_title'] = $level['level_title'];
        $player['player']['level_image'] = $level['level_image'];

        $player['player']['badges'] = $this->player_model->getBadge($pb_player_id, $this->site_id, null, true);
        $player['player']['goods'] = $this->player_model->getGoods($pb_player_id, $this->site_id);
        $points = $this->player_model->getPlayerPoints($pb_player_id, $this->site_id);
        foreach ($points as &$point) {
            $point['reward_name'] = $this->point_model->getRewardNameById(array_merge($this->validToken, array(
                'reward_id' => $point['reward_id']
            )));
            $point['reward_id'] = $point['reward_id'] . "";
            $reward_expire = $this->point_model->getPlayerRewardExpiration($this->validToken['client_id'], $this->validToken['site_id'], $pb_player_id, $point['reward_id']);
            if($reward_expire){
                $expire_sum = array_sum(array_column($reward_expire, 'current_value'));
                $expire_value = $expire_sum ? $expire_sum : 0;
                $point['value'] = $point['value'] - $expire_value;
            }
            ksort($point);
        }
        $player['player']['points'] = $points;

        $nodes_list = $this->store_org_model->getAssociatedNodeOfPlayer($this->validToken['client_id'],
            $this->validToken['site_id'], $pb_player_id);
        $organization = array();
        if (is_array($nodes_list)) {
            foreach ($nodes_list as $node) {
                $org_node = $this->store_org_model->retrieveNodeById($this->validToken['site_id'], $node['node_id']);
                $name = $org_node['name'];
                $org_info = $this->store_org_model->retrieveOrganizeById($this->validToken['client_id'],
                    $this->validToken['site_id'], $org_node['organize']);
                $node_id = (String)$node['node_id'];
                $roles = array();
                if (isset($node['roles']) && is_array($node['roles'])) {
                    foreach ($node['roles'] as $role_name => $date_join) {
                        array_push($roles,
                            array('role' => $role_name, 'join_date' => datetimeMongotoReadable($date_join)));
                    }
                }
                if (empty($roles)) {
                    $roles = null;
                }
                array_push($organization, array(
                    'name' => $name,
                    'node_id' => $node_id,
                    'organize_type' => $org_info['name'],
                    'roles' => $roles
                ));
            }
        }
        $player['player']['organization'] = empty($organization) ? null : $organization;

        //get last login/logout
        $player['player']['last_login'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id, 'LOGIN');
        $player['player']['last_logout'] = $this->player_model->getLastEventTime($pb_player_id, $this->site_id,
            'LOGOUT');
        $this->response($this->resp->setRespond($player), 200);
    }

    public function status_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        //read player information
        $player['player'] = $this->player_model->readPlayer($pb_player_id, $this->site_id, array('status'));
        $this->response($this->resp->setRespond($player), 200);
    }

    public function register_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        if (!$this->validClPlayerId($player_id)) {
            $this->response($this->error->setError('USER_ID_INVALID'), 200);
        }

        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));

        if ($pb_player_id) {
            $this->response($this->error->setError('USER_ALREADY_EXIST'), 200);
        }

        $playerInfo = array(
            'email' => $this->input->post('email'),
            'image' => $this->input->post('image') ? $this->input->post('image') : "https://www.pbapp.net/images/default_profile.jpg",
            'username' => $this->input->post('username'),
            'player_id' => $player_id
        );

        //check if username is already exist in this site
        $player = $this->player_model->getPlayerByUsername($this->site_id, $playerInfo['username']);
        if ($player) {
            $this->response($this->error->setError('USERNAME_ALREADY_EXIST'), 200);
        }

        //check if email is already exist in this site
        $player = $this->player_model->getPlayerByEmail($this->site_id, $playerInfo['email']);
        if ($player) {
            $this->response($this->error->setError('EMAIL_ALREADY_EXIST'), 200);
        }

        $firstName = $this->input->post('first_name');
        if ($this->utility->is_not_empty($firstName)) {
            $playerInfo['first_name'] = $firstName;
        }
        $lastName = $this->input->post('last_name');
        if ($this->utility->is_not_empty($lastName)) {
            $playerInfo['last_name'] = $lastName;
        }
        $nickName = $this->input->post('nickname');
        if ($this->utility->is_not_empty($nickName)) {
            $playerInfo['nickname'] = $nickName;
        }
        $phoneNumber = $this->input->post('phone_number');
        if ($phoneNumber) {
            if ($this->validTelephonewithCountry($phoneNumber)) {
                $playerInfo['phone_number'] = $phoneNumber;
            } else {
                $this->response($this->error->setError('USER_PHONE_INVALID'), 200);
            }
        }
        $playerInfo['tags'] = $this->input->post('tags') && !is_null($this->input->post('tags')) ? explode(',', $this->input->post('tags')) : null;
        $facebookId = $this->input->post('facebook_id');
        if ($facebookId) {
            $playerInfo['facebook_id'] = $facebookId;
        }
        $twitterId = $this->input->post('twitter_id');
        if ($twitterId) {
            $playerInfo['twitter_id'] = $twitterId;
        }
        $instagramId = $this->input->post('instagram_id');
        if ($instagramId) {
            $playerInfo['instagram_id'] = $instagramId;
        }
        if ($this->password_validation($this->validToken['client_id'], $this->validToken['site_id'],
            $playerInfo['username'])
        ) {
            $this->player_model->unlockPlayer($this->validToken['site_id'], $pb_player_id);
            $password = $this->input->post('password');
            if ($password) {
                $playerInfo['password'] = do_hash($password);
            }
        } else {
            $this->response($this->error->setError('FORM_VALIDATION_FAILED', $this->validation_errors()[0]), 200);
        }
        $gender = $this->input->post('gender');
        if ($this->utility->is_not_empty($gender)) {
            $playerInfo['gender'] = $gender;
        }
        $birthdate = $this->input->post('birth_date');
        if ($birthdate) {
            $timestamp = strtotime($birthdate);
            $playerInfo['birth_date'] = date('Y-m-d', $timestamp);
        }
        $approve_status = $this->input->post('approve_status');
        if ($approve_status) {
            $playerInfo['approve_status'] = $approve_status;
        }
        $device_id = $this->input->post('device_id');
        if ($device_id) {
            $playerInfo['device_id'] = $device_id;
        }
        $referral_code = $this->input->post('code');
        $anonymous = $this->input->post('anonymous');

        if ($anonymous && $referral_code) {
            $this->response($this->error->setError('ANONYMOUS_CANNOT_REFERRAL'), 200);
        }

        // check referral code
        $playerA = null;
        if ($referral_code) {
            $playerA = $this->player_model->findPlayerByCode($this->validToken["site_id"], $referral_code,
                array('cl_player_id'));
            if (!$playerA) {
                $this->response($this->error->setError('REFERRAL_CODE_INVALID'), 200);
            }
        }

        //check anonymous feature depend on plan
        if ($anonymous) {
            $clientData = array(
                'client_id' => $this->validToken['client_id'],
                'site_id' => $this->validToken['site_id']
            );
            $result = $this->client_model->checkFeatureByFeatureName($clientData, "Anonymous");
            if ($result) {
                $playerInfo['anonymous'] = $anonymous;
            } else {
                $this->response($this->error->setError('ANONYMOUS_NOT_FOUND'), 200);
            }
        }

        // get plan_id
        $plan_id = $this->client_model->getPlanIdByClientId($this->validToken["client_id"]);
        try {
            $player_limit = $this->client_model->getPlanLimitById(
                $this->client_plan,
                "others",
                "player");
        } catch (Exception $e) {
            $this->response($this->error->setError('INTERNAL_ERROR'), 200);
        }

        $pb_player_id = $this->player_model->createPlayer(
            array_merge($this->validToken, $playerInfo), $player_limit);

        $platform = $this->auth_model->getOnePlatform($this->client_id, $this->site_id);

        /* trigger reward for referral program (if any) */
        if ($playerA) {

            // [rule] A invite B
            $this->utility->request('engine', 'json', http_build_query(array(
                'api_key' => $platform['api_key'],
                'pb_player_id' => $playerA['_id'] . '',
                'action' => ACTION_INVITE,
                'pb_player_id-2' => $pb_player_id . ''
            )));


            // [rule] B invited by A
            $this->utility->request('engine', 'json', http_build_query(array(
                'api_key' => $platform['api_key'],
                'pb_player_id' => $pb_player_id . '',
                'action' => ACTION_INVITED,
                'pb_player_id-2' => $playerA['_id'] . ''
            )));
        }


        $this->utility->request('engine', 'json', http_build_query(array(
            'api_key' => $platform['api_key'],
            'pb_player_id' => $pb_player_id . '',
            'action' => ACTION_REGISTER
        )));

        /* Automatically energy initialization after creating a new player*/
        foreach ($this->energy_model->findActiveEnergyRewardsById($this->validToken['client_id'],
            $this->validToken['site_id']) as $energy) {

            $energy_reward_id = $energy['reward_id'];
            $energy_max = (int)$energy['energy_props']['maximum'];
            $batch_data = array();
            if ($energy['type'] == 'gain') {
                array_push($batch_data, array(
                    'pb_player_id' => $pb_player_id,
                    'cl_player_id' => $player_id,
                    'client_id' => $this->validToken['client_id'],
                    'site_id' => $this->validToken['site_id'],
                    'reward_id' => $energy_reward_id,
                    'value' => $energy_max,
                    'date_cron_modified' => new MongoDate(),
                    'date_added' => new MongoDate(),
                    'date_modified' => new MongoDate()
                ));
            } elseif ($energy['type'] == 'loss') {
                array_push($batch_data, array(
                    'pb_player_id' => $pb_player_id,
                    'cl_player_id' => $player_id,
                    'client_id' => $this->validToken['client_id'],
                    'site_id' => $this->validToken['site_id'],
                    'reward_id' => $energy_reward_id,
                    'value' => 0,
                    'date_cron_modified' => new MongoDate(),
                    'date_added' => new MongoDate(),
                    'date_modified' => new MongoDate()
                ));
            }
            if (!empty($batch_data)) {
                $this->energy_model->bulkInsertInitialValue($batch_data);
            }
        }
        if ($pb_player_id) {
            $this->response($this->resp->setRespond(), 200);
        } else {
            $this->response($this->error->setError('LIMIT_EXCEED'), 200);
        }
    }
    public function referral_post()
    {
        $required = $this->input->checkParam(array(
            'player_id',
            'referral_code',
        ));

        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }

        $cl_player_id_B = $this->input->post('player_id');
        $referral_code = $this->input->post('referral_code');
        $client_id = $this->validToken["client_id"];
        $site_id = $this->validToken["site_id"];
        
        $pb_player_id_B = $this->player_model->getPlaybasisId(array_merge($this->validToken, array('cl_player_id' => $cl_player_id_B)));
        if (!$pb_player_id_B) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $action_id_invited = $this->action_model->findAction(array_merge($this->validToken, array('action_name' => ACTION_INVITED)));
        $action_id_invite = $this->action_model->findAction(array_merge($this->validToken, array('action_name' => ACTION_INVITE)));
        if (!$action_id_invited || !$action_id_invite) {
            $this->response($this->error->setError('REFERRAL_ACTION_INVITE_OR_INVITED_NOT_AVAILABLE'), 200);
        }

        $playerA = $this->player_model->findPlayerByCode($site_id, $referral_code, array('cl_player_id'));
        if ($playerA && ($playerA['_id'] != $pb_player_id_B)) {

            $action_count = $this->player_model->getActionCount($pb_player_id_B, $action_id_invited, $site_id);
            if ($action_count && isset($action_count['count']) && $action_count['count'] < 1){
                $platform = $this->auth_model->getOnePlatform($client_id, $site_id);


                $parameter_A = array(
                    'api_key' => $platform['api_key'],
                    'pb_player_id' => $playerA['_id'] . '',
                    'action' => ACTION_INVITE,
                    'pb_player_id-2' => $pb_player_id_B . ''
                );

                $parameter_B = array(
                    'api_key' => $platform['api_key'],
                    'pb_player_id' => $pb_player_id_B . '',
                    'action' => ACTION_INVITED,
                    'pb_player_id-2' => $playerA['_id'] . ''
                );

                $custom_params = $this->input->post();
                $private_datas = array('player_id', 'token', 'XDEBUG_SESSION_START', 'XDEBUG_TRACE');
                foreach($custom_params as $key => $value) {
                    if (!in_array($key,$private_datas)) {
                        $parameter_A[$key] = $value;
                        $parameter_B[$key] = $value;
                    }
                }

                // [rule] A invite B
                $playerA_result = $this->utility->request('engine', 'json', http_build_query($parameter_A), true);
                $playerA_result = json_decode($playerA_result[0]);

                // [rule] B invited by A
                $playerB_result = $this->utility->request('engine', 'json', http_build_query($parameter_B), true);
                $playerB_result = json_decode($playerB_result[0]);

                $this->response($this->resp->setRespond(array(
                    'inviter_response' => array(
                        'player_id' => $playerA['cl_player_id'],
                        'response' => $playerA_result->response

                    ),
                    'invitee_response' => array(
                        'player_id' => $cl_player_id_B,
                        'response' => $playerB_result->response
                    )
                )), 200);

            }else{
                $this->response($this->error->setError('REFERRAL_PLAYER_ALREADY_BE_INVITED'), 200);
            }

        } else {
            $this->response($this->error->setError('REFERRAL_CODE_INVALID'), 200);
        }

    }
    
    public function registerBatch_post()
    {
        $batch_data = json_decode($this->input->post()['batch'],true);

        try {
            $player_limit = $this->client_model->getPlanLimitById(
                $this->client_plan,
                "others",
                "player");
        } catch (Exception $e) {
            $this->response($this->error->setError('INTERNAL_ERROR'), 200);
        }

        $return = $this->player_model->bulkRegisterPlayer($batch_data, $this->validToken, $player_limit);

        if ($return) {
            $this->response($this->resp->setRespond(), 200);
        } else {
            $this->response($this->error->setError('LIMIT_EXCEED'), 200);
        }
    }

    public function update_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $playerInfo = array();
        $is_email_changed = false;
        $email = $this->input->post('email');
        if ($email) {
            // check whether the email is changed
            if($email != $this->player_model->getEmail($this->site_id, $pb_player_id)){
                $is_email_changed = true;
            }

            //check if new email is already exist in this site
            $player = $this->player_model->getPlayerByEmailButNotID($this->site_id, $email, $pb_player_id);
            if ($player) {
                $this->response($this->error->setError('EMAIL_ALREADY_EXIST'), 200);
            }
            $playerInfo['email'] = $email;
        }
        $image = $this->input->post('image');
        if ($image) {
            $playerInfo['image'] = $image;
        }
        $username = $this->input->post('username');
        if ($this->utility->is_not_empty($username)) {
            //check if new username is already exist in this site
            $player = $this->player_model->getPlayerByUsernameButNotID($this->site_id, $username, $pb_player_id);
            if ($player) {
                $this->response($this->error->setError('USERNAME_ALREADY_EXIST'), 200);
            }
            $playerInfo['username'] = $username;
        }
        $exp = $this->input->post('exp');
        if (is_numeric($exp)) {
            $playerInfo['exp'] = intval($exp);
        }
        $level = $this->input->post('level');
        if (is_numeric($level)) {
            $playerInfo['level'] = intval($level);
        }
        $firstName = $this->input->post('first_name');
        if ($this->utility->is_not_empty($firstName)) {
            $playerInfo['first_name'] = $firstName;
        }
        $lastName = $this->input->post('last_name');
        if ($this->utility->is_not_empty($lastName)) {
            $playerInfo['last_name'] = $lastName;
        }
        $nickName = $this->input->post('nickname');
        if ($this->utility->is_not_empty($nickName)) {
            $playerInfo['nickname'] = $nickName;
        }
        $phoneNumber = $this->input->post('phone_number');
        if ($phoneNumber) {
            if ($this->validTelephonewithCountry($phoneNumber)) {
                $playerInfo['phone_number'] = $phoneNumber;
            } else {
                $this->response($this->error->setError('USER_PHONE_INVALID'), 200);
            }
        }
        if ($this->input->post('tags')){
            if(strtolower($this->input->post('tags')) == "null"){
                $playerInfo['tags'] = null;
            }else{
                $playerInfo['tags'] = explode(',', $this->input->post('tags'));
            }
        }
        $facebookId = $this->input->post('facebook_id');
        if ($facebookId) {
            $playerInfo['facebook_id'] = $facebookId;
        }
        $twitterId = $this->input->post('twitter_id');
        if ($twitterId) {
            $playerInfo['twitter_id'] = $twitterId;
        }
        $instagramId = $this->input->post('instagram_id');
        if ($instagramId) {
            $playerInfo['instagram_id'] = $instagramId;
        }
        $deviceId = $this->input->post('device_id');
        if ($deviceId) {
            $playerInfo['device_id'] = $deviceId;
        }
        $password = $this->input->post('password');
        if ($password) {
            if (!isset($username) || $username == '') {
                $username = $this->player_model->readPlayer($pb_player_id, $this->site_id,
                    array('username'))['username'];
            }
            if ($this->password_validation($this->validToken['client_id'], $this->validToken['site_id'], $username)) {
                $this->player_model->unlockPlayer($this->validToken['site_id'], $pb_player_id);
                $playerInfo['password'] = do_hash($password);
            } else {
                $this->response($this->error->setError('FORM_VALIDATION_FAILED', $this->validation_errors()), 200);
            }
        }

        $gender = $this->input->post('gender');
        if ($this->utility->is_not_empty($gender)) {
            $playerInfo['gender'] = intval($gender);
        }
        $birthdate = $this->input->post('birth_date');
        if ($birthdate) {
            $timestamp = strtotime($birthdate);
            $playerInfo['birth_date'] = date('Y-m-d', $timestamp);
        }
        $approve_status = $this->input->post('approve_status');
        if ($approve_status) {
            $playerInfo['approve_status'] = $approve_status;
        }
        // unset email_verify flag if player's email is changed
        if($is_email_changed){
            $playerInfo['email_verify'] = false;
        }

        $this->player_model->updatePlayer($pb_player_id, $this->validToken['site_id'], $playerInfo);
        $this->response($this->resp->setRespond(), 200);
    }

    public function custom_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        //read player information
        $player['player'] = $this->player_model->readPlayer($pb_player_id, $this->site_id, array(
            'custom',
        ));
        if(isset($player['player']['custom'])){
            foreach($player['player']['custom'] as $custom_index => $custom){
                if(is_numeric($custom) && !is_string($custom)){
                    unset($player['player']['custom'][$custom_index]);
                }
            }
        }
        $this->response($this->resp->setRespond($player), 200);
    }

    public function custom_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $playerInfo = array();
        $key = $this->input->post('key');
        if ($key) {
            $playerInfo['custom'] = array();
            $keys = str_getcsv($this->input->post('key'));
            $values = str_getcsv($this->input->post('value'));
            foreach ($keys as $i => $key) {
                $playerInfo['custom'][$key] = isset($values[$i]) ? $values[$i] : null;
            }
        }

        // Add _numeric for numeric value
        if (is_array($playerInfo['custom'])) {
            foreach ($playerInfo['custom'] as $name => $value) {
                $value = str_replace(',', '', $value);
                if (is_numeric($value)) {
                    $playerInfo['custom'][$name . POSTFIX_NUMERIC_PARAM] = floatval($value);
                }
            }
        }

        $this->player_model->updatePlayer($pb_player_id, $this->validToken['site_id'], $playerInfo);
        $this->response($this->resp->setRespond(), 200);
    }

    public function delete_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $this->player_model->deletePlayer($pb_player_id, $this->validToken['site_id']);
        $this->response($this->resp->setRespond(), 200);
    }

    public function login_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        //check anonymous user
        $anonymousFeature = $this->client_model->checkFeatureByFeatureName($this->validToken, "Anonymous");
        if ($anonymousFeature) {
            $sessions = $this->player_model->findBySessionId($this->client_id, $this->site_id,
                $this->input->post('session_id'), true);
            if (count($sessions) > 0) {
                $anonymousUser = $this->player_model->isAnonymous($this->client_id, $this->site_id, null,
                    $sessions['pb_player_id']);
                if ($anonymousUser) {
                    $this->mongo_db->where('pb_player_id', $sessions['pb_player_id']);
                    $action_logs = $this->mongo_db->get('playbasis_action_log');
                    foreach ($action_logs as $action_log) {
                        $engine = new Engine();
                        $input = array_merge($this->validToken, array(
                            'pb_player_id' => $pb_player_id,
                            'action_id' => $action_log['action_id'],
                            'action_name' => $action_log['action_name'],
                            'url' => $action_log['url'],
                            'date_added' => $action_log['date_added'],
                            'test' => false
                        ));
                        $engine->processRule($input, $this->validToken, null, null, $action_log['date_added']);
                        $this->player_model->deletePlayer($sessions['pb_player_id'], $this->validToken['site_id']);
                    }
                }
            }
        }

        //trigger and log event
        $eventMessage = $this->utility->getEventMessage('login');
        $this->tracker_model->trackEvent('LOGIN', $eventMessage, array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'pb_player_id' => $pb_player_id,
            'action_log_id' => null
        ));
        //publish to node stream
        $this->node->publish(array(
            'pb_player_id' => $pb_player_id,
            'action_name' => 'login',
            'action_icon' => 'fa-sign-in',
            'message' => $eventMessage
        ), $this->validToken['site_name'], $this->validToken['site_id']);

        /* Optionally, keep track of session */
        $session_id = $this->input->post('session_id');
        $session_expires_in = $this->input->post('session_expires_in');
        if (!$session_expires_in) {
            $setting = $this->player_model->getSecuritySetting($this->client_id, $this->site_id);
            $timeout = (isset($setting['timeout'])) ? ($setting['timeout'] > 0 ? $setting['timeout'] : 0) : false;
            $session_expires_in = $timeout;
        }

        if ($session_id) {
            $this->player_model->login($this->client_id, $this->site_id, $pb_player_id, $session_id,
                $session_expires_in);
        }

        // [rule] login
        $platform = $this->auth_model->getOnePlatform($this->client_id, $this->site_id);
        $this->utility->request('engine', 'json', http_build_query(array(
            'api_key' => $platform['api_key'],
            'pb_player_id' => $pb_player_id . '',
            'action' => ACTION_LOGIN,
        )));

        $this->response($this->resp->setRespond(), 200);
    }

    public function logout_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        //trigger and log event
        $eventMessage = $this->utility->getEventMessage('logout');
        $this->tracker_model->trackEvent('LOGOUT', $eventMessage, array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id'],
            'pb_player_id' => $pb_player_id,
            'action_log_id' => null
        ));
        //publish to node stream
        $this->node->publish(array(
            'pb_player_id' => $pb_player_id,
            'action_name' => 'logout',
            'action_icon' => 'fa-sign-out',
            'message' => $eventMessage
        ), $this->validToken['site_name'], $this->validToken['site_id']);

        /* Optionally, remove session */
        $session_id = $this->input->post('session_id');
        if ($session_id) {
            $this->player_model->logout($this->client_id, $this->site_id, $session_id);
        }

        // [rule] logout
        $platform = $this->auth_model->getOnePlatform($this->client_id, $this->site_id);
        $this->utility->request('engine', 'json', http_build_query(array(
            'api_key' => $platform['api_key'],
            'pb_player_id' => $pb_player_id . '',
            'action' => ACTION_LOGOUT,
        )));

        $this->response($this->resp->setRespond(), 200);
    }

    public function sessions_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        /* List all active sessions of the player */
        $sessions = $this->player_model->listSessions($this->client_id, $this->site_id, $pb_player_id);

        $this->response($this->resp->setRespond($sessions), 200);
    }

    public function session_get($session_id = '')
    {
        if (!$session_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'session_id'
            )), 200);
        }

        /* Find a player given login session ID */
        $session = $this->player_model->findBySessionId($this->client_id, $this->site_id, $session_id);
        if (!$session) {
            $this->response($this->error->setError('SESSION_NOT_VALID'), 200);
        }
        $player = $this->player_model->readPlayer($session['pb_player_id'], $this->site_id, array(
            'cl_player_id',
            'username',
            'first_name',
            'last_name',
            'gender',
            'image',
            'exp',
            'level',
            'date_added',
            'birth_date'
        ));

        $this->response($this->resp->setRespond($player), 200);
    }

    public function auth_post()
    {
        $username = $this->input->post('username');
        $email = $this->input->post('email');
        $password = do_hash($this->input->post('password'));

        $player = null;
        if ($email) {
            $player = $this->player_model->getPlayerByEmail($this->site_id, $email);
        } elseif ($username && is_null($player)) {
            $player = $this->player_model->getPlayerByUsername($this->site_id, $username);
        }
        
        if(!$player){
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        } elseif ( !isset($player['approve_status']) || $player['approve_status'] != "approved") {
            $this->response($this->error->setError('ACCOUNT_NOT_APPROVED', array('cl_player_id' => $player['cl_player_id'])), 200);
        } elseif (isset($player['locked']) && $player['locked']) {
            $this->response($this->error->setError('ACCOUNT_IS_LOCKED'), 200);
        }

        $setting = $this->player_model->getSecuritySetting($this->client_id, $this->site_id);
        if (isset($setting['max_retries']) && ($setting['max_retries'] > 0) && ($player['login_attempt'] >= $setting['max_retries'])) {
            $this->player_model->lockPlayer($this->site_id, $player['_id']);
            $this->response($this->error->setError('ACCOUNT_IS_LOCKED'), 200);
        }
        if (isset($setting['email_verification_enable']) && ($setting['email_verification_enable'])){
            if(!isset($player['email_verify']) || $player['email_verify'] != true){
                $this->response($this->error->setError('EMAIL_NOT_VERIFIED'), 200);
            }
        }

        $auth = $this->player_model->authPlayer($this->site_id, $player['_id'], $password);
        if (!$auth) {
            $this->player_model->increaseLoginAttempt($this->site_id, $player['_id']);
            $this->response($this->error->setError('AUTHENTICATION_FAIL'), 200);
        } else {
            $list_device_tokens = array_map('index_device_token', $this->player_model->listDevices(
                $this->client_id, $this->site_id, $player['_id']));

            $device_token = $this->input->post('device_token');
            if (!empty($device_token)) {
                // Change new device
                // Send SMS verification if device_token is a new one (not in a list of existing device tokens)
                if (!in_array($device_token, $list_device_tokens, true) && !empty($player['phone_number'])) {
                    $this->response($this->error->setError('SMS_VERIFICATION_REQUIRED'), 200);
                    // Otherwise, sent warning if phone number not found
                } elseif (empty($player['phone_number'])) {
                    $this->response($this->error->setError('SMS_VERIFICATION_PHONE_NUMBER_NOT_FOUND'), 200);
                }
                $this->player_model->increaseLoginAttempt($this->client_id, $this->site_id);
            }
        }

        $this->player_model->resetLoginAttempt($this->site_id, $player['_id']);
        //trigger and log event
        $eventMessage = $this->utility->getEventMessage('login');
        $this->tracker_model->trackEvent('LOGIN', $eventMessage, array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'pb_player_id' => $player['_id'],
            'action_log_id' => null
        ));
        //publish to node stream
        $this->node->publish(array(
            'pb_player_id' => $player['_id'],
            'action_name' => 'login',
            'action_icon' => 'fa-sign-in',
            'message' => $eventMessage
        ), $this->validToken['site_name'], $this->validToken['site_id']);

        /* Optionally, keep track of session */
        $session_id = get_random_code(40, true, true, true);

        $timeout = (isset($setting['timeout'])) ? ($setting['timeout'] > 0 ? $setting['timeout'] : 0) : PLAYER_AUTH_SESSION_TIMEOUT;
        $session_expires_in = $timeout;
        if ($session_id) {
            $this->player_model->login($this->client_id, $this->site_id, $player['_id'], $session_id,
                $session_expires_in);
        }

        $platform = $this->auth_model->getOnePlatform($this->client_id, $this->site_id);
        $this->utility->request('engine', 'json', http_build_query(array(
            'api_key' => $platform['api_key'],
            'pb_player_id' => $player['_id'] . '',
            'action' => ACTION_LOGIN,
        )));

        $this->response($this->resp->setRespond(array(
            'cl_player_id' => $player['cl_player_id'],
            'session_id' => $session_id
        )), 200);
    }

    public function verifyOTPCode_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        $player = $this->player_model->getPlayerByPlayerId($this->site_id, $player_id);
        if (!$player) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $code = $this->input->post('code');
        $result = $this->player_model->getPlayerOTPCode($player['_id'], $code);
        if (!$result) {
            $this->response($this->error->setError('SMS_VERIFICATION_CODE_INVALID'), 200);
        }

        if ($result['date_expire']->sec <= time()) {
            $this->response($this->error->setError('SMS_VERIFICATION_CODE_EXPIRED'), 200);
        }

        if(isset($result['phone_number']) && !empty($result['phone_number'])){ // there is pending phone_number
            $this->player_model->updatePlayer($player['_id'], $this->validToken['site_id'], array(
                'phone_number' => $result['phone_number']
            ));
        }

        if(isset($result['device_token']) && !empty($result['device_token'])){ // there is pending device_token
            $result1 = $this->player_model->storeDeviceToken(array(
                'client_id' => $this->client_id,
                'site_id' => $this->site_id,
                'pb_player_id' => $player['_id'],
                'device_token' => $result['device_token'],
                'device_description' => $result['device_description'],
                'device_name' =>$result['device_name'],
                'os_type' => $result['os_type']
            ));
            if (!$result1) {
                $this->response($this->error->setError('INTERNAL_ERROR'), 200);
            }
        }
        $this->player_model->deleteOTPCode($result['code']);

        $this->response($this->resp->setRespond(), 200);
    }

    public function forgotPasswordEmail_post()
    {
        $email = $this->input->post('email');
        $player = $this->player_model->getPlayerByEmail($this->site_id, $email);
        if (!$player) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        // generate password key
        $random_key = $this->player_model->generatePasswordResetCode($player['_id']);
        $site_data = $this->client_model->findBySiteId($this->site_id);

        // send email
        /* before send, check whether custom domain was set by user or not*/
        $from = get_verified_custom_domain($this->client_id, $this->site_id);
        $to = $email;
        $subject = 'Reset Your Password';
        $html = $this->parser->parse('player_forgotpassword.html', array(
            'firstname' => $player['first_name'],
            'lastname' => $player['last_name'],
            'username' => $player['username'],
            'site_logo' => (isset($site_data['image']) && !empty($site_data['image'])) ? S3_IMAGE.$site_data['image'] : "https://www.pbapp.net/images/playbasis-logo.jpg",
            'url' => $this->config->item('CONTROL_DASHBOARD_URL') . 'player/password/reset/'.$random_key
        ), true);
        $response = $this->utility->email($from, $to, $subject, $html);
        $this->email_model->log(EMAIL_TYPE_USER, $this->client_id, $this->site_id, $response,
            $from, $to, $subject, $html);
        $this->response($this->resp->setRespond(array('success' => true)), 200);
    }

    public function emailVerify_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        $player = $this->player_model->getPlayerByPlayerId($this->site_id, $player_id);
        if (!$player) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        // generate password key
        $random_key = $this->player_model->generateEmailVerifyCode($player['_id']);
        $site_data = $this->client_model->findBySiteId($this->site_id);

        // send email
        /* before send, check whether custom domain was set by user or not*/
        $from = get_verified_custom_domain($this->client_id, $this->site_id);
        $to = $player['email'];
        $subject = 'Verify Your Email';
        $html = $this->parser->parse('player_verifyemail.html', array(
            'firstname' => $player['first_name'],
            'lastname' => $player['last_name'],
            'site_logo' => (isset($site_data['image']) && !empty($site_data['image'])) ? S3_IMAGE.$site_data['image'] : "https://www.pbapp.net/images/playbasis-logo.jpg",
            'url' => $this->config->item('CONTROL_DASHBOARD_URL') . 'player/email/verify/'.$random_key
        ), true);
        $response = $this->utility->email($from, $to, $subject, $html);
        $this->email_model->log(EMAIL_TYPE_USER, $this->client_id, $this->site_id, $response,
            $from, $to, $subject, $html);
        $this->response($this->resp->setRespond(array('success' => true,'message' => 'Verification message was sent to your email. Please check it.')), 200);
    }

    public function points_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $input = array_merge($this->validToken, array(
            'pb_player_id' => $pb_player_id
        ));
        //get player points
        $points['points'] = $this->player_model->getPlayerPoints($pb_player_id, $this->site_id);
        foreach ($points['points'] as &$point) {
            $point['reward_name'] = $this->point_model->getRewardNameById(array_merge($input, array(
                'reward_id' => $point['reward_id']
            )));
            $point['reward_id'] = $point['reward_id'] . "";
            $reward_expire = $this->point_model->getPlayerRewardExpiration($this->validToken['client_id'], $this->validToken['site_id'], $pb_player_id, $point['reward_id']);
            if($reward_expire){
                $expire_sum = array_sum(array_column($reward_expire, 'current_value'));
                $expire_value = $expire_sum ? $expire_sum : 0;
                $point['value'] = $point['value'] - $expire_value;
            }
            ksort($point);
        }
        $this->response($this->resp->setRespond($points), 200);
    }

    public function point_get($player_id = '', $reward = '')
    {
        $required = array();
        if (!$player_id) {
            array_push($required, 'player_id');
        }
        if (!$reward) {
            array_push($required, 'reward');
        }
        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $input = array_merge($this->validToken, array(
            'reward_name' => $reward
        ));
        $reward_id = $this->point_model->findPoint($input);
        if (!$reward_id) {
            $this->response($this->error->setError('REWARD_NOT_FOUND'), 200);
        }
        $point['point'] = $this->player_model->getPlayerPoint($pb_player_id, $reward_id, $this->site_id);
        if(isset($point['point'][0]['value'])){
            $reward_expire = $this->point_model->getPlayerRewardExpiration($this->validToken['client_id'], $this->validToken['site_id'], $pb_player_id, $reward_id);
            if($reward_expire){
                $expire_sum = array_sum(array_column($reward_expire, 'current_value'));
                $expire_value = $expire_sum ? $expire_sum : 0;
                $point['point'][0]['value'] = $point['point'][0]['value'] - $expire_value;
            }
        } else {
            $point['point'][0]['value'] = 0;
        }
        $point['point'][0]['reward_id'] = $reward_id . "";
        $point['point'][0]['reward_name'] = $reward;
        ksort($point);
        $this->response($this->resp->setRespond($point), 200);
    }

    public function pointLastUsed_get($player_id = '', $reward = '')
    {
        $required = array();
        if (!$player_id) {
            array_push($required, 'player_id');
        }
        if (!$reward) {
            array_push($required, 'reward');
        }
        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $input = array_merge($this->validToken, array(
            'reward_name' => $reward
        ));
        $reward_id = $this->point_model->findPoint($input);
        if (!$reward_id) {
            $this->response($this->error->setError('REWARD_NOT_FOUND'), 200);
        }
        $point['point'] = $this->player_model->getLastUsedPoint($pb_player_id, $reward_id, $this->site_id);
        $point['point'][0]['reward_id'] = $reward_id . "";
        $point['point'][0]['reward_name'] = $reward;
        $point['point'][0]['reward_last_used'] = datetimeMongotoReadable($point['point'][0]['date_modified']);
        unset($point['point'][0]['date_modified']);
        ksort($point);
        $this->response($this->resp->setRespond($point), 200);
    }

    public function pointLastCronModified_get($player_id = '', $reward = '')
    {
        $required = array();
        if (!$player_id) {
            array_push($required, 'player_id');
        }
        if (!$reward) {
            array_push($required, 'reward');
        }
        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $input = array_merge($this->validToken, array(
            'reward_name' => $reward
        ));
        $reward_id = $this->point_model->findPoint($input);
        if (!$reward_id) {
            $this->response($this->error->setError('REWARD_NOT_FOUND'), 200);
        }
        $point['point'] = $this->player_model->getLastCronModifiedPoint($pb_player_id, $reward_id, $this->site_id);
        $point['point'][0]['reward_id'] = $reward_id . "";
        $point['point'][0]['reward_name'] = $reward;
        $point['point'][0]['reward_last_filled'] = isset($point['point'][0]['date_cron_modified']) ? datetimeMongotoReadable($point['point'][0]['date_cron_modified']) : null;
        unset($point['point'][0]['date_cron_modified']);
        ksort($point);
        $this->response($this->resp->setRespond($point), 200);
    }

    public function point_history_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $offset = ($this->input->get('offset')) ? $this->input->get('offset') : 0;
        $limit = ($this->input->get('limit')) ? $this->input->get('limit') : RETURN_LIMIT_FOR_RANK;
        if ($limit > 500) {
            $limit = 500;
        }
        $reward_name = $this->input->get('point_name');

        $reward = array(
            'site_id' => $this->site_id,
            'client_id' => $this->validToken['client_id'],
            'reward_name' => $reward_name
        );

        if ($reward) {
            $reward_id = $this->point_model->findPoint($reward);
        } else {
            $reward_id = null;
        }

        $respondThis['points'] = $this->player_model->getPointHistoryFromPlayerID($pb_player_id, $this->site_id,
            $reward_id, $offset, $limit, $this->input->get('order'));

        $this->response($this->resp->setRespond($respondThis), 200);
    }

    public function quest_reward_history_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $offset = ($this->input->get('offset')) ? $this->input->get('offset') : 0;
        $limit = ($this->input->get('limit')) ? $this->input->get('limit') : RETURN_LIMIT_FOR_RANK;
        if ($limit > 500) {
            $limit = 500;
        }

        $respondThis['rewards'] = $this->quest_model->getRewardHistoryFromPlayerID($this->client_id, $this->site_id,
            $pb_player_id, $offset, $limit);
        array_walk_recursive($respondThis, array($this, "convert_mongo_object"));

        $this->response($this->resp->setRespond($respondThis), 200);
    }

    public function action_get($player_id = '', $action = '', $option = 'time')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $actions = array();
        if ($action) {
            $action_id = $this->action_model->findAction(array_merge($this->validToken, array(
                'action_name' => urldecode($action)
            )));
            if (!$action_id) {
                $this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
            }
            if($this->input->get('key') && $this->input->get('value')){
                $key = explode(',', $this->input->get('key'));
                $value =  explode(',', $this->input->get('value'));
                if (sizeof($key) != sizeof($value)){
                    $this->response($this->error->setError('SIZE_KEY_VAL_NOT_MATCH'), 200);
                }
            }
            $actions['action'] = ($option == 'time') ? $this->player_model->getActionPerform($pb_player_id, $action_id, $this->site_id) : 
                                                       $this->player_model->getActionCount($pb_player_id, $action_id, $this->site_id,
                                                           isset($key) && $key ? $key : null , isset($value) && $value ? $value : null);
        } else //get last action performed
        {
            if ($option != 'time') {
                $this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
            }
            $actions['action'] = $this->player_model->getLastActionPerform($pb_player_id, $this->site_id);
        }
        $this->response($this->resp->setRespond($actions), 200);
    }

    public function action_history_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        
        
        $data = array(
            'client_id' => new MongoId($this->validToken['client_id']),
            'site_id' => new MongoId($this->validToken['site_id']),
            'cl_player_id' => $player_id,
        );
        if($this->input->get('action_name')){
            $action_name = explode(',',$this->input->get('action_name'));
            foreach ($action_name as $item) {
                $action_id = $this->action_model->findAction(array_merge($this->validToken, array(
                    'action_name' => $item
                )));
                if (!$action_id) {
                    $this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
                }
            }
            
            $data['action_name'] = $action_name;
        }
        
        if($this->input->get('date_start')){
            $data['date_added']['$gte'] = new MongoDate(strtotime($this->input->get('date_start')));
        }
        if($this->input->get('date_end')){
            $data['date_added']['$lte'] = new MongoDate(strtotime($this->input->get('date_end') . " 23:59:59"));
        }
        if($this->input->get('offset')){
            $data['offset'] = $this->input->get('offset');
        }
        if($this->input->get('limit')){
            $data['limit'] = $this->input->get('limit');
        }
        
        $result = $this->player_model->getActionHistoryDetail($data);
        foreach ($result as &$value){
            unset($value['_id']);
            $value['date_added'] = datetimeMongotoReadable($value['date_added']);
        }
        $this->response($this->resp->setRespond($result), 200);
    }

    public function giveGift_post($sent_player_id, $gift_type){
        $client_id = new MongoId($this->validToken['client_id']);
        $site_id = new MongoId($this->validToken['site_id']);
        $site_name = $this->validToken['site_name'];
        $gift_id = new MongoId($this->input->post('gift_id'));
        $gift_value = $this->input->post('amount');
        $received_player_id = $this->input->post('received_player_id');
        $gift_type = strtoupper($gift_type);
        $gift_data = array();

        $sent_pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array('cl_player_id' => $sent_player_id)));
        if (!$sent_pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $received_pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array('cl_player_id' => $received_player_id)));
        if (!$received_pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        if($gift_type == "BADGE"){
            $gift_data['before'] = $this->player_model->checkPlayerWithEnoughBadge($client_id, $site_id, $sent_pb_player_id, $gift_id, $gift_value);
            $gift_data['gift'] = $this->badge_model->getBadge(array("client_id" => $client_id, "site_id" => $site_id, "badge_id" => $gift_id));
        }
        elseif($gift_type == "GOODS"){
            $gift_data['before'] = $this->player_model->checkPlayerWithEnoughGoods($client_id, $site_id, $sent_pb_player_id, $gift_id, $gift_value);
            $gift_data['gift'] = $this->goods_model->getGoods(array("client_id" => $client_id, "site_id" => $site_id, "goods_id" => $gift_id));
        }
        elseif($gift_type == "CUSTOM_POINT"){
            $gift_data['before'] = $this->player_model->checkPlayerWithEnoughPoint($client_id, $site_id, $sent_pb_player_id, $gift_id, $gift_value);
            $gift_data['gift']['name'] = $this->point_model->getRewardNameById(array("client_id" => $client_id, "site_id" => $site_id, "reward_id" => $gift_id));
        }

        if(!$gift_data['gift']){
            $this->response($this->error->setError('GIFT_NOT_EXIST'), 200);
        }

        if(!$gift_data['before']){
            $this->response($this->error->setError('GIFT_NOT_ENOUGH'), 200);
        }


        $status = $this->player_model->giveGift($client_id, $site_id, $sent_pb_player_id, $received_pb_player_id, $received_player_id, $gift_id, $gift_type, $gift_value,$gift_data);
        $gift_data['after']['gift_name'] = $gift_data['gift']['name'];
        $gift_data['after']['type'] = $gift_type;
        $gift_data['after']['remaining'] = $gift_data['before']['value'] - $gift_value;

        if($status) {
            $event = array(
                'event_type' => 'GIFT_RECEIVED',
                'gift_type' => strtolower($gift_type),
                'gift_data' => $gift_data['gift'],
                'value' => $gift_value
            );

            //log event - reward, badge
            $data_reward = array(
                'gift_type' => $gift_type,
                'gift_id' => $gift_id,
                'gift_name' => $event['gift_data']['name'],
                'gift_value' => $gift_value,
            );
            if(isset($gift_data['gift']['group'])){
                $data_reward['group'] = $gift_data['gift']['group'];
            }
            if(isset($gift_data['gift']['code'])){
                $data_reward['code'] = $gift_data['gift']['code'];
            }

            $this->trackGift($sent_pb_player_id, $sent_player_id, $received_pb_player_id, $client_id, $site_id, $data_reward, $received_player_id);

            $eventMessage = $this->utility->getEventMessage('gift', $gift_value, $event['gift_data']['name'], $event['gift_data']['name'], '', '',$event['gift_data']['name'], $sent_player_id);

            if ($gift_type == "GOODS"){
                $validToken = array(
                    'client_id' =>$client_id,
                    'site_id' =>$site_id,
                    'pb_player_id' => $received_pb_player_id,
                    'cl_player_id' => $received_player_id,
                    'goods_id' => $gift_id,
                    'goods_name' => isset($gift_data['gift']['name']) ? $gift_data['gift']['name'] : "",
                    'code' => isset($gift_data['gift']['code']) ? $gift_data['gift']['code'] : null,
                    'is_sponsor' => isset($gift_data['gift']['sponsor']) ? $gift_data['gift']['sponsor'] : false,
                    'amount' => intval($gift_value),
                    'date_expire' => isset($gift_data['before']['date_expire']) ? $gift_data['before']['date_expire']: null,
                    'redeem' => isset($gift_data['gift']['redeem']) ? $gift_data['gift']['redeem'] : null,
                    'group' => isset($gift_data['gift']['group']) ? $gift_data['gift']['group'] : null,
                    'action_name' => 'redeem_goods',
                    'action_icon' => 'fa-icon-shopping-cart',
                    'message' => $eventMessage,
                    'status' => 'receiver',
                    'sender_id' => $sent_pb_player_id
                );
                // log event - goods
                $this->tracker_model->trackGoods($validToken);
                $this->tracker_model->trackGoodsStatus($client_id, $site_id, $sent_pb_player_id, $gift_id, "sender", $received_pb_player_id);
            }
            //publish to node stream
            $this->node->publish(array(
                "client_id" => $client_id,
                "site_id" => $site_id,
                "pb_player_id" => $received_pb_player_id,
                "player_id" => $received_player_id,
                'action_name' => 'gift',
                'action_icon' => 'fa-gift',
                'message' => $eventMessage,
                strtolower($gift_type) => $event['gift_data'],
            ), $site_name, $site_id);
        }
        $this->response($this->resp->setRespond($gift_data['after']), 200);
    }

    private function trackGift($sent_pb_player_id, $sent_player_id, $received_pb_player_id, $client_id, $site_id, $data_reward, $received_player_id)
    {
        $eventMessage = $this->utility->getEventMessage('gift', $data_reward['gift_value'], $data_reward['gift_name'],$data_reward['gift_name'], '', '',$data_reward['gift_name'], $sent_player_id);
        $data = array(
            'sent_pb_player_id' => $sent_pb_player_id,
            'sent_cl_player_id' => $sent_player_id,
            'pb_player_id' => $received_pb_player_id,
            'cl_player_id' => $received_player_id,
            'client_id' => $client_id,
            'site_id' => $site_id,
            'reward_type' => $data_reward['gift_type'],
            'reward_id' => $data_reward['gift_id'],
            'reward_name' => $data_reward['gift_name'],
            'amount' => $data_reward['gift_value'],
            'message' => $eventMessage
        );
        if(isset($data_reward['group'])){
            $data['group'] = $data_reward['group'];
        }
        if(isset($data_reward['code'])){
            $data['code'] = $data_reward['code'];
        }
        $this->tracker_model->trackGift($data);
    }

    public function badge_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        //get player badge
        $badgeList = $this->player_model->getBadge($pb_player_id, $this->site_id, $this->input->get('tags') ? explode(',', $this->input->get('tags')) : null, true);
        $this->response($this->resp->setRespond($badgeList), 200);
    }

    public function badgeAll_get($player_id = '')
    {
        $pb_player_id = null;
        if ($player_id) {
            $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
                'cl_player_id' => $player_id
            )));
            if (!$pb_player_id) {
                $this->response($this->error->setError('USER_NOT_EXIST'), 200);
            }
        }
        $badges = $this->badge_model->getAllBadges(array_merge($this->validToken, array(
            'tags' => $this->input->get('tags') ? explode(',', $this->input->get('tags')) : null
        )), true);
        if ($badges && $pb_player_id) {
            foreach ($badges as &$badge) {
                $c = $this->player_model->getBadgeCount($this->site_id, $pb_player_id, new MongoId($badge['badge_id']));
                $badge['amount'] = $c;
            }
        }
        $this->response($this->resp->setRespond($badges), 200);
    }

    public function rank_get($ranked_by, $limit = RETURN_LIMIT_FOR_RANK)
    {
        if (!$ranked_by) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'ranked_by'
            )), 200);
        }

        if ($ranked_by == 'level') {
            $leaderboard = $this->player_model->getLeaderboardByLevel($limit, $this->validToken['client_id'],
                $this->validToken['site_id']);
        } else {

            $reward = $this->reward_model->findByName(array(
                'client_id' => $this->validToken['client_id'],
                'site_id' => $this->validToken['site_id'],
                'group' => 'POINT',
            ), $ranked_by);

            if (empty($reward)) {
                $this->response($this->error->setError('REWARD_FOR_USER_NOT_EXIST', 200));
            }

            $mode = $this->input->get('mode');
            switch ($mode) {
                case 'weekly':
                    $leaderboard = $this->player_model->getWeeklyLeaderboard($ranked_by, $limit,
                        $this->validToken['client_id'], $this->validToken['site_id']);
                    break;
                case 'monthly':
                    $leaderboard = $this->player_model->getMonthlyLeaderboard($ranked_by, $limit,
                        $this->validToken['client_id'], $this->validToken['site_id']);
                    break;
                default: // all-time
                    $leaderboard = $this->player_model->getLeaderboard($ranked_by, $limit,
                        $this->validToken['client_id'], $this->validToken['site_id']);
                    break;
            }
        }
        $this->response($this->resp->setRespond($leaderboard), 200);
    }

    public function ranks_get($limit = RETURN_LIMIT_FOR_RANK)
    {
        $mode = $this->input->get('mode');
        switch ($mode) {
            case 'weekly':
                $leaderboards = $this->player_model->getWeeklyLeaderboards($limit, $this->validToken['client_id'],
                    $this->validToken['site_id']);
                break;
            case 'monthly':
                $leaderboards = $this->player_model->getMonthlyLeaderboards($limit, $this->validToken['client_id'],
                    $this->validToken['site_id']);
                break;
            default: // all-time
                $leaderboards = $this->player_model->getLeaderboards($limit, $this->validToken['client_id'],
                    $this->validToken['site_id']);
                break;
        }
        $this->response($this->resp->setRespond($leaderboards), 200);
    }

    public function rankuser_get($player_id = '', $ranked_by = '')
    {
        if ($player_id == '' || $ranked_by == '') {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'ranked_by',
                'player_id'
            )), 200);
        } else {
            $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
                'cl_player_id' => $player_id
            )));
            if (!$pb_player_id) {
                $this->response($this->error->setError('USER_NOT_EXIST'), 200);
            }

            $mode = $this->input->get('mode');
            switch ($mode) {
                case 'weekly':
                    $value = $this->player_model->getWeeklyPlayerReward($this->validToken['client_id'],
                        $this->validToken['site_id'], $this->reward_model->findByName($this->validToken, $ranked_by),
                        $pb_player_id);
                    $c = $this->player_model->countWeeklyPlayersHigherReward($this->validToken['client_id'],
                        $this->validToken['site_id'], $this->reward_model->findByName($this->validToken, $ranked_by),
                        $value);
                    $player = array(
                        'player_id' => $player_id,
                        'rank' => $c + 1,
                        'ranked_by' => $ranked_by,
                        'ranked_value' => $value,
                    );
                    break;
                case 'monthly':
                    $value = $this->player_model->getMonthlyPlayerReward($this->validToken['client_id'],
                        $this->validToken['site_id'], $this->reward_model->findByName($this->validToken, $ranked_by),
                        $pb_player_id);
                    $c = $this->player_model->countMonthlyPlayersHigherReward($this->validToken['client_id'],
                        $this->validToken['site_id'], $this->reward_model->findByName($this->validToken, $ranked_by),
                        $value);
                    $player = array(
                        'player_id' => $player_id,
                        'rank' => $c + 1,
                        'ranked_by' => $ranked_by,
                        'ranked_value' => $value,
                    );
                    break;
                default:
                    $players = $this->player_model->sortPlayersByReward($this->validToken['client_id'],
                        $this->validToken['site_id'], $this->reward_model->findByName($this->validToken, $ranked_by));
                    $cl_player_ids = array_map('index_cl_player_id', $players);
                    $idx = array_search($player_id, $cl_player_ids);
                    $player = ($idx !== false ? array(
                        'player_id' => $player_id,
                        'rank' => $idx + 1,
                        'ranked_by' => $ranked_by,
                        'ranked_value' => $players[$idx]['value'],
                    ) : array(
                        'player_id' => $player_id,
                        'rank' => count($players) + 1,
                        'ranked_by' => $ranked_by,
                        'ranked_value' => 0,
                    ));
                    break;
            }
            $this->response($this->resp->setRespond($player), 200);
        }
    }

    public function rankParam_get($action, $param)
    {
        // Check validity of action and parameter
        if (!$action) {
            $this->response($this->error->setError('ACTION_NOT_FOUND', array(
                'action'
            )), 200);
        }
        if (!$param) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'parameter'
            )), 200);
        }

        $action_id = $this->action_model->findAction(array_merge($this->validToken, array(
            'action_name' => urldecode($action)
        )));
        $valid = false;
        if (!$action_id) {
            $this->response($this->error->setError('ACTION_NOT_FOUND'), 200);
        }

        // Action and parameter are valid !
        // Now, getting all input
        $input = $this->input->get();

        // default mode is sum
        $input['mode'] = isset($input['mode']) ? $input['mode'] : "sum";

        // default limit is infinite
        $input['limit'] = isset($input['limit']) ? $input['limit'] : -1;

        // default group_by is player_id the smallest resolution, it could be distrct/ area as well
        $input['group_by'] = (isset($input['group_by']) && $input['group_by'] !== 'cl_player_id') ? $input['group_by'] : 'cl_player_id';
        $input['action_name'] = $action;
        $input['param'] = $param;

        // Let's Rank !!
        $result = $this->player_model->getMonthLeaderboardsByCustomParameter($input, $this->validToken['client_id'],
            $this->validToken['site_id']);

        $this->response($this->resp->setRespond($result), 200);
    }

    public function level_get($level = '')
    {
        if (!$level) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'level'
            )), 200);
        }

        $level = $this->level_model->getLevelDetail($level, $this->validToken['client_id'],
            $this->validToken['site_id']);
        $this->response($this->resp->setRespond($level), 200);
    }

    public function levels_get()
    {
        $level = $this->level_model->getLevelsDetail($this->validToken['client_id'], $this->validToken['site_id']);
        $this->response($this->resp->setRespond($level), 200);
    }

    public function goods_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $status = $this->input->get('status');
        if($status){
            if ($status != "all" && $status != "active" && $status != "used" && $status != "expired" && $status != "gifted") {
                $this->response($this->error->setError('INVALID_STATUS'), 200);
            }
            $status = ($status == "all") ? null : $status;
        } else {
            $status = "active";
        }
        //get player goods
        $goodsList['goods'] = $this->player_model->getGoods($pb_player_id, $this->site_id,
                              $this->input->get('tags') ? explode(',', $this->input->get('tags')) : null, $status);

        $null_list = array();
        $not_null_list = array();
        $favorite_null_list = array();
        $favorite_not_null_list = array();
        $date_expire= array();
        $favorite_date_expire= array();
        $name = array();
        $favorite_name = array();
        $name_null = array();
        $favorite_name_null = array();
        foreach ($goodsList['goods'] as $key => &$row) {
            $isFavorite = $this->player_model->getFavoriteGoods($this->client_id, $this->site_id, $pb_player_id, $row['goods_id']);
            $row['is_favorite'] = $isFavorite;
            $row['is_group'] = array_key_exists('group', $row);

            if(isset($row['date_expire']) && !is_null($row['date_expire'])){
                if($isFavorite){
                    array_push($favorite_not_null_list, $row);
                    $favorite_date_expire[$key]  = $row['date_expire'];
                    $favorite_name[$key] = $row['name'];
                }else{
                    array_push($not_null_list, $row);
                    $date_expire[$key]  = $row['date_expire'];
                    $name[$key] = $row['name'];
                }

            } else {
                if($isFavorite){
                    $favorite_name_null[$key] = $row['name'];
                    array_push($favorite_null_list, $row);
                }else {
                    $name_null[$key] = $row['name'];
                    array_push($null_list, $row);
                }
            }
        }
        array_multisort($favorite_date_expire, SORT_ASC, $favorite_name, SORT_ASC, $favorite_not_null_list);
        array_multisort($date_expire, SORT_ASC, $name, SORT_ASC, $not_null_list);
        array_multisort($favorite_name_null, SORT_ASC, $favorite_null_list);
        array_multisort($name_null, SORT_ASC, $null_list);
        $goodsList['goods'] = array_merge($favorite_not_null_list,$favorite_null_list,$not_null_list,$null_list);
        $this->response($this->resp->setRespond($goodsList), 200);
    }

    public function goods_favorite_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $status = $this->input->post('status');

        if ($status == "true"){
            $status = true;
        }elseif($status == "false"){
            $status = false;
        }
        else{
            $this->response($this->error->setError('INVALID_STATUS'), 200);
        }

        $goods_id = $this->input->post('goods_id');
        //check player goods
        $isPlayerGoodsFound = false;
        $playerGoodsList = $this->player_model->getGoods($pb_player_id, $this->site_id, null, null);
        foreach($playerGoodsList as $playerGoods){
            if($playerGoods['goods_id'] == $goods_id){
                $isPlayerGoodsFound = true;
                break;
            }
        }

        if (!$isPlayerGoodsFound) {
            $this->response($this->error->setError('GOODS_NOT_EXIST_IN_PLAYER_INVENTORY'), 200);
        }

        $this->player_model->setFavoriteGoods($this->client_id, $this->site_id, $pb_player_id, $goods_id, $status);

        $this->response($this->resp->setRespond(), 200);
    }

    public function goodsCount_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        $status = $this->input->get('status');
        if($status){
            if ($status != "all" && $status != "active" && $status != "used" && $status != "expired" && $status != "gifted") {
                $this->response($this->error->setError('INVALID_STATUS'), 200);
            }
            $status = ($status == "all") ? null : $status;
        } else {
            $status = "active";
        }
        //get player goods
        $n = $this->player_model->getGoodsCount($pb_player_id, $this->site_id,
            $this->input->get('tags') ? explode(',', $this->input->get('tags')) : null, $status);

        $this->response($this->resp->setRespond(array('n' => $n)), 200);
    }

    public function code_get($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        $player = $this->player_model->getPlayerByPlayerId($this->site_id, $player_id, array('code'));
        if (!$player) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }
        if (!isset($player['code'])) {
            $player['code'] = $this->player_model->generateCode($player['_id']);
        }
        $this->response($this->resp->setRespond(array('code' => $player['code'])), 200);
    }

    private function sendEngine($type, $from, $to, $message)
    {
        $access = false;
        try {
            $this->client_model->permissionProcess(
                $this->client_data,
                $this->client_id,
                $this->site_id,
                "notifications",
                "sms"
            );
            $access = true;
        } catch (Exception $e) {
            log_message('error', 'Error = ' . $e->getMessage());
        }

        if ($access) {
            $this->benchmark->mark('send_start');
            $validToken = $this->validToken;

            // send SMS
            $this->config->load("twilio", true);
            $config = $this->sms_model->getSMSClient($validToken['client_id'], $validToken['site_id']);
            $twilio = $this->config->item('twilio');
            $config['api_version'] = $twilio['api_version'];
            $this->load->library('twilio/twiliomini', $config);

            $response = $this->twiliomini->sms($from, $to, $message);
            $this->sms_model->log($validToken['client_id'], $validToken['site_id'], $type, $from, $to, $message,
                $response);
            if ($response->IsError) {
                log_message('error', 'Error sending SMS using Twilio, response = ' . print_r($response, true));
                $this->response($this->error->setError('INTERNAL_ERROR', $response), 200);
            }
            $this->benchmark->mark('send_end');
            $processing_time = $this->benchmark->elapsed_time('send_start', 'send_end');
            $this->response($this->resp->setRespond(array(
                'to' => $to,
                'from' => $from,
                'message' => $message,
                'processing_time' => $processing_time
            )), 200);
        }
        $this->response($this->error->setError('LIMIT_EXCEED'), 200);
    }

    public function requestOTPCode_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        $player = $this->player_model->getPlayerByPlayerId($this->site_id, $player_id);
        if (!$player) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        if (!isset($player['phone_number'])||!$player['phone_number']) {
            $this->response($this->error->setError('SMS_VERIFICATION_PHONE_NUMBER_NOT_FOUND'), 200);
        }

        if($this->input->post('os_type') && strtolower($this->input->post('os_type')) != "ios" && strtolower($this->input->post('os_type')) != "android"){
            $this->response($this->error->setError('OS_TYPE_INVALID'), 200);
        }

        $deviceInfo = array(
            'phone_number'=>null,
            'device_token'=>$this->input->post('device_token'),
            'device_description'=>$this->input->post('device_description'),
            'device_name'=>$this->input->post('device_name'),
            'os_type'=>strtolower($this->input->post('os_type'))
        );

        $code = $this->player_model->generateOTPCode($player['_id'], $deviceInfo);

        $validToken = $this->validToken;

        $sms_data = $this->sms_model->getSMSClient($validToken['client_id'], $validToken['site_id']);
        $from = $sms_data['number'];// this should be optimized to set config in twilio for sending from name not number
        $message = $code." is your verification code";

        $this->sendEngine('user', $from, $player['phone_number'], $message);

        $this->response($this->resp->setRespond(array('success' => true)), 200);
    }

    public function setupPhone_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }

        if (!$this->validTelephonewithCountry($this->input->post('phone_number'))) {
            $this->response($this->error->setError('USER_PHONE_INVALID'), 200);
        }

        if($this->input->post('os_type') && strtolower($this->input->post('os_type')) != "ios" && strtolower($this->input->post('os_type')) != "android"){
            $this->response($this->error->setError('OS_TYPE_INVALID'), 200);
        }

        $deviceInfo = array(
            'phone_number'=>$this->input->post('phone_number'),
            'device_token'=>$this->input->post('device_token'),
            'device_description'=>$this->input->post('device_description'),
            'device_name'=>$this->input->post('device_name'),
            'os_type'=>strtolower($this->input->post('os_type'))
        );

        $player = $this->player_model->getPlayerByPlayerId($this->site_id, $player_id);
        if (!$player) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $code = $this->player_model->generateOTPCode($player['_id'], $deviceInfo);

        $validToken = $this->validToken;

        $sms_data = $this->sms_model->getSMSClient($validToken['client_id'], $validToken['site_id']);
        $from = $sms_data['number']; // this should be optimized to set config in twilio for sending from name not number
        $message = $code." is your verification code";

        $this->sendEngine('user', $from,$this->input->post('phone_number'), $message);

        $this->response($this->resp->setRespond(array('success' => true)), 200);
    }

    public function contact_get($player_id = 0, $N = 10)
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        /* FIXME: random conact from randomeuser.me */
        $players = array();
        $i = 0;
        while ($i < $N) {
            $player = json_decode(file_get_contents('http://api.randomuser.me/'));
            if (!isset($player->results[0]->user)) {
                continue;
            }
            $user = $player->results[0]->user;
            $user->cl_player_id = $i + 1000;
            $user->first_name = $user->name->first;
            $user->last_name = $user->name->last;
            $user->phone = $user->cell;
            $user->image = $user->picture->thumbnail;
            $user->gender = $user->gender == 'male' ? 1 : 0;
            $user->birth_date = date(DATE_ISO8601, intval($user->dob));
            $user->registered = date(DATE_ISO8601, intval($user->registered));
            $user->type = $this->getSource($user);
            switch ($user->type) {
                case 'phone':
                    unset($user->email);
                    break;
                case 'g+':
                case 'fb':
                case 'tw':
                case 'gmail':
                default:
                    unset($user->phone);
                    break;
            }
            unset($user->name);
            unset($user->location);
            unset($user->picture);
            unset($user->password);
            unset($user->salt);
            unset($user->md5);
            unset($user->sha1);
            unset($user->sha256);
            unset($user->dob);
            unset($user->cell);
            unset($user->SSN);
            unset($user->PPS);
            unset($user->BSN);
            unset($user->TFN);
            unset($user->DNI);
            unset($user->NINO);
            unset($user->HETU);
            unset($user->INSEE);
            unset($user->nationality);
            unset($user->version);
            array_push($players, $user);
            $i++;
        }

        $this->response($this->resp->setRespond($players), 200);
    }

    private function getSource($user)
    {
        $r = rand(0, 100);
        if ($r <= 5) {
            return 'g+';
        }
        if ($r <= 15) {
            return 'tw';
        }
        if ($r <= 25) {
            return 'gmail';
        }
        if ($r <= 50) {
            return 'fb';
        }
        return 'phone';
    }

    public function deduct_reward_post($player_id)
    {
        /* param "player_id" */
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'cl_player_id' => $player_id,
        ));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        /* param "reward" */
        $reward = $this->input->post('reward');
        $reward_id = $this->reward_model->findByName(array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id
        ), $reward);
        if (!$reward_id) {
            $this->response($this->error->setError('REWARD_NOT_FOUND'), 200);
        }

        /* param "amount" */
        $amount = $this->input->post('amount');
        $amount = intval($amount);

        /* param "force" */
        $force = $this->input->post('force');

        /* get current reward value */
        $record = $this->reward_model->getPlayerReward($this->client_id, $this->site_id, $pb_player_id, $reward_id);
        if (!$record) {
            $this->response($this->error->setError('REWARD_FOR_USER_NOT_EXIST'), 200);
        }

        $reward_expire = $this->point_model->getPlayerRewardExpiration($this->validToken['client_id'], $this->validToken['site_id'], $pb_player_id, $reward_id);
        if($reward_expire){
            $expire_sum = array_sum(array_column($reward_expire, 'current_value'));
            $expire_value = $expire_sum ? $expire_sum : 0;
            $record['value'] = $record['value'] - $expire_value;
        }

        /* set new reward value */
        if (!$force && $record['value'] < $amount) {
            $this->response($this->error->setError('REWARD_FOR_USER_NOT_ENOUGH'), 200);
        }
        $new_value = $amount > $record['value'] ? 0 : $record['value'] - $amount;
        $this->reward_model->setPlayerReward($this->client_id, $this->site_id, $pb_player_id, $reward_id, $amount > $record['value'] ? $record['value'] : $amount);
        if($reward_expire) {
            $this->client_model->updateRewardExpired($this->client_id, $this->site_id, $pb_player_id, $reward_id, $amount > $record['value'] ? $record['value'] : $amount);
        }
        if ($reward == 'exp') {
            $this->player_model->setPlayerExp($this->client_id, $this->site_id, $pb_player_id, $new_value);
        }

        $this->response($this->resp->setRespond(array(
            "old_value" => $record['value'],
            "new_value" => $new_value,
            "value_deducted" => $amount > $record['value'] ? $record['value'] : $amount
        )), 200);
    }


    public function deduct_badge_post($player_id)
    {
        /* param "player_id" */
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'cl_player_id' => $player_id,
        ));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        /* param "reward" */
        $badge_name = $this->input->post('badge');
        $badge_id = $this->badge_model->getBadgeIDByName($this->client_id, $this->site_id, $badge_name);

        if (!$badge_id) {
            $this->response($this->error->setError('BADGE_NOT_FOUND'), 200);
        }

        /* param "amount" */
        $amount = intval($this->input->post('amount'));

        /* param "force" */
        $force = $this->input->post('force');

        /* get current reward value */
        $record = $this->reward_model->getPlayerBadge($this->client_id, $this->site_id, $pb_player_id, $badge_id);
        if (!$record) {
            $this->response($this->error->setError('BADGE_FOR_USER_NOT_EXIST'), 200);
        }

        /* set new reward value */
        if (!$force && $record['value'] < $amount) {
            $this->response($this->error->setError('BADGE_FOR_USER_NOT_ENOUGH'), 200);
        }
        $new_value = $record['value'] - $amount;
        if ($new_value < 0) {
            $new_value = 0;
        }
        $value_deducted = $record['value'] - $new_value;
        $this->reward_model->setPlayerBadge($this->client_id, $this->site_id, $pb_player_id, $badge_id, $new_value);

        $this->response($this->resp->setRespond(array(
            "old_value" => $record['value'],
            "new_value" => $new_value,
            "value_deducted" => $value_deducted
        )), 200);
    }

    public function total_get()
    {
        $log = array();
        $sum = 0;
        $prev = null;
        foreach ($this->player_model->new_registration($this->validToken, $this->input->get('from'),
            $this->input->get('to')) as $key => $value) {
            $key = $value['_id'];
            if ($prev) {
                $d = date('Y-m-d', strtotime('+1 day', strtotime($prev)));
                while (strtotime($d) < strtotime($key)) {
                    array_push($log, array($d => array('count' => 0)));
                    $d = date('Y-m-d', strtotime('+1 day', strtotime($d)));
                }
            }
            $prev = $key;
            $sum += $value['value'];
            array_push($log, array($key => array('count' => $sum)));
        }
        $this->response($this->resp->setRespond($log), 200);
    }

    public function new_get()
    {
        // Limit
        $plan_id = $this->client_model->getPlanIdByClientId($this->validToken['client_id']);
        $limit = $this->client_model->getPlanLimitById(
            $this->client_plan,
            'others',
            'insight'
        );

        $now = new Datetime();
        $startDate = new DateTime($this->input->get('from', true));
        $endDate = new DateTime($this->input->get('to', true));

        $log = array();
        $prev = null;
        $this->player_model->set_read_preference_secondary();
        foreach ($this->player_model->new_registration(
            $this->validToken,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')) as $key => $value) {
            $dDiff = $now->diff(new DateTime($value["_id"]));
            if ($limit && $dDiff->days > $limit) {
                continue;
            }
            $key = $value['_id'];
            if ($prev) {
                $d = date('Y-m-d', strtotime('+1 day', strtotime($prev)));
                while (strtotime($d) < strtotime($key)) {
                    array_push($log, array($d => array('count' => 0)));
                    $d = date('Y-m-d', strtotime('+1 day', strtotime($d)));
                }
            }
            $prev = $key;
            array_push($log, array($key => array('count' => $value['value'])));
        }
        $this->player_model->set_read_preference_primary();
        $this->response($this->resp->setRespond($log), 200);
    }

    public function dauDay_get()
    {
        $log = array();
        $prev = null;
        $this->player_model->set_read_preference_secondary();
        foreach ($this->player_model->daily_active_user_per_day($this->validToken, $this->input->get('from'),
            $this->input->get('to')) as $key => $value) {
            $key = $value['_id'];
            if ($prev) {
                $d = date('Y-m-d', strtotime('+1 day', strtotime($prev)));
                while (strtotime($d) < strtotime($key)) {
                    array_push($log, array($d => array('count' => 0)));
                    $d = date('Y-m-d', strtotime('+1 day', strtotime($d)));
                }
            }
            $prev = $key;
            array_push($log,
                array($key => array('count' => ($value['value'] instanceof MongoId ? 1 : $value['value']))));
        }
        $this->player_model->set_read_preference_primary();
        $this->response($this->resp->setRespond($log), 200);
    }

    public function mauDay_get()
    {
        $log = array();
        $prev = null;
        $this->player_model->set_read_preference_secondary();
        foreach ($this->player_model->monthy_active_user_per_day($this->validToken, $this->input->get('from'),
            $this->input->get('to')) as $key => $value) {
            $key = $value['_id'];
            if (strtotime($key . ' 00:00:00') <= time()) { // suppress future calculated results
                if ($prev) {
                    $d = date('Y-m-d', strtotime('+1 day', strtotime($prev)));
                    while (strtotime($d) < strtotime($key)) {
                        array_push($log, array($d => array('count' => 0)));
                        $d = date('Y-m-d', strtotime('+1 day', strtotime($d)));
                    }
                }
                $prev = $key;
                array_push($log,
                    array($key => array('count' => ($value['value'] instanceof MongoId ? 1 : $value['value']))));
            } else {
                break;
            }
        }
        $this->player_model->set_read_preference_primary();
        $this->response($this->resp->setRespond($log), 200);
    }

    public function mauWeek_get()
    {
        $log = array();
        $prev = null;
        $this->player_model->set_read_preference_secondary();
        foreach ($this->player_model->monthy_active_user_per_week($this->validToken, $this->input->get('from'),
            $this->input->get('to')) as $key => $value) {
            $key = $value['_id'];
            if (strtotime($key . ' 00:00:00') <= time()) { // suppress future calculated results
                if ($prev) {
                    $str = explode('-', $prev, 3);
                    $year_month = $str[0] . '-' . $str[1];
                    $next_month = date('m', strtotime('+1 month', strtotime($prev)));
                    $d = $str[2] == '01' ? $year_month . '-08' : ($str[2] == '08' ? $year_month . '-15' : ($str[2] == '15' ? $year_month . '-22' : $str[0] . '-' . $next_month . '-01'));
                    while (strtotime($d) < strtotime($key)) {
                        array_push($log, array($d => array('count' => 0)));
                        $str = explode('-', $d, 3);
                        $year_month = $str[0] . '-' . $str[1];
                        $next_month = date('m', strtotime('+1 month', strtotime($prev)));
                        $d = $str[2] == '01' ? $year_month . '-08' : ($str[2] == '08' ? $year_month . '-15' : ($str[2] == '15' ? $year_month . '-22' : $str[0] . '-' . $next_month . '-01'));
                    }
                }
                $prev = $key;
                array_push($log,
                    array($key => array('count' => ($value['value'] instanceof MongoId ? 1 : $value['value']))));
            } else {
                break;
            }
        }
        $this->player_model->set_read_preference_primary();
        $this->response($this->resp->setRespond($log), 200);
    }

    public function mauMonth_get()
    {
        $log = array();
        $prev = null;
        $this->player_model->set_read_preference_secondary();
        foreach ($this->player_model->monthy_active_user_per_month($this->validToken, $this->input->get('from'),
            $this->input->get('to')) as $key => $value) {
            $key = $value['_id'];
            if (strtotime($key . '-01 00:00:00') <= time()) { // suppress future calculated results
                if ($prev) {
                    $d = date('Y-m', strtotime('+1 month', strtotime($prev . '-01 00:00:00')));
                    while (strtotime($d . '-01 00:00:00') < strtotime($key . '-01 00:00:00')) {
                        array_push($log, array($d => array('count' => 0)));
                        $d = date('Y-m', strtotime('+1 month', strtotime($d . '-01 00:00:00')));
                    }
                }
                $prev = $key;
                array_push($log,
                    array($key => array('count' => ($value['value'] instanceof MongoId ? 1 : $value['value']))));
            } else {
                break;
            }
        }
        $this->player_model->set_read_preference_primary();
        $this->response($this->resp->setRespond($log), 200);
    }

    public function test_get()
    {
        echo '<pre>';
        $credential = array(
            'key' => 'abc',
            'secret' => 'abcde'
        );
        $cl_player_id = 'test1234';
        $image = 'profileimage.jpg';
        $email = 'test123@email.com';
        $username = 'test-1234';
        $token = $this->auth_model->getApiInfo($credential);
        echo '<br>createPlayer:<br>';
        $pb_player_id = $this->player_model->createPlayer(array_merge($token, array(
            'player_id' => $cl_player_id,
            'image' => $image,
            'email' => $email,
            'username' => $username,
            'birth_date' => '1982-09-08',
            'gender' => 1
        )));
        print_r($pb_player_id);
        echo '<br>readPlayer:<br>';
        $result = $this->player_model->readPlayer($pb_player_id, $token['site_id'], array(
            'cl_player_id',
            'pb_player_id',
            'username',
            'email',
            'image',
            'date_added',
            'birth_date'
        ));
        print_r($result);
        echo '<br>updatePlayer:<br>';
        $result = $this->player_model->updatePlayer($pb_player_id, $token['site_id'], array(
            'username' => 'test-4567',
            'email' => 'test4567@email.com'
        ));
        $result = $this->player_model->readPlayer($pb_player_id, $token['site_id'], array(
            'username',
            'email'
        ));
        print_r($result);
        echo '<br>deletePlayer:<br>';
        $result = $this->player_model->deletePlayer($pb_player_id, $token['site_id']);
        print_r($result);
        echo '<br>';
        $cl_player_id = '1';
        echo '<br>getPlaybasisId:<br>';
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($token, array(
            'cl_player_id' => $cl_player_id
        )));
        print_r($pb_player_id);
        echo '<br>getClientPlayerId:<br>';
        $cl_player_id = $this->player_model->getClientPlayerId($pb_player_id, $token['site_id']);
        print_r($cl_player_id);
        echo '<br>';
        echo '<br>getPlayerPoints:<br>';
        $result = $this->player_model->getPlayerPoints($pb_player_id, $token['site_id']);
        print_r($result);
        $reward_id = $this->point_model->findPoint(array_merge($token, array('reward_name' => 'exp')));
        echo '<br>getPlayerPoint:<br>';
        $result = $this->player_model->getPlayerPoint($pb_player_id, $reward_id, $token['site_id']);
        print_r($result);
        echo '<br>getLastActionPerform:<br>';
        $result = $this->player_model->getLastActionPerform($pb_player_id, $token['site_id']);
        print_r($result);
        echo '<br>getActionPerform:<br>';
        $action_id = $this->action_model->findAction(array_merge($token, array('action_name' => 'like')));
        $result = $this->player_model->getActionPerform($pb_player_id, $action_id, $token['site_id']);
        print_r($result);
        echo '<br>getActionCount:<br>';
        $result = $this->player_model->getActionCount($pb_player_id, $action_id, $token['site_id']);
        print_r($result);
        echo '<br>getBadge:<br>';
        $result = $this->player_model->getBadge($pb_player_id, $token['site_id']);
        print_r($result);
        echo '<br>getLastEventTime<br>';
        $result = $this->player_model->getLastEventTime($pb_player_id, $token['site_id'], 'LOGIN');
        print_r($result);
        echo '<br>';
        echo '<br>getLeaderboard<br>';
        $result = $this->player_model->getLeaderboard('exp', RETURN_LIMIT_FOR_RANK, $token['client_id'],
            $token['site_id']);
        print_r($result);
        echo '<br>getLeaderboards<br>';
        $result = $this->player_model->getLeaderboards(RETURN_LIMIT_FOR_RANK, $token['client_id'], $token['site_id']);
        print_r($result);
        echo '</pre>';
    }

    private function validClPlayerId($cl_player_id)
    {
        return (!preg_match("/^([a-zA-Z0-9-_=]+)+$/i", $cl_player_id)) ? false : true;
    }

    private function validTelephonewithCountry($number)
    {
        return (!preg_match("/\+(9[976]\d|8[987530]\d|6[987]\d|5[90]\d|42\d|3[875]\d| 2[98654321]\d|9[8543210]|8[6421]|6[6543210]|5[87654321]| 4[987654310]|3[9643210]|2[70]|7|1)\d{1,14}$/",
            $number)) ? false : true;
    }

    /**
     * Use with array_walk and array_walk_recursive.
     * Recursive iterable items to modify array's value
     * from MongoId to string and MongoDate to readable date
     * @param mixed $item this is reference
     * @param string $key
     */
    private function convert_mongo_object(&$item, $key)
    {
        if (is_object($item)) {
            if (get_class($item) === 'MongoId') {
                $item = $item->{'$id'};
            } else {
                if (get_class($item) === 'MongoDate') {
                    $item = datetimeMongotoReadable($item);
                }
            }
        }
    }

    public function getAssociatedNode_get($player_id = '')
    {
        $result = array();
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $temp = $this->store_org_model->getAssociatedNodeOfPlayer($this->validToken['client_id'],
            $this->validToken['site_id'], $pb_player_id);
        foreach ($temp as $entry) {
            //$result[$key]["_id"]=$entry["_id"]."";
            $temp2 = $this->store_org_model->retrieveNodeById($this->validToken['site_id'], $entry["node_id"]);
            $temp3['node_id'] = $entry["node_id"] . "";
            $temp3['name'] = $temp2['name'];

            array_push($result, $temp3);
        }

        $this->response($this->resp->setRespond($result), 200);
    }

    public function getRole_get($player_id = '', $node_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        if (!$node_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'node_id'
            )), 200);
        }

        $node = $this->store_org_model->retrieveNodeById($this->validToken['site_id'], new MongoId($node_id));
        if (!$node) {
            $this->response($this->error->setError('STORE_ORG_NODE_NOT_FOUND'), 200);
        }

        $role_info = $this->store_org_model->getRoleOfPlayer($this->validToken['client_id'],
            $this->validToken['site_id'], $pb_player_id, new MongoId($node_id));
        if (!$role_info) {
            $this->response($this->error->setError('STORE_ORG_PLAYER_NOT_EXISTS_WITH_NODE'), 200);
        } else {
            $org_info = $this->store_org_model->retrieveOrganizeById($this->validToken['client_id'],
                $this->validToken['site_id'], $node['organize']);
            $roles = array();
            $array_role = isset($role_info['roles']) ? array_keys($role_info['roles']) : array();
            if (is_array($array_role)) {
                foreach ($array_role as $role) {
                    $roles[] = array(
                        'role' => $role,
                        'join_date' => datetimeMongotoReadable($role_info['roles'][$role])
                    );
                }
            }
            $result = array(
                'organize_type' => $org_info['name'],
                'roles' => $roles
            );
            $this->response($this->resp->setRespond($result), 200);
        }
    }

    public function unlock_post($player_id = '')
    {
        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));

        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $this->player_model->unlockPlayer($this->validToken['site_id'], $pb_player_id);
        $this->response($this->resp->setRespond(), 200);
    }

    public function saleReport_get($player_id = '')
    {
        $result = array();

        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $month = $this->input->get('month');
        if (!$month) {
            $month = date("m", time());
        }
        $year = $this->input->get('year');
        if (!$year) {
            $year = date("Y", time());
        }
        $action = $this->input->get('action');
        if (!$action) {
            $action = "sell";
        }
        $parameter = $this->input->get('parameter');
        if (!$parameter) {
            $parameter = "amount";
        }

        $parent_node = $this->store_org_model->getAssociatedNodeOfPlayer($this->validToken['client_id'],
            $this->validToken['site_id'], $pb_player_id);

        foreach ($parent_node as $node) {
            $list = array();
            $nodesData = $this->store_org_model->retrieveNode($this->client_id, $this->site_id);
            $this->utility->recurGetChildUnder($nodesData, new MongoId($node['node_id']), $list);

            $table = $this->store_org_model->getSaleHistoryOfNode($this->validToken['client_id'],
                $this->validToken['site_id'], $list, $action, $parameter, $month, $year, 2);

            $this_month_time = strtotime($year . "-" . $month);
            $previous_month_time = strtotime('-1 month', $this_month_time);

            $current_month = date("m", $this_month_time);
            $current_year = date("Y", $this_month_time);

            $previous_month = date("m", $previous_month_time);
            $previous_year = date("Y", $previous_month_time);

            $current_month_sales = $table[$current_year][$current_month][$parameter];
            $previous_month_sales = $table[$previous_year][$previous_month][$parameter];

            $temp2 = $this->store_org_model->retrieveNodeById($this->validToken['site_id'], $node['node_id']);
            $temp['name'] = $temp2['name'];
            $temp[$parameter] = $current_month_sales;
            $temp['previous_' . $parameter] = $previous_month_sales;

            if ($current_month_sales == 0 && $previous_month_sales == 0) {
                $temp['percent_changed'] = 0;
            } elseif ($previous_month_sales == 0) {
                $temp['percent_changed'] = 100;
            } else {
                $temp['percent_changed'] = (($current_month_sales - $previous_month_sales) * 100) / $previous_month_sales;
            }

            $node["node_id"] = $node["node_id"] . "";

            array_push($result, array_merge(array('node_id' => $node['node_id']), $temp));
        }

        $this->response($this->resp->setRespond($result), 200);
    }

    public function actionReport_get($player_id = '')
    {
        $result = array();

        if (!$player_id) {
            $this->response($this->error->setError('PARAMETER_MISSING', array(
                'player_id'
            )), 200);
        }
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if (!$pb_player_id) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $count = $this->input->get('count');
        if (!$count) {
            $count = 1;
        }

        $month = $this->input->get('month');
        if (!$month) {
            $month = date("m", time());
        }
        $year = $this->input->get('year');
        if (!$year) {
            $year = date("Y", time());
        }
        $action = $this->input->get('action');
        if (!$action) {
            $action = "sell";
        }
        $parameter = $this->input->get('parameter');
        if (!$parameter) {
            $parameter = "amount";
        }

        $table = $this->player_model->getActionHistory($this->validToken['client_id'],
                $this->validToken['site_id'], $player_id, $action, $parameter, $month, $year, $count+1);

        $this_month_time = strtotime($year . "-" . $month);
        for ($index = 0; $index < $count; $index++) {
            $current_month = date("m", strtotime('-' . ($index) . ' month', $this_month_time));
            $current_year = date("Y", strtotime('-' . ($index) . ' month', $this_month_time));

            $previous_month = date("m", strtotime('-' . ($index + 1) . ' month', $this_month_time));
            $previous_year = date("Y", strtotime('-' . ($index + 1) . ' month', $this_month_time));

            $current_month_sales = $table[$current_year][$current_month][$parameter];
            $previous_month_sales = $table[$previous_year][$previous_month][$parameter];

            $result[$current_year][$current_month][$parameter] = $current_month_sales;
            $result[$current_year][$current_month]['previous_' . $parameter] = $previous_month_sales;

            if ($current_month_sales == 0 && $previous_month_sales == 0) {
                $result[$current_year][$current_month]['percent_changed'] = 0;
            } elseif ($previous_month_sales == 0) {
                $result[$current_year][$current_month]['percent_changed'] = 100;
            } else {
                $result[$current_year][$current_month]['percent_changed'] = (($current_month_sales - $previous_month_sales) * 100) / $previous_month_sales;
            }
        }

        $this->response($this->resp->setRespond($result), 200);
    }

    private function password_validation($client_id, $site_id, $inhibited_str = '')
    {
        $return_status = false;
        $setting = $this->player_model->getSecuritySetting($client_id, $site_id);
        if (isset($setting['password_policy'])) {
            $password_policy = $setting['password_policy'];
            $rule = 'trim|xss_clean';
            if ($password_policy['min_char'] && $password_policy['min_char'] > 0) {
                $rule = $rule . '|' . 'min_length[' . $password_policy['min_char'] . ']';
            }
            if ($password_policy['alphabet'] && $password_policy['numeric']) {
                $rule = $rule . '|callback_require_at_least_number_and_alphabet';
            } elseif ($password_policy['alphabet']) {
                $rule = $rule . '|callback_require_at_least_alphabet';
            } elseif ($password_policy['numeric']) {
                $rule = $rule . '|callback_require_at_least_number';
            }

            if ($password_policy['user_in_password'] && ($inhibited_str != '')) {
                $rule = $rule . '|callback_word_in_password[' . $inhibited_str . ']';
            }
            $this->form_validation->set_rules('password', 'password', $rule);
            if ($this->form_validation->run()) {
                $return_status = true;
            } else {
                $return_status = false;
            }
        } else {
            $return_status = true;
        }
        return $return_status;

    }

    public function require_at_least_number_and_alphabet($str)
    {
        if (preg_match('#[0-9]#', $str) && preg_match('#[a-zA-Z]#', $str)) {
            return true;
        }
        $this->form_validation->set_message('require_at_least_number_and_alphabet',
            'The %s field require at least one numeric character and one alphabet');
        return false;
    }

    public function require_at_least_number($str)
    {
        if (preg_match('#[0-9]#', $str)) {
            return true;
        }
        $this->form_validation->set_message('require_at_least_number',
            'The %s field require at least one number');
        return false;
    }

    public function require_at_least_alphabet($str)
    {
        if (preg_match('#[a-zA-Z]#', $str)) {
            return true;
        }
        $this->form_validation->set_message('require_at_least_alphabet',
            'The %s field require at least one alphabet');
        return false;
    }

    public function word_in_password($str, $val)
    {
        if (strpos($str, $val) !== false) {
            $this->form_validation->set_message('word_in_password',
                'The %s field disallow to contain logon IDs');
            return false;
        }
        return true;
    }

}

function index_cl_player_id($obj)
{
    return $obj['cl_player_id'];
}

function index_device_token($obj)
{
    return $obj['device_token'];
}

?>
