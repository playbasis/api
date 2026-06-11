<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Location extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('location_model');
        $this->load->model('tool/error_model', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function list_get()
    {
        $data = $this->input->get();
        if (isset($data['status']) && !is_scalar($data['status'])) {
            $this->response($this->error->setError('PARAMETER_INVALID', array('status')), 200);
        }
        if (isset($data['status'])) {
            $data['status'] = (string)$data['status'];
        }

        $location_info = $this->location_model->getLocation($this->client_id, $this->site_id,$data);
        
        array_walk_recursive($location_info, array($this, "convert_mongo_object"));

        $this->response($this->resp->setRespond($location_info), 200);
    }



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
}
