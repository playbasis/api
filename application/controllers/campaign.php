<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Campaign extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('campaign_model');
        $this->load->model('player_model');
        $this->load->model('tool/error_model', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function index_get()
    {
        $campaign_name = $this->input->get('campaign_name');
        $tags = $this->tagsFromQuery();
        $result = $this->campaign_model->getCampaign($this->client_id, $this->site_id, $campaign_name ? $campaign_name: false, $tags);
        foreach ($result as $index => $res){
            unset($result[$index]['_id']);
        }

        array_walk_recursive($result, array($this, "convert_mongo_object_and_optional"));
        $this->response($this->resp->setRespond($result), 200);
    }

    public function activeCampaign_get()
    {
        $tags = $this->tagsFromQuery();
        $result = $this->campaign_model->getActiveCampaign($this->client_id, $this->site_id, $tags);
        if($result){
            unset($result['_id']);
            array_walk_recursive($result, array($this, "convert_mongo_object_and_optional"));
        }

        $this->response($this->resp->setRespond($result), 200);
    }

    private function tagsFromQuery()
    {
        $tags = $this->input->get('tags');
        if (!$tags) {
            return null;
        }
        if (!is_scalar($tags)) {
            $this->response($this->error->setError('PARAMETER_INVALID', array('tags')), 200);
        }
        return explode(',', $tags);
    }

    /**
     * Use with array_walk and array_walk_recursive.
     * Recursive iterable items to modify array's value
     * from MongoId to string and MongoDate to readable date
     * @param mixed $item this is reference
     * @param string $key
     */
    private function convert_mongo_object_and_optional(&$item, $key)
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
