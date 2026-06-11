<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';
require_once(APPPATH . 'controllers/engine.php');

class import extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('import_model');
        $this->load->model('player_model');
        $this->load->model('tool/utility', 'utility');
        $this->load->model('tool/error_model', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    private function scalarPost($field)
    {
        $value = $this->input->post($field);
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_scalar($value)) {
            $this->response($this->error->setError('PARAMETER_INVALID', array($field)), 200);
        }

        $value = (string)$value;
        return $this->utility->is_not_empty($value) ? $value : null;
    }

    public function importSetting_post()
    {
 /*       $required = $this->input->checkParam(array(
            'email',
            'username'
        ));
        if (!$player_id) {
            array_push($required, 'player_id');
        }
        if ($required) {
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        }

        $client_id = $this->input->post('client_id');
        if ($client_id) {
            $playerInfo['client_id'] = $client_id;
        }

        $site_id = $this->input->post('site_id');
        if ($site_id) {
            $playerInfo['site_id'] = $site_id;
        }
        */

        $playerInfo = array();
        $missing = array();
        $requiredFields = array('name', 'url', 'port', 'user_name', 'password', 'import_type');
        foreach ($requiredFields as $field) {
            $value = $this->scalarPost($field);
            if ($value === null) {
                $missing[] = $field;
                continue;
            }
            $playerInfo[$field] = $value;
        }
        if ($missing) {
            $this->response($this->error->setError('PARAMETER_MISSING', $missing), 200);
        }

        $playerInfo['routine'] = $this->scalarPost('routine');

        $result = $this->import_model->insertData(
        array_merge($this->validToken, $playerInfo), 0);

        return $result;

    }

    public function importSetting_get()
    {
        $data = $this->input->get();

        if ((!isset($data['client_id'])) || (!isset($data['site_id'])) || (!isset($data['import_type']))){
            $this->response($this->error->setError('PARAMETER_MISSING',200));
        }

        $importData = $this->import_model->retrieveDataByImportType($data['client_id'], $data['site_id'], $data['import_type']);
        $this->response($this->resp->setRespond($importData), 200);

    }

    public function processImport_post()
    {
        $importType = $this->scalarPost('import_type');
        if ($importType === null) {
            $this->response($this->error->setError('PARAMETER_MISSING', array('import_type')), 200);
        }

        $importRows = $this->import_model->retrieveDataByImportType($this->client_id, $this->site_id, $importType);
        $importData = $importRows ? $importRows[0] : null;
        if (!$importData) {
            $this->response($this->error->setError('PARAMETER_INVALID', array('import_type')), 200);
        }

        $data = array(
            'client_id' => $importData['client_id'],
            'site_id' => $importData['site_id'],
        );

        if ($importData['import_type'] == ('player')){
            $url = $importData['url'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);
            $jsonData = json_decode($result, true);
            $return = $this->player_model->bulkRegisterPlayer($jsonData, $data, null);
        } elseif ($importData['import_type'] == ('transaction')){

        } elseif ($importData['import_type'] == ('store_org')){

        } else {
            $this->response($this->error->setError('PARAMETER_MISSING'), 200);
        }

        if ($return) {
            $this->response($this->resp->setRespond(), 200);
        } else {
            $this->response($this->error->setError('ANONYMOUS_CANNOT_REFERRAL'), 200);
        }
    }

}
