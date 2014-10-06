<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

function index_weight($obj) {
    return $obj['weight'];
}

function index_quiz_id($obj) {
    return $obj['quiz_id'];
}

function convert_MongoId_id($obj) {
    $_id = $obj['_id'];
    unset($obj['_id']);
    $obj['id'] = $_id->{'$id'};
    return $obj;
}

function convert_MongoId_quiz_id($obj) {
    $_id = $obj['quiz_id'];
    unset($obj['quiz_id']);
    $obj['id'] = $_id->{'$id'};
    return $obj;
}

function convert_MongoId_question_id($obj) {
    $_id = $obj['question_id'];
    unset($obj['question_id']);
    $obj['id'] = $_id->{'$id'};
    return $obj;
}

function convert_MongoId_option_id($obj) {
    $_id = $obj['option_id'];
    unset($obj['option_id']);
    $obj['id'] = $_id->{'$id'};
    return $obj;
}

class Quiz extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('client_model');
        $this->load->model('player_model');
        $this->load->model('quiz_model');
        $this->load->model('reward_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/utility', 'utility');
        $this->load->model('tool/respond', 'resp');
        $this->load->model('tool/node_stream', 'node');
        $this->load->model('tracker_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function list_get()
    {
        $this->benchmark->mark('start');

        $result = null;
        $nin = null;

        /* param "player_id" */
        $player_id = $this->input->get('player_id');
        if ($player_id !== false) {
            $pb_player_id = $this->player_model->getPlaybasisId(array(
                'client_id' => $this->client_id,
                'site_id' => $this->site_id,
                'cl_player_id' => $player_id,
            ));
            if (!$pb_player_id) $this->response($this->error->setError('USER_NOT_EXIST'), 200);
            $arr = $this->quiz_model->find_quiz_done_by_player($this->client_id, $this->site_id, $pb_player_id);
            $nin = array_map('index_quiz_id', $arr);
        }

        $result = $this->quiz_model->find($this->client_id, $this->site_id, $nin);
        $result = array_map('convert_MongoId_id', $result);

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $result, 'processing_time' => $t)), 200);
    }

    public function detail_get($quiz_id)
    {
        $this->benchmark->mark('start');

        /* param "quiz_id" */
        if (empty($quiz_id)) $this->response($this->error->setError('PARAMETER_MISSING', array('quiz_id')), 200);
        $quiz_id = new MongoId($quiz_id);
        $result = $this->quiz_model->find_by_id($this->client_id, $this->site_id, $quiz_id);
        if ($result === null) $this->response($this->error->setError('QUIZ_NOT_FOUND'), 200);

        $result = convert_MongoId_id($result);
        $result['date_start'] = $result['date_start'] ? $result['date_start']->sec : null;
        $result['date_expire'] = $result['date_expire'] ? $result['date_expire']->sec : null;
        $questions = $result['questions'];
        $total_max_score = 0;
        if (is_array($questions)) foreach ($questions as $question) {
            $total_max_score += $this->get_max_score_of_question($question['options']);
        }
        $result['total_max_score'] = $total_max_score;
        unset($result['questions']);

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $result, 'processing_time' => $t)), 200);
    }

    public function random_get()
    {
        $this->benchmark->mark('start');

        $result = null;
        $nin = null;

        /* param "player_id" */
        $player_id = $this->input->get('player_id');
        if ($player_id === false) $this->response($this->error->setError('PARAMETER_MISSING', array('player_id')), 200);
        $pb_player_id = $this->player_model->getPlaybasisId(array(
                'client_id' => $this->client_id,
                'site_id' => $this->site_id,
                'cl_player_id' => $player_id,
        ));
        if (!$pb_player_id) $this->response($this->error->setError('USER_NOT_EXIST'), 200);

        $arr = $this->quiz_model->find_quiz_done_by_player($this->client_id, $this->site_id, $pb_player_id);
        $nin = array_map('index_quiz_id', $arr);
        $result = $this->quiz_model->find($this->client_id, $this->site_id, $nin);
        $result = array_map('convert_MongoId_id', $result);

        if ($result) {
            $index = $this->random_weight(array_map('index_weight', $result));
            $result = $result[$index];
        }

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $result, 'processing_time' => $t)), 200);
    }

    public function player_recent_get($player_id, $limit)
    {
        $this->benchmark->mark('start');

        /* param "player_id" */
        if (empty($player_id)) $this->response($this->error->setError('PARAMETER_MISSING', array('player_id')), 200);
        $result = null;
        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'cl_player_id' => $player_id,
        ));
        if (!$pb_player_id) $this->response($this->error->setError('USER_NOT_EXIST'), 200);

        $result = $this->quiz_model->find_quiz_done_by_player($this->client_id, $this->site_id, $pb_player_id, $limit);
        $result = array_map('convert_MongoId_quiz_id', $result);

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $result, 'processing_time' => $t)), 200);
    }

    public function player_pending_get($player_id, $limit)
    {
        $this->benchmark->mark('start');

        /* param "player_id" */
        if (empty($player_id)) $this->response($this->error->setError('PARAMETER_MISSING', array('player_id')), 200);
        $result = null;
        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'cl_player_id' => $player_id,
        ));
        if (!$pb_player_id) $this->response($this->error->setError('USER_NOT_EXIST'), 200);

        $result = $this->quiz_model->find_quiz_pending_by_player($this->client_id, $this->site_id, $pb_player_id, $limit);
        $result = array_map('convert_MongoId_quiz_id', $result);

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $result, 'processing_time' => $t)), 200);
    }

    //public function question_post($quiz_id)
public function question_get($quiz_id)
    {
        $this->benchmark->mark('start');

        /* param "quiz_id" */
        if (empty($quiz_id)) $this->response($this->error->setError('PARAMETER_MISSING', array('quiz_id')), 200);
        $quiz_id = new MongoId($quiz_id);
        $quiz = $this->quiz_model->find_by_id($this->client_id, $this->site_id, $quiz_id);
        if ($quiz === null) $this->response($this->error->setError('QUIZ_NOT_FOUND'), 200);

        /* param "player_id" */
        //$player_id = $this->input->post('player_id');
$player_id = $this->input->get('player_id');
        if ($player_id === false) $this->response($this->error->setError('PARAMETER_MISSING', array('player_id')), 200);
        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'cl_player_id' => $player_id,
        ));
        if (!$pb_player_id) $this->response($this->error->setError('USER_NOT_EXIST'), 200);

        $result = $this->quiz_model->find_quiz_by_quiz_and_player($this->client_id, $this->site_id, $quiz_id, $pb_player_id);
        $completed_questions = $result ? $result['questions'] : array();
        $question = null;
        foreach ($quiz['questions'] as $q) {
            if (!in_array($q['question_id'], $completed_questions)) {
                $question = $q; // get the first question in the quiz that the player has not submitted an answer
                break;
            }
        }
        if ($question) {
            $question = convert_MongoId_question_id($question);
            foreach ($question['options'] as &$option) {
                $option = convert_MongoId_option_id($option);
                unset($option['score']);
                unset($option['explanation']);
            }
        }

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $question, 'processing_time' => $t)), 200);
    }

    //public function answer_post($quiz_id)
public function answer_get($quiz_id)
    {
        $this->benchmark->mark('start');

        /* param "quiz_id" */
        if (empty($quiz_id)) $this->response($this->error->setError('PARAMETER_MISSING', array('quiz_id')), 200);
        $quiz_id = new MongoId($quiz_id);
        $quiz = $this->quiz_model->find_by_id($this->client_id, $this->site_id, $quiz_id);
        if ($quiz === null) $this->response($this->error->setError('QUIZ_NOT_FOUND'), 200);

        /* param "player_id" */
        //$player_id = $this->input->post('player_id');
$player_id = $this->input->get('player_id');
        if ($player_id === false) $this->response($this->error->setError('PARAMETER_MISSING', array('player_id')), 200);
        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'cl_player_id' => $player_id,
        ));
        if (!$pb_player_id) $this->response($this->error->setError('USER_NOT_EXIST'), 200);

        /* param "question_id" */
        //$question_id = $this->input->post('question_id');
$question_id = $this->input->get('question_id');
        if ($question_id === false) $this->response($this->error->setError('PARAMETER_MISSING', array('question_id')), 200);
        $question_id = new MongoId($question_id);
        $question = null;
        $total_max_score = 0;
        foreach ($quiz['questions'] as $q) {
            $total_max_score += $this->get_max_score_of_question($q['options']);
            if ($q['question_id'] == $question_id) {
                $question = $q;
            }
        }
        if (!$question) $this->response($this->error->setError('QUIZ_QUESTION_NOT_FOUND'), 200);

        /* param "option_id" */
        //$option_id = $this->input->post('option_id');
$option_id = $this->input->get('option_id');
        if ($option_id === false) $this->response($this->error->setError('PARAMETER_MISSING', array('option_id')), 200);
        $option_id = new MongoId($option_id);
        $option = null;
        $max_score = -1;
        foreach ($question['options'] as $o) {
            if ($o['score'] > $max_score) $max_score = $o['score'];
            if ($o['option_id'] == $option_id) {
                $option = $o;
            }
        }
        if (!$option) $this->response($this->error->setError('QUIZ_OPTION_NOT_FOUND'), 200);

        /* check to see if the question has already been answered by the player */
        $result = $this->quiz_model->find_quiz_by_quiz_and_player($this->client_id, $this->site_id, $quiz_id, $pb_player_id);
        $completed_questions = $result ? $result['questions'] : array();
        if (in_array($question_id, $completed_questions)) $this->response($this->error->setError('QUIZ_QUESTION_ALREADY_COMPLETED'), 200);

        /* get score from answering that option */
        $score = $option['score'];
        $explanation = $option['explanation'];
        $acc_score = $result ? $result['value'] : 0;
        $total_score = $acc_score + $score;

        /* update player's score */
        $this->quiz_model->update_player_score($this->client_id, $this->site_id, $quiz_id, $pb_player_id, $question_id, $total_score);

        /* if this is the last question, then grade the player's score */
        $grade = null;
        if (count($completed_questions) + 1 >= count($quiz['questions'])) {
            $percent = ($total_score*1.0)/$total_max_score*100;
            foreach ($quiz['grades'] as $g) {
                if ($g['start'] <= $percent && $percent < $g['end']) {
                    $grade = $g;
                    break;
                }
            }
        }

        /* check to see if grade has reward associated with it */
        $rewards = isset($grade["rewards"]) ? $this->update_rewards($this->client_id, $this->site_id, $pb_player_id, $player_id, $grade["rewards"]) : null;
        unset($grade['rewards']);

        /* data */
        $data = array(
            'score' => $score,
            'max_score' => $max_score,
            'explanation' => $explanation,
            'total_score' => $total_score,
            'total_max_score' => $total_max_score,
            'grade' => $grade,
            'rewards' => $rewards
        );

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $data, 'processing_time' => $t)), 200);
    }

    private function update_rewards($client_id, $site_id, $pb_player_id, $cl_player_id, $rewards) {
        $events = array();
        foreach ($rewards as $type => $reward) {
            switch ($type) {
            case 'exp':
                $value = $reward['exp_value'];
                // update player's exp
                $lv = $this->client_model->updateExpAndLevel($value, $pb_player_id, $cl_player_id, array(
                    'client_id' => $client_id,
                    'site_id' => $site_id
                ));
                // if level up
                if ($lv > 0) {
                    array_push($events, array(
                        'event_type' => 'LEVEL_UP',
                        'value' => $lv
                    ));
                }
                array_push($events, array(
                    'event_type' => 'REWARD_RECEIVED',
                    'reward_type' => 'exp',
                    'value' => $value
                ));
                break;
            case 'point':
                $name = 'point';
                $id = $this->reward_model->findByName(array('client_id' => $client_id, 'site_id' => $site_id), $name);
                $value = $reward['point_value'];
                $this->client_model->updateCustomReward($name, $value, array(
                    'client_id' => $client_id,
                    'site_id' => $site_id,
                    'pb_player_id' => $pb_player_id,
                    'player_id' => $cl_player_id
                ), array());
                array_push($events, array(
                    'event_type' => 'REWARD_RECEIVED',
                    'reward_type' => $name,
                    'value' => $value
                ));
                break;
            case 'badge':
                if (is_array($reward)) foreach ($reward as $badge) {
                    $id = $badge['badge_id'];
                    $value = $badge['badge_value'];
                    $this->client_model->updateplayerBadge($id, $value, $pb_player_id, $cl_player_id, $client_id, $site_id);
                    $badgeData = $this->client_model->getBadgeById($id, $site_id);
                    if (!$badgeData) break;
                    array_push($events, array(
                        'event_type' => 'REWARD_RECEIVED',
                        'reward_type' => 'badge',
                        'reward_data' => $badgeData,
                        'value' => $value
                    ));
                }
                break;
            case 'custom':
                if (is_array($reward)) foreach ($reward as $custom) {
                    $name = $this->reward_model->getRewardName(array('client_id' => $client_id, 'site_id' => $site_id), $custom['custom_id']);
                    $id = $custom['custom_id'];
                    $value = $custom['custom_value'];
                    $this->client_model->updateCustomReward($name, $value, array(
                        'client_id' => $client_id,
                        'site_id' => $site_id,
                        'pb_player_id' => $pb_player_id,
                        'player_id' => $cl_player_id
                    ), array());
                    array_push($events, array(
                        'event_type' => 'REWARD_RECEIVED',
                        'reward_type' => $name,
                        'value' => $value
                    ));
                }
                break;
            default:
                log_message('error', 'Unsupported type = '.$type);
                break;
            }
        }
        return $events;
    }

    private function random_weight($weights) {
        if (!is_array($weights) || !(count($weights) > 0)) throw new Exception("$weights is not a non-empty array");
        $sum = 0;
        $acc = array();
        foreach ($weights as $weight) {
            $sum += $weight;
            array_push($acc, $sum);
        }
        $max = $acc[count($acc)-1];
        $ran = rand(0, $max-1);
        foreach ($acc as $i => $value) {
            if ($ran < $value) return $i;
        }
        return 0;
    }

    private function get_max_score_of_question($options) {
        $max = -1;
        if (is_array($options)) foreach ($options as $option) {
            $score = $option['score'];
            if ($score > $max) $max = $score;
        }
        return $max;
    }
}
?>