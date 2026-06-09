<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class CMS extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('client_model');
        $this->load->model('player_model');
        $this->load->model('CMS_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    public function getArticles_get()
    {
        $category = $this->input->get('category');
        $type = $this->input->get('type');
        $paging = $this->input->get('paging');
        $page = $this->input->get('page');

        $data = array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'type' => $type,
            'category' => $category,
            'paging' => $paging,
            'page' => $page
        );

        $results = $this->CMS_model->listArticles($data);
        $this->response($this->resp->setRespond($results), 200);
    }

    public function getArticle_get($article_id = null)
    {
        if ($article_id === null || $article_id === '') {
            $this->response($this->error->setError('PARAMETER_MISSING', array('article_id')), 200);
        }
        if (!is_scalar($article_id)) {
            $this->response($this->error->setError('PARAMETER_INVALID', array('article_id')), 200);
        }

        $data = array(
            'client_id' => $this->client_id,
            'site_id' => $this->site_id,
            'id' => (string)$article_id
        );
        $results = $this->CMS_model->getArticleByID($data);
        $this->response($this->resp->setRespond($results), 200);


    }
}
