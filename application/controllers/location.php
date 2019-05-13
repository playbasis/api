<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Location extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('location_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    /**
     * @SWG\Get(
     *     tags={"Location"},
     *     path="/Location",
     *     description="Retrieve location",
     *     @SWG\Parameter(
     *         name="location_id",
     *         in="query",
     *         type="string",
     *         description="Location ID",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="status",
     *         in="query",
     *         type="string",
     *         description="Status to retrieve location",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function list_get()
    {
        $data = $this->input->get();

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