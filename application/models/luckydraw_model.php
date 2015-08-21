<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Luckydraw_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('playbasis');
    }

    /***
     * Get Luckydraws list from database by given timestamp in range between date start and date end
     *
     * @param string $client_id Client ID
     * @param string $site_id  Site ID
     * @param int $timestamp_end Unix timestamp of end date
     * @access public
     * @return array $results
     */
    public function getActiveLuckyDrawsByEndingTimestamp($timestamp_end)
    {
        $this->mongo_db->where('deleted', false);
        $this->mongo_db->where('date_end', array('$lte' => new MongoDate($timestamp_end)));

        $results = $this->mongo_db->get("playbasis_luckydraw_to_client");

        //var_dump($this->mongo_db->last_query());

        $results = $this->getEventsStatus($results);

        return isset($results) ? $results : null;
    }

    private function getEventsStatus($db_results)
    {
        if (is_array($db_results) && !empty($db_results)) { //if is from getLuckyDraws
            foreach ($db_results as &$result) {
                $date_today = time();
                if ($date_today > $result['date_start']->sec
                    && $date_today > $result['date_end']->sec
                ) {
                    $result['status'] = "Done";
                } elseif ($date_today >= $result['date_start']->sec && $date_today <= $result['date_end']->sec) {
                    $result['status'] = "Ongoing";
                } else {
                    $result['status'] = "Planned";
                }
            }
        }
        return $db_results;
    }
}