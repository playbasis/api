<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class jigsaw extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('playbasis');
        $this->load->library('mongo_db');
    }

    public function action($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        $data_set = $this->getActionDatasetInfo($config['action_name']);
        $required = array();
        if (is_array($data_set)) {
            foreach ($data_set as $param) {
                $isRequired = isset($param['required']) ? $param['required'] : false;
                $param_name = $param['param_name'];
                if (!isset($input[$param_name]) && ($isRequired)) {
                    array_push($required, $param_name);
                }
            }
        }
        if (!empty($required)) {
            $requiredParam = implode(", ", $required);
            try {
                throw new Exception($requiredParam);
            } catch (Exception $e) {
                throw new Exception('PARAMETER_MISSING', 0, $e);
            }
        }

        return true;
    }

    public function getActionDatasetInfo($action_name)
    {
        $this->set_site_mongodb($this->session->userdata('site_id'));

        $this->mongo_db->where(array(
            'name' => $action_name
        ));
        $results = $this->mongo_db->get("playbasis_action");
        return $results ? $results[0]['init_dataset'] : null;
    }

    public function customParameter($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['param_name']));
        assert(isset($config['param_value']));
        assert(isset($config['param_operation']));

        $result = false;
        $param_name = $config['param_name'];

        if (isset($input[$param_name])) {
            if ($config['param_operation'] == '=') {
                $result = ($input[$param_name] == $config['param_value']);
            } elseif ($config['param_operation'] == '>') {
                $result = ($input[$param_name] > $config['param_value']);
            } elseif ($config['param_operation'] == '<') {
                $result = ($input[$param_name] < $config['param_value']);
            } elseif ($config['param_operation'] == '>=') {
                $result = ($input[$param_name] >= $config['param_value']);
            } elseif ($config['param_operation'] == '<=') {
                $result = ($input[$param_name] <= $config['param_value']);
            }
        } else {
            $result = false;
        }
        return $result;
    }

    public function level($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['type']));
        assert(isset($config['value']));

        $result = false;

        if (isset($input['level'])) {
            if ($config['type'] == '=') {
                $result = ($input['level'] == $config['value']);
            } elseif ($config['type'] == '>') {
                $result = ($input['level'] > $config['value']);
            } elseif ($config['type'] == '<') {
                $result = ($input['level'] < $config['value']);
            } elseif ($config['type'] == '>=') {
                $result = ($input['level'] >= $config['value']);
            } elseif ($config['type'] == '<=') {
                $result = ($input['level'] <= $config['value']);
            }
        }

        return $result;
    }

    public function badgeCondition($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['badge_id']));
        assert(isset($config['value']));
        $result = false;

        foreach ($input['player_badge'] as $key => $badge) {
            if (($badge['badge_id'] == $config['badge_id']) && $badge['amount'] >= $config['value']) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public function reward($config, $input, &$exInfo = array(), $cache = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['reward_id']));
        assert(isset($config['reward_name']));
        assert($config["item_id"] == null || isset($config["item_id"]));
        assert(isset($config['quantity']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        //always true if reward type is point
        if (is_null($config['item_id']) || $config['item_id'] == '') {
            return $this->checkReward($config['reward_id'], $input['site_id'], $config['quantity']);
        }

        //if reward type is badge
        switch ($config['reward_name']) {
            case 'badge':
                return $this->checkBadge($config['item_id'], $input['pb_player_id'], $input['site_id'],
                    $config['quantity']);
            case 'goods':
                return $this->checkGoodsWithCache($cache, $config['item_id'], $input['pb_player_id'], $input['site_id'],
                    $config['quantity']);
            default:
                return false;
        }
    }

    public function customPointReward($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        $name = $config['reward_name'];
        $quan = $config['quantity'];
        if (!$name && isset($input['reward']) && $input['reward']) {
            $name = $input['reward'];
        }
        if (!$quan && isset($input['quantity']) && $input['quantity']) {
            $quan = $input['quantity'];
        }
        $exInfo['dynamic']['reward_name'] = $name;
        $exInfo['dynamic']['quantity'] = $quan;
        return $name && $quan;
    }

    public function specialReward($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        $name = $config['reward_name'];
        $quan = $config['quantity'];
        if (!$name && isset($input['reward']) && $input['reward']) {
            $name = $input['reward'];
        }
        if (!$quan && isset($input['quantity']) && $input['quantity']) {
            $quan = $input['quantity'];
        }
        $exInfo['dynamic']['reward_name'] = $name;
        $exInfo['dynamic']['quantity'] = $quan;
        return $name && $quan;
    }

    public function counter($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['counter_value']));
        assert(isset($config['interval']));
        assert(isset($config['interval_unit']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $result = $this->getMostRecentJigsaw($input, array(
            'input',
            'date_added'
        ));
        if (!$result) {
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1;
            $exInfo['remaining_time'] = (int)$config['interval'];
            return false;
        }
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $log = $result['input'];
        if ($config['interval'] == 0) //if config time = 0 reduce counter and return false
        {
            $log['remaining_counter'] -= 1;
            if ((int)$log['remaining_counter'] == 0) {
                $exInfo['remaining_counter'] = (int)$config['counter_value'];
                $exInfo['remaining_time'] = -1; //reset timer, timer won't go down until counter triggers again
                return true;
            }
            $exInfo['remaining_counter'] = $log['remaining_counter'];
            $exInfo['remaining_time'] = $config['interval'];
            return false;
        }
        if ($config['interval'] != 0 && $log['remaining_time'] == 0) {
            $exInfo['remaining_counter'] = $log['remaining_counter'] - 1;
            $exInfo['remaining_time'] = (int)$config['interval'];
            return false;
        }
        $lastTime = $result['date_added'];
        $timeDiff = ($log['interval_unit']) == 'second' ? (int)($timeNow - $lastTime->sec) : (int)(date_diff(new DateTime("@$timeNow"),
            new DateTime(datetimeMongotoReadable($lastTime)))->d);
        $resetUnit = ($log['interval_unit'] != $config['interval_unit']);
        $remainingTime = $log['remaining_time'];
        $reset = ($remainingTime >= 0) && ($timeDiff > $remainingTime);
        if ($resetUnit || $reset) //if reset, start counter timer and decrease counter by 1
        {
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1;
            $exInfo['remaining_time'] = (int)$config['interval'];
            return false;
        }
        $log['remaining_counter'] -= 1;
        if ((int)$log['remaining_counter'] == 0) {
            $exInfo['remaining_counter'] = (int)$config['counter_value'];
            $exInfo['remaining_time'] = -1; //reset timer, timer won't go down until counter triggers again
            return true;
        } else {
            $exInfo['remaining_counter'] = $log['remaining_counter'];
            if (($remainingTime < 0) || $config['reset_timeout']) {
                $exInfo['remaining_time'] = (int)$config['interval'];
            } else {
                $exInfo['remaining_time'] = $remainingTime - $timeDiff;
            }
            return false;
        }
    }

    public function counterWithin($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['counter_value']));
        assert(isset($config['within']));
        assert(isset($config['interval_unit']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $result = $this->getMostRecentJigsaw($input, array(
            'input',
            'date_added'
        ));
        if (!$result) {
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1; // max-1
            $exInfo['beginning_time'] = $timeNow;
            if ($exInfo['remaining_counter'] == 0) {
                $exInfo['remaining_counter'] = (int)$config['counter_value']; // max
                $exInfo['beginning_time'] = -1; // unset
                return true;
            }
            return false;
        }
        $log = $result['input'];
        $within = (int)$config['within'];
        $timeDiff = ($log['interval_unit']) == 'second' ? (int)($timeNow - $within) : (int)($timeNow - strtotime('-' . $within . ' days',
                $timeNow));
        if ($timeDiff > $log['beginning_time']) { // time's up!
            $exInfo['remaining_counter'] = (int)$config['counter_value'] - 1; // max-1
            $exInfo['beginning_time'] = $timeNow; // reset to "now"
        } else { // valid
            $exInfo['remaining_counter'] = (int)$log['remaining_counter'] - 1; // current-1
            $exInfo['beginning_time'] = $log['beginning_time']; // stays the same;
        }
        if ($exInfo['remaining_counter'] == 0) {
            $exInfo['remaining_counter'] = (int)$config['counter_value']; // max
            $exInfo['beginning_time'] = -1; // unset
            return true;
        }
        return false;
    }

    public function cooldown($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['cooldown']));
        assert($input != false);
        assert(is_array($input));
        assert($input['pb_player_id']);
        assert($input['rule_id']);
        assert($input['jigsaw_id']);
        $result = $this->getMostRecentJigsaw($input, array(
            'input',
            'date_added'
        ));
        if (!$result) {
            $exInfo['remaining_cooldown'] = (int)$config['cooldown'];
            return true;
        }
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $log = $result['input'];
        $lastTime = $result['date_added'];
        $timeDiff = (int)($timeNow - $lastTime->sec);
        if ($timeDiff > $log['remaining_cooldown']) {
            $exInfo['remaining_cooldown'] = (int)$config['cooldown'];
            return true;
        } else {
            $exInfo['remaining_cooldown'] = (int)$log['remaining_cooldown'] - $timeDiff;
            return false;
        }
    }

    public function before($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['timestamp']));
        assert($input != false);
        assert(is_array($input));
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        return (strtotime($config['timestamp']) > $timeNow);
    }

    public function after($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert(isset($config['timestamp']));
        assert($input != false);
        assert(is_array($input));
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        return (strtotime($config['timestamp']) < $timeNow);
    }

    public function between($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['start_time']));
        assert(isset($config['end_time']));
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $start = $config['start_time'];
        $end = $config['end_time'];
        $start = strtotime("1970-01-01 $start:00");
        $end = strtotime("1970-01-01 $end:00");
        //check time range that crosses to the next day
        if ($end < $start) {
            $end = strtotime("1970-01-02 $end:00");
        }
        $now = strtotime("1970-01-01 " . date('H:i', $timeNow) . ":00");
        return ($start < $now && $now < $end);
    }

    public function daily($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        $result = $this->getMostRecentJigsaw($input, array(
            'date_added'
        ));
        if (!$result) {
            return true;
        }
        $lastTime = $result['date_added'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        $currentYMD = date("Y-m-d");

        $settingTime = $config['time_of_day'];
        $settingTime = strtotime("$currentYMD $settingTime:00");
        $currentTime = strtotime($currentYMD." " . date('H:i', $timeNow) . ":00");

        if ($settingTime < $lastTime->sec){ // action has been processed for today !
            return false;
        }
        return $currentTime > $settingTime;
    }

    public function weekly($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        assert(isset($config['day_of_week']));
        $result = $this->getMostRecentJigsaw($input, array(
            'input'
        ));
        if (!$result) {
            $exInfo['next_trigger'] = strtotime("next " . $config['day_of_week'] . " " . $config['time_of_day']);
            return true;
        }
        $logInput = $result['input'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        if ($timeNow >= $logInput['next_trigger']) {
            $exInfo['next_trigger'] = strtotime("next " . $config['day_of_week'] . " " . $config['time_of_day']);
            return true;
        }
        $exInfo['next_trigger'] = $logInput['next_trigger'];
        return false;
    }

    public function monthly($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        assert(isset($config['day_of_month']));
        $result = $this->getMostRecentJigsaw($input, array(
            'input'
        ));
        if (!$result) {
            $lastDateOfMonth = date('d', strtotime("last day of next month"));
            $exInfo['next_trigger'] = $config['day_of_month'] > $lastDateOfMonth ? strtotime("last day of next month" . $config['time_of_day']) : strtotime("first day of next month " . $config['time_of_day']) + ($config['day_of_month'] - 1) * 3600 * 24;
            return true;
        }
        $logInput = $result['input'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        if ($timeNow >= $logInput['next_trigger']) {
            $lastDateOfMonth = date('d', strtotime("last day of next month"));
            $exInfo['next_trigger'] = $config['day_of_month'] > $lastDateOfMonth ? strtotime("last day of next month" . $config['time_of_day']) : strtotime("first day of next month " . $config['time_of_day']) + ($config['day_of_month'] - 1) * 3600 * 24;
            return true;
        }
        $exInfo['next_trigger'] = $logInput['next_trigger'];
        return false;
    }

    public function everyNDays($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['time_of_day']));
        assert(isset($config['num_of_days']));
        $result = $this->getMostRecentJigsaw($input, array(
            'input'
        ));
        if (!$result) {
            $currentDate = new DateTime();
            $nextTrigger = $currentDate->modify("+" . $config['num_of_days'] . " day");
            assert($nextTrigger);
            $time = explode(':', $config['time_of_day']);
            $nextTrigger->setTime($time[0], $time[1]);
            assert($nextTrigger);
            $exInfo['next_trigger'] = $nextTrigger->getTimestamp();
            return true;
        }
        $logInput = $result['input'];
        $timeNow = isset($input['action_log_time']) ? $input['action_log_time'] : time();
        if ($timeNow >= $logInput['next_trigger']) {
            $nextTrigger = new DateTime();
            $nextTrigger->setTimestamp($logInput['next_trigger']);
            assert($nextTrigger);
            $nextTrigger->modify("+" . $config['num_of_days'] . " day");
            assert($nextTrigger);
            $exInfo['next_trigger'] = $nextTrigger->getTimestamp();
            return true;
        }
        $exInfo['next_trigger'] = $logInput['next_trigger'];
        return false;
    }

    public function objective($config, $input, &$exInfo = array())
    {
        assert($config != false);
        assert(is_array($config));
        assert($input != false);
        assert(is_array($input));
        assert(isset($config['objective_id']));
        $objective_id = $config['objective_id'];
        assert(is_string($objective_id));
        $this->set_site_mongodb($input['site_id']);
        //check if this objective has been completed
        $this->mongo_db->where(array(
            'objective_id' => new MongoId($objective_id),
            'pb_player_id' => $input['pb_player_id'],
        ));
        $count = $this->mongo_db->count('playbasis_objective_to_player');
        if ($count > 0) {
            return true;
        }
        //objective not yet completed, check prerequisites
        $this->mongo_db->select(array(
            'prerequisites',
            'name'
        ));
        $this->mongo_db->where(array('_id' => new MongoId($objective_id)));
        $result = $this->mongo_db->get('playbasis_objective');
        assert($result);
        $result = $result[0];
        $prereqs = $result['prerequisites'];
        $objName = $result['name'];
        foreach ($prereqs as $value) {
            $this->mongo_db->where(array(
                'objective_id' => $value,
                'pb_player_id' => $input['pb_player_id'],
            ));
            $count = $this->mongo_db->count('playbasis_objective_to_player');
            if (!$count || ($count <= 0)) {
                return false;
            } //prereq objective not complete, can't complete this objective
        }
        $exInfo['objective_complete'] = array(
            'id' => $objective_id,
            'name' => $objName
        );
        return true;
    }

    public function distinct($config, $input, &$exInfo = array())
    {
        $params = array();
        $data_set = $this->getActionDatasetInfo($input['action_name']);
        if (is_array($data_set)) {
            foreach ($data_set as $param) {
                $param_name = $param['param_name'];
                if (isset($input[$param_name])) {
                    $params[$param_name] = $input[$param_name];
                }
            }
        }
        $c = $this->countActionWithParams($input['client_id'], $input['site_id'], $input['pb_player_id'],
            $input['action_id'], $params);
        return $c == 0;
    }

    private function countActionWithParams($client_id, $site_id, $pb_player_id, $action_id, $parameters)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'pb_player_id' => $pb_player_id,
            'action_id' => $action_id,
        ));

        foreach ($parameters as $name => $value) {
            $this->mongo_db->where(array('parameters.' . $name => $value));
        }

        $temp = $this->mongo_db->count('playbasis_validated_action_log');
        return $temp;
    }

    public function email($config, $input, &$exInfo = array())
    {
        return $this->feedback('email', $config, $input, $exInfo);
    }

    public function sms($config, $input, &$exInfo = array())
    {
        return $this->feedback('sms', $config, $input, $exInfo);
    }
    public function push($config, $input, &$exInfo = array())
    {
        return $this->feedback('push', $config, $input, $exInfo);
    }

    private function feedback($type, $config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $this->mongo_db->where('status', true);
        $this->mongo_db->where('site_id', $input['site_id']);
        $this->mongo_db->where('link', $type);
        $this->mongo_db->limit(1);
        if ($this->mongo_db->count('playbasis_feature_to_client') > 0) {
            $this->mongo_db->where('_id', new MongoId($config['template_id']));
            $this->mongo_db->where('status', true);
            $this->mongo_db->where('deleted', false);
            return $this->mongo_db->count('playbasis_' . $type . '_to_client') > 0;
        }
        return false;
    }

    public function random($config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $sum = 0;
        $acc = array();
        $cache = array();
        foreach ($config['group_container'] as $i => $conf) {
            // invalid goods will be excluded from randomness
            if (!(array_key_exists('reward_name', $conf) && $conf['reward_name'] == 'goods')
                || $this->checkGoodsWithCache($cache, new MongoId($conf['item_id']), $input['pb_player_id'],
                    $input['site_id'], $conf['quantity'])
            ) {
                $sum += intval($conf['weight']);
                $acc[$i] = $sum;
            }
        }
        if (!$acc) {
            return false;
        } // there is no valid entry
        $max = $sum;
        $ran = rand(0, $max - 1);
        foreach ($acc as $i => $value) {
            if ($ran < $value) {
                $exInfo['index'] = $i;
                $exInfo['break'] = false;
                $conf = $config['group_container'][$i];
                if (array_key_exists('reward_name', $conf)) {
                    foreach (array('item_id', 'reward_id') as $field) {
                        if (array_key_exists($field, $conf)) {
                            $conf[$field] = $conf[$field] ? ($conf[$field] != 'goods' ? new MongoId($conf[$field]) : $conf[$field]) : null;
                        }
                    }
                    $ret = $this->reward($conf, $input, $exInfo, $cache);
                    return $ret;
                } else {
                    if (array_key_exists('feedback_name', $conf)) {
                        $ret = $this->feedback($conf['feedback_name'], $conf, $input, $exInfo);
                        return $ret;
                    }
                }
                return false; // should not reach this line
            }
        }
        return false; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    public function sequence($config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $result = $this->getMostRecentJigsaw($input, array(
            'input'
        ));
        $i = !$result || !isset($result['input']['index']) ? 0 : $result['input']['index'] + 1;
        $exInfo['index'] = $i;
        $exInfo['break'] = true; // generally, "sequence" will block
        if ($i > count($config['group_container']) - 1) {
            $exInfo['index'] = $result['input']['index']; // ensure that "index" has not been changed
            if ($config['loop'] === 'false' || !$config['loop']) {
                return false;
            }
            $i = 0; // looping, reset to be starting at 0
            $exInfo['index'] = 0;
        }
        if ($i == count($config['group_container']) - 1) {
            $exInfo['break'] = false;
        } // if this is last item in the sequence jigsaw, we allow the rule to process next jigsaw
        $conf = $config['group_container'][$i];
        if (array_key_exists('reward_name', $conf)) {
            foreach (array('item_id', 'reward_id') as $field) {
                if (array_key_exists($field, $conf)) {
                    $conf[$field] = $conf[$field] ? ($conf[$field] != 'goods' ? new MongoId($conf[$field]) : $conf[$field]) : null;
                }
            }
            return $this->reward($conf, $input, $exInfo);
        } else {
            if (array_key_exists('feedback_name', $conf)) {
                return $this->feedback($conf['feedback_name'], $conf, $input, $exInfo);
            }
        }
        return false; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    public function redeem($config, $input, &$exInfo = array())
    {
        $this->set_site_mongodb($input['site_id']);
        $ok = true; // default is true
        foreach ($config['group_container'] as $conf) {
            $avail = false;
            if (is_null($conf['item_id']) || $conf['item_id'] == '') {
                $avail = $this->checkRedeemPoint($input['site_id'], new MongoId($conf['reward_id']),
                    $input['pb_player_id'], intval($conf['quantity']));
            } else {
                switch ($conf['reward_name']) {
                    case 'badge':
                        $avail = $this->checkRedeemBadge($input['site_id'], new MongoId($conf['item_id']),
                            $input['pb_player_id'], intval($conf['quantity']));
                        break;
                    case 'goods':
                        /* TODO: support goods */
                        break;
                    default:
                        break;
                }
            }
            if (!$avail) {
                $ok = false;
                break;
            }
        }
        if ($ok) {
            /* TODO: permissionProcess "redeem" */
            foreach ($config['group_container'] as $conf) {
                if (is_null($conf['item_id']) || $conf['item_id'] == '') {
                    if ($conf['reward_name'] == 'exp') {
                        continue;
                    } // "exp" should not be decreasing
                    $this->updatePlayerPointReward($input['client_id'], $input['site_id'],
                        new MongoId($conf['reward_id']), $input['pb_player_id'], $input['player_id'],
                        -1 * (int)$conf['quantity']);
                } else {
                    switch ($conf['reward_name']) {
                        case 'badge':
                            $this->updateplayerBadge($input['client_id'], $input['site_id'],
                                new MongoId($conf['item_id']), $input['pb_player_id'], $input['player_id'],
                                -1 * (int)$conf['quantity']);
                            break;
                        case 'goods':
                            /* TODO: support goods */
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return $ok;
    }

    public function getMostRecentJigsaw($input, $fields)
    {
        assert(isset($input['site_id']));
        $this->set_site_mongodb($input['site_id']);
        $this->mongo_db->select($fields);
        $this->mongo_db->where(array(
            'pb_player_id' => $input['pb_player_id'],
            'rule_id' => $input['rule_id'],
            'jigsaw_id' => $input['jigsaw_id'],
            'jigsaw_index' => $input['jigsaw_index']
        ));
        $this->mongo_db->order_by(array(
            'date_added' => 'desc'
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('jigsaw_log');
        //for backward compatibility, check again without jigsaw_index
        if (!$result) {
            $this->mongo_db->select($fields);
            $this->mongo_db->where(array(
                'pb_player_id' => $input['pb_player_id'],
                'rule_id' => $input['rule_id'],
                'jigsaw_id' => $input['jigsaw_id'],
            ));
            $this->mongo_db->order_by(array(
                'date_added' => 'desc'
            ));
            $this->mongo_db->limit(1);
            $result = $this->mongo_db->get('jigsaw_log');
        }
        return ($result) ? $result[0] : $result;
    }

    private function checkBadge($badgeId, $pb_player_id, $site_id, $quantity = 0)
    {
        //get badge properties
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'stackable',
            'substract',
            'quantity'
        ));
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'badge_id' => $badgeId,
            'deleted' => false
        ));
        $this->mongo_db->limit(1);
        $badgeInfo = $this->mongo_db->get('playbasis_badge_to_client');
        if (!$badgeInfo || !$badgeInfo[0]) {
            return false;
        }
        $badgeInfo = $badgeInfo[0];
        if (!$badgeInfo['quantity']) {
            return false;
        }
        if ($badgeInfo['stackable']) {
            return true;
        }
        //badge not stackable, check if player already have the badge
        $this->mongo_db->where(array(
            'badge_id' => $badgeId,
            'pb_player_id' => $pb_player_id
        ));
        $haveBadge = $this->mongo_db->count('playbasis_reward_to_player');
        if ($haveBadge) {
            return false;
        }
        return true;
    }

    private function checkGoodsWithCache(&$cache, $goodsId, $pb_player_id, $site_id, $quantity = 0)
    {
        $key = $goodsId . '-' . $pb_player_id . '-' . $site_id . '-' . $quantity;
        if (!array_key_exists($key, $cache)) {
            $value = $this->checkGoods($goodsId, $pb_player_id, $site_id, $quantity);
            $cache[$key] = $value;
        }
        return $cache[$key];
    }

    private function checkGoods($goodsId, $pb_player_id, $site_id, $quantity = 0)
    {
        if (!$quantity) {
            return true;
        }
        $goods = $this->getGoods($site_id, $goodsId);
        if (!$goods) {
            return false;
        }
        $total = isset($goods['group']) ? $this->getGroupQuantity($site_id, $goods['group']) : $goods['quantity'];
        $max = $goods['per_user'];
        $used = $this->getPlayerGoods($site_id, $goodsId, $pb_player_id);
        if ($total === 0 || $max === 0) {
            return false;
        }
        if ($total && $quantity > $total) {
            return false;
        }
        if (!$max) {
            return true;
        }
        return $used + $quantity <= $max;
    }

    private function checkReward($rewardId, $siteId, $quantity = 0)
    {
        $this->set_site_mongodb($siteId);
        $this->mongo_db->select(array('limit'));
        $this->mongo_db->where(array(
            'reward_id' => $rewardId,
            'site_id' => $siteId
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_reward_to_client');
        if (!$result) {
            return false;
        }
        $result = $result[0];
        if (is_null($result['limit'])) {
            return true;
        }

        return $result['limit'] > 0;
    }

    private function getGroupQuantity($site_id, $group)
    {
        $results = $this->mongo_db->aggregate('playbasis_goods_to_client', array(
            array(
                '$match' => array(
                    'deleted' => false,
                    'site_id' => $site_id,
                    'group' => $group
                ),
            ),
            array(
                '$project' => array('group' => 1, 'quantity' => 1)
            ),
            array(
                '$group' => array('_id' => array('group' => '$group'), 'quantity' => array('$sum' => '$quantity'))
            ),
        ));
        $res = $results ? $results['result'] : array();
        return $res ? $res[0]['quantity'] : $res;
    }

    public function getGoods($site_id, $goodsId)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'goods_id',
            'name',
            'description',
            'image',
            'per_user',
            'quantity',
            'group',
            'code'
        ));
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'goods_id' => $goodsId,
            '$and' => array(
                array(
                    '$or' => array(
                        array('date_start' => array('$lte' => $this->new_mongo_date(date('Y-m-d')))),
                        array('date_start' => null)
                    )
                ),
                array(
                    '$or' => array(
                        array(
                            'date_expire' => array(
                                '$gte' => $this->new_mongo_date(date('Y-m-d'), '23:59:59')
                            )
                        ),
                        array('date_expire' => null)
                    )
                )
            ),
            'status' => true,
            'deleted' => false
        ));
        $this->mongo_db->limit(1);
        $ret = $this->mongo_db->get("playbasis_goods_to_client");
        return $ret && isset($ret[0]) ? $ret[0] : array();
    }

    private function getPlayerGoods($site_id, $goodsId, $pb_player_id)
    {
        $this->mongo_db->select(array('value'));
        $this->mongo_db->where(array(
            'site_id' => $site_id,
            'goods_id' => $goodsId,
            'pb_player_id' => $pb_player_id
        ));
        $this->mongo_db->limit(1);
        $goods = $this->mongo_db->get('playbasis_goods_to_player');
        return isset($goods[0]) ? $goods[0]['value'] : null;
    }

    private function checkRedeemPoint($site_id, $rewardId, $pb_player_id, $quantity = 0)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'reward_id' => new MongoId($rewardId),
            'pb_player_id' => $pb_player_id,
        ));
        if ($quantity) {
            $this->mongo_db->where_gte('value', $quantity);
        }
        return $this->mongo_db->count('playbasis_reward_to_player');
    }

    private function checkRedeemBadge($site_id, $badgeId, $pb_player_id, $quantity = 0)
    {
        $this->set_site_mongodb($site_id);
        $this->mongo_db->where(array(
            'badge_id' => new MongoId($badgeId),
            'pb_player_id' => $pb_player_id,
        ));
        if ($quantity) {
            $this->mongo_db->where_gte('value', $quantity);
        }
        return $this->mongo_db->count('playbasis_reward_to_player');
    }

    /* copied over from client_model */
    private function updatePlayerPointReward(
        $client_id,
        $site_id,
        $rewardId,
        $pb_player_id,
        $cl_player_id,
        $quantity = 0
    ) {
        $this->set_site_mongodb($site_id);

        // check anonymous player
        $this->mongo_db->where('_id', $pb_player_id);
        $anon_result = $this->mongo_db->get('playbasis_player');
        $anon_flag = isset($anon_result[0]['anonymous_flag']) ? $anon_result[0]['anonymous_flag'] : false;

        // update player reward table
        $this->mongo_db->where(array(
            'pb_player_id' => $pb_player_id,
            'reward_id' => $rewardId
        ));
        $hasReward = $this->mongo_db->count('playbasis_reward_to_player');
        if ($hasReward) {
            $this->mongo_db->where(array(
                'pb_player_id' => $pb_player_id,
                'reward_id' => $rewardId
            ));
            $this->mongo_db->set('date_modified', new MongoDate(time()));
            $this->mongo_db->dec('value', intval($quantity));
            $this->mongo_db->update('playbasis_reward_to_player');
        } else {
            $mongoDate = new MongoDate(time());
            $this->mongo_db->insert('playbasis_reward_to_player', array(
                'pb_player_id' => $pb_player_id,
                'cl_player_id' => $cl_player_id,
                'client_id' => $client_id,
                'site_id' => $site_id,
                'reward_id' => $rewardId,
                'value' => intval($quantity),
                'date_added' => $mongoDate,
                'date_modified' => $mongoDate
            ));
        }

        // update client reward limit
        if ($anon_flag) {
            return;
        }
        $this->mongo_db->select(array('limit'));
        $this->mongo_db->where(array(
            'reward_id' => $rewardId,
            'site_id' => $site_id
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_reward_to_client');
        assert($result);
        $result = $result[0];
        if (is_null($result['limit'])) {
            return;
        }
        $this->mongo_db->where(array(
            'reward_id' => $rewardId,
            'site_id' => $site_id
        ));
        $this->mongo_db->dec('limit', intval($quantity));
        $this->mongo_db->update('playbasis_reward_to_client');
    }

    /* copied over from client_model */
    private function updateplayerBadge($client_id, $site_id, $badgeId, $pb_player_id, $cl_player_id, $quantity = 0)
    {
        $this->set_site_mongodb($site_id);

        // check anonymous player
        $this->mongo_db->where('_id', $pb_player_id);
        $anon_result = $this->mongo_db->get('playbasis_player');
        $anon_flag = isset($anon_result[0]['anonymous_flag']) ? $anon_result[0]['anonymous_flag'] : false;

        // update badge master table
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array(
            'substract',
            'quantity',
            'claim',
            'redeem'
        ));
        $this->mongo_db->where(array(
            'client_id' => $client_id,
            'site_id' => $site_id,
            'badge_id' => $badgeId,
            'deleted' => false
        ));
        $this->mongo_db->limit(1);
        $result = $this->mongo_db->get('playbasis_badge_to_client');
        if (!$result) {
            return;
        }
        $badgeInfo = $result[0];
        $mongoDate = new MongoDate(time());
        if (!$anon_flag && isset($badgeInfo['substract']) && $badgeInfo['substract']) {
            $remainingQuantity = (int)$badgeInfo['quantity'] - (int)$quantity;
            if ($remainingQuantity < 0) {
                $remainingQuantity = 0;
                $quantity = $badgeInfo['quantity'];
            }
            $this->mongo_db->set('quantity', $remainingQuantity);
            $this->mongo_db->set('date_modified', $mongoDate);
            $this->mongo_db->where('client_id', $client_id);
            $this->mongo_db->where('site_id', $site_id);
            $this->mongo_db->where('badge_id', $badgeId);
            $this->mongo_db->update('playbasis_badge_to_client');
        }

        // update player badge table
        $this->mongo_db->where(array(
            'pb_player_id' => $pb_player_id,
            'badge_id' => $badgeId
        ));
        $hasBadge = $this->mongo_db->count('playbasis_reward_to_player');
        if ($hasBadge) {
            $this->mongo_db->where(array(
                'pb_player_id' => $pb_player_id,
                'badge_id' => $badgeId
            ));
            $this->mongo_db->set('date_modified', $mongoDate);
            if (isset($badgeInfo['claim']) && $badgeInfo['claim']) {
                $this->mongo_db->inc('claimed', intval($quantity));
            } else {
                $this->mongo_db->dec('value', intval($quantity));
            }
            $this->mongo_db->update('playbasis_reward_to_player');
        } else {
            $data = array(
                'pb_player_id' => $pb_player_id,
                'cl_player_id' => $cl_player_id,
                'client_id' => $client_id,
                'site_id' => $site_id,
                'badge_id' => $badgeId,
                'redeemed' => 0,
                'date_added' => $mongoDate,
                'date_modified' => $mongoDate
            );
            if (isset($badgeInfo['claim']) && $badgeInfo['claim']) {
                $data['value'] = 0;
                $data['claimed'] = intval($quantity);
            } else {
                $data['value'] = intval($quantity);
                $data['claimed'] = 0;
            }
            $this->mongo_db->insert('playbasis_reward_to_player', $data);
        }
    }

//	private function matchUrl($inputUrl, $compareUrl, $isRegEx)
    private function matchUrl($inputUrl, $compareUrl)
    {
        // return (boolean) $this->matchUrl($input['url'], $config['url'], $config['regex']);

        $urlFragment = parse_url($inputUrl);
        //check posible index page
        if (!$urlFragment['path']) {
            $inputUrl = '/';
        }
        //if($urlFragment['path'] == '/')
        //	$inputUrl = '/';
        if (preg_match('/\/index\.[a-zA-Z]{3,}$/', $urlFragment['path'])) // match all "/index.*"
        {
            $inputUrl = '/';
        }
        if (preg_match('/\/index\.[a-zA-Z]{3,}\/$/', $urlFragment['path'])) // match all "/index.*/"
        {
            $inputUrl = '/';
        }
        //check query
        if (isset($urlFragment['query']) && $urlFragment['query']) {
            $inputUrl .= '?' . $urlFragment['query'];
        }
        //check fragment
        if (isset($urlFragment['fragment']) && $urlFragment['fragment']) {
            $inputUrl .= '#' . $urlFragment['fragment'];
        }
        //compare url
//      if($isRegEx){
//          if ($compareUrl == '*') $compareUrl = '.*'; // quick-fix for handling a case of '*' pattern
//          if(!preg_match('/^\//', $compareUrl))
//              $compareUrl = "/".$compareUrl;
//          if(!preg_match('/\/$/', $compareUrl))
//              $compareUrl = $compareUrl."/";
//          $match = preg_match($compareUrl, $inputUrl);
//      }else{
//          $match = (string) $compareUrl === (string) $inputUrl;
//      }

        $match = (string)$compareUrl === (string)$inputUrl;

        return $match;
        //e.g.
        //inputurl domain/forum/hello-my-new-notebook
        //input domain/forum/test1234
        //url = domain/forum/(a-zA-Z0-9\_\-)+
    }

    public function calculateFrequency($from = null, $to = null)
    {
        ini_set('memory_limit', -1);
        $date_added = array();
        if ($from) {
            $date_added['$gt'] = $from;
        }
        if ($to) {
            $date_added['$lt'] = $to;
        }
        $default = array('action_log_id' => array('$exists' => 1));
        $match = array_merge($date_added ? array('date_added' => $date_added) : array(), $default);
        $results = $this->mongo_db->aggregate('jigsaw_log',
            array(
                array(
                    '$match' => $match
                ),
                array(
                    '$project' => array(
                        'client_id' => 1,
                        'site_id' => 1,
                        'action_log_id' => 1,
                        'rule_id' => 1,
                        'date_added' => 1
                    )
                ),
                array(
                    '$group' => array(
                        '_id' => array('action_log_id' => '$action_log_id', 'rule_id' => '$rule_id'),
                        'n' => array('$sum' => 1),
                        'client_id' => array('$first' => '$client_id'),
                        'site_id' => array('$first' => '$site_id'),
                        'date_added' => array('$max' => '$date_added')
                    )
                ),
            )
        );
        return $results ? $results['result'] : array();
    }

    public function storeFrequency($data)
    {
        return $this->mongo_db->insert('jigsaw_log_precomp', array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'action_log_id' => $data['_id']['action_log_id'],
            'rule_id' => $data['_id']['rule_id'],
            'n' => $data['n'],
            'date_added' => $data['date_added'],
            'date_modified' => $data['date_added'],
        ), array('w' => 0, 'j' => false));
    }

    public function getLastCalculateFrequencyTime()
    {
        $this->mongo_db->select(array('date_added'));
        $this->mongo_db->order_by(array('date_added' => -1));
        $this->mongo_db->limit(1);
        $results = $this->mongo_db->get('jigsaw_log_precomp');
        return $results ? $results[0]['date_added'] : array();
    }

    public function groupOr($config, $input, &$exInfo = array())
    {
        $return_val = false;
        $condition_group = $config['condition_group_container'];
        if (is_array($condition_group)) {
            foreach ($condition_group as $con) {
                if (array_key_exists('condition_id', $con)) {
                    $jigsaw_id = new MongoId($con['condition_id']);
                    $processor = $this->getJigsawProcessor($jigsaw_id, $input['site_id']);
                    $jigsaw_result = $this->$processor($con, $input, $exInfo = array());
                    if ($jigsaw_result == true) {
                        $return_val = true;
                        break;
                    }
                }
            }
        }
        return $return_val; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    public function groupNot($config, $input, &$exInfo = array())
    {
        $return_val = true;
        $condition_group = $config['condition_group_container'];
        if (is_array($condition_group)) {
            foreach ($condition_group as $con) {
                if (array_key_exists('condition_id', $con)) {
                    $jigsaw_id = new MongoId($con['condition_id']);
                    $processor = $this->getJigsawProcessor($jigsaw_id, $input['site_id']);
                    $jigsaw_result = $this->$processor($con, $input, $exInfo = array());
                    if ($jigsaw_result == true) {
                        $return_val = false;
                        break;
                    }
                }
            }
        }
        return $return_val; // can reach this line if (1) there is no entry (2) all entries are invalid
    }

    /* copied over from client_model */
    private function getJigsawProcessor($jigsawId, $site_id)
    {
        assert($jigsawId);
        $this->set_site_mongodb($site_id);
        $this->mongo_db->select(array('class_path'));
        $this->mongo_db->where(array(
            'jigsaw_id' => $jigsawId
        ));
        $this->mongo_db->limit(1);
        $jigsawProcessor = $this->mongo_db->get('playbasis_game_jigsaw_to_client');
        if ($jigsawProcessor) {
            assert($jigsawProcessor);
            return $jigsawProcessor[0]['class_path'];
        } else {
            return null;
        }
    }

}

?>