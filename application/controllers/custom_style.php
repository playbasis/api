<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Custom_style extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_style_model');
        $this->load->model('tool/utility', 'utility');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    /**
     * @SWG\Get(
     *     tags={"Style"},
     *     path="/Style",
     *     description="Retrieve custom style by specified filter fields",
     *     @SWG\Parameter(
     *         name="name",
     *         in="query",
     *         type="string",
     *         description="Name of group to retrieve custom style",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="key",
     *         in="query",
     *         type="string",
     *         description="Name of key name to retrieve custom style",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="sort",
     *         in="query",
     *         type="string",
     *         description="Field to sort (e.g. name, key, date_added, date_modified)",
     *         required=false,
     *         default="name"
     *     ),
     *     @SWG\Parameter(
     *         name="order",
     *         in="query",
     *         type="string",
     *         description="Field to sort (e.g. name, key, date_added, date_modified)",
     *         required=false,
     *         default="asc",
     *         enum={"asc", "desc", "random"}
     *     ),
     *     @SWG\Parameter(
     *         name="offset",
     *         in="query",
     *         type="integer",
     *         description="Specify paging offset | default = 0",
     *         required=false,
     *         default=0,
     *     ),
     *     @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         type="integer",
     *         description="Specify paging limit | default = 20",
     *         required=false,
     *         default=20
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function list_get()
    {
        $this->benchmark->mark('start');
        $query_data = $this->input->get(null, true);

        $result = $this->custom_style_model->retrieveStyle($this->validToken['client_id'],
            $this->validToken['site_id'], $query_data);

        array_walk_recursive($result, array($this, "convert_mongo_object_and_optional"));
        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $result, 'processing_time' => $t)), 200);
    }

    /**
     * Use with array_walk and array_walk_recursive.
     * Recursive iterable items to modify array's value
     * from MongoId to string and MongoDate to readable date
     * @param string $key
     */
    private function convert_mongo_object_and_optional(&$item)
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