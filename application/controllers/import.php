<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';
require_once(APPPATH . 'controllers/engine.php');

define('IMPORT_MAX_REMOTE_BYTES', 10 * 1024 * 1024);

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

    private function isPublicImportIp($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function isAllowedImportUrl($url)
    {
        if (!is_string($url)) {
            return false;
        }

        $url = trim($url);
        if ($url === '' || strpos($url, "\0") !== false) {
            return false;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, array('http', 'https'), true)) {
            return false;
        }

        if (isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $host = trim($parts['host'], "[] \t\n\r\0\x0B");
        $host_lower = strtolower($host);
        if ($host_lower === 'localhost' || substr($host_lower, -10) === '.localhost') {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->isPublicImportIp($host);
        }

        $ips = @gethostbynamel($host);
        if (!is_array($ips) || empty($ips)) {
            return false;
        }

        foreach ($ips as $ip) {
            if (!$this->isPublicImportIp($ip)) {
                return false;
            }
        }

        return true;
    }

    private function fetchImportUrl($url)
    {
        if (!$this->isAllowedImportUrl($url)) {
            return false;
        }

        $response = '';
        $bytes = 0;
        $ch = curl_init($url);
        if (!$ch) {
            return false;
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($ch, $chunk) use (&$response, &$bytes) {
            $bytes += strlen($chunk);
            if ($bytes > IMPORT_MAX_REMOTE_BYTES) {
                return 0;
            }
            $response .= $chunk;
            return strlen($chunk);
        });

        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTP') && defined('CURLPROTO_HTTPS')) {
            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        }

        if (defined('CURLOPT_REDIR_PROTOCOLS')) {
            curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, 0);
        }

        if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }

        $result = curl_exec($ch);
        $error = curl_errno($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($result === false || $error || $status < 200 || $status >= 300) {
            return false;
        }

        return $response;
    }

    private function isPlayerImportList($jsonData)
    {
        if (!is_array($jsonData) || empty($jsonData)) {
            return false;
        }

        foreach ($jsonData as $row) {
            if (!is_array($row)) {
                return false;
            }
        }

        return true;
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
        $return = false;
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
            $result = isset($importData['url']) ? $this->fetchImportUrl($importData['url']) : false;
            if ($result === false) {
                $this->response($this->error->setError('PARAMETER_INVALID', array('url')), 200);
            }

            $jsonData = json_decode($result, true);
            if (!$this->isPlayerImportList($jsonData)) {
                $this->response($this->error->setError('PARAMETER_INVALID', array('url')), 200);
            }

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
