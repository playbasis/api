<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Content extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('content_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function list_get()
    {
        $this->benchmark->mark('start');

        $query_data = $this->input->get(null, true);

        if (isset($query_data['id'])) {
            try{
                $query_data['id'] = new MongoId($query_data['id']);
            }catch (Exception $e){
                $this->response($this->error->setError('PARAMETER_INVALID', array('id')), 200);
            }
        }

        $contents = $this->content_model->retrieveContent($this->client_id, $this->site_id, $query_data);
        if (empty($contents)) {
            $this->response($this->error->setError('CONTENT_NOT_FOUND'), 200);
        }

        array_walk_recursive($contents, array($this, "convert_mongo_object"));

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $contents, 'processing_time' => $t)), 200);
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
}