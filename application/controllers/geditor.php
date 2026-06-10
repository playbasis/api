<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . '/libraries/REST2_Controller.php';

class Geditor extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();

        //load model
        $this->load->model('Editor', 'gameModel');
    }

    private function requireScopedReadRequest()
    {
        if (!empty($this->validToken) && !empty($this->client_id) && !empty($this->site_id)) {
            return;
        }

        if (isset($this->auth_method) && $this->auth_method == 'api_key') {
            $this->response($this->error->setError('INVALID_API_KEY_OR_SECRET'), 200);
        }

        $this->response($this->error->setError('INVALID_TOKEN'), 200);
    }

    // public function index_get(){
    // 	$this->response(array(1,2,3,4,5),200);
    // }

    //get all rule
    //parameter :: site_id,[rule_id]
    public function rules_get($siteId = '', $ruleId = '')
    {
        $this->requireScopedReadRequest();

        //check site_id
        if (empty($siteId)) {
            $data = array(
                'status' => false,
                'message' => 'site_id required',
            );

            $this->response($data, 200);
        }

        if ((string)$siteId !== (string)$this->site_id) {
            $data = array(
                'status' => false,
                'message' => 'site_id invalid',
            );

            $this->response($data, 200);
        }

        //get specific rule
        if (!empty($ruleId)) {
            $rule = $this->gameModel->getScopedRule($ruleId, $this->client_id, $this->site_id);

            $data = array(
                'status' => true,
                'message' => 'success',
                'response' => array(
                    'rules' => $rule,
                ),
            );

            $this->response($data, 200);
        } //get all game rule
        else {
            $rules = $this->gameModel->getScopedRules($this->client_id, $this->site_id);

            $data = array(
                'status' => true,
                'message' => 'success',
                'response' => array(
                    'rules' => $rules,
                ),
            );

            $this->response($data, 200);
        }
    }

    //get game jigsaw
    //parameter :: rule_id
    public function jigsaws_get($ruleId = '')
    {
        $this->requireScopedReadRequest();

        //check rule_id
        if (empty($ruleId)) {
            $data = array(
                'status' => false,
                'message' => 'rule_id required',
            );

            $this->response($data, 200);
        }

        $jigsawSet = $this->gameModel->getScopedJigsaw($ruleId, $this->client_id, $this->site_id);
        if (!is_array($jigsawSet) || !array_key_exists('jigsaw_set', $jigsawSet)) {
            $data = array(
                'status' => false,
                'message' => 'rule_id invalid',
            );

            $this->response($data, 200);
        }

        //unserialize data
        $jigsawSet = $jigsawSet['jigsaw_set'];

        $data = array(
            'status' => true,
            'message' => 'success',
            'response' => array(
                'jigsaws' => $jigsawSet,
            ),
        );

        $this->response($data, 200);

    }


    //update rule status use POST
    public function ruleStatus_post($ruleId = '')
    {
        //check rule_id
        if (empty($ruleId)) {
            $data = array(
                'status' => false,
                'message' => 'rule_id required',
            );

            $this->response($data, 200);
        }

        $this->gameModel->updateRuleStatus($ruleId);

        $data = array(
            'status' => true,
            'message' => 'success',
        );

        $this->response($data, 200);

    }

    //create new rule use POST
    public function addRule_post()
    {

        if (!$this->input->post('client_id')) {
            $data = array(
                'status' => false,
                'message' => 'client_id required',
            );

            $this->response($data, 200);
        }

        if (!$this->input->post('site_id')) {
            $data = array(
                'status' => false,
                'message' => 'site_id required',
            );

            $this->response($data, 200);
        }

        if (!$this->input->post('rule_name')) {
            $data = array(
                'status' => false,
                'message' => 'rule_name required',
            );

            $this->response($data, 200);
        }

        //add new rule
        $rule = $this->gameModel->addRule($this->input->post());

        $data = array(
            'status' => true,
            'message' => 'success',
            'response' => array(
                'rules' => $rule,
            ),
        );

        $this->response($data, 200);
    }

    //update rule data include jigsaw use POST


    //get action that available for client


    //get reward that available for client


    //get jigsaw that available for client


}
