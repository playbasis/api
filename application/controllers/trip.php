<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Trip extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('Trip_model');
        $this->load->model('player_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/respond', 'resp');
    }

    /**
     * @SWG\Get(
     *     tags={"Trip"},
     *     path="/Trip",
     *     description="List of Trips that the player has started.",
     *     @SWG\Parameter(
     *         name="player_id",
     *         in="query",
     *         type="string",
     *         description="Player ID as used in client's website",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="trip_id",
     *         in="query",
     *         type="string",
     *         description="Trip ID",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="finished",
     *         in="query",
     *         type="string",
     *         description="Status of trip",
     *         default="",
     *         required=false,
     *         enum={"", "true", "false"},
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function getTrip_get()
    {
        $this->benchmark->mark('start');
        $client_id = $this->validToken['client_id'];
        $site_id = $this->validToken['site_id'];
        $query_data = $this->input->get();

        if(!isset($query_data['player_id'])){
            $this->response($this->error->setError('PARAMETER_MISSING', 'player_id'), 200);
        }

        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id'],
            'cl_player_id' => $query_data['player_id']
        ));
        if (empty($pb_player_id)) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $finished = null;
        if(isset($query_data['finished']) && $query_data['finished'] == "true"){
            $finished = true;
        } elseif(isset($query_data['finished']) && $query_data['finished'] == "false"){
            $finished = false;
        }
        $trip_id = (isset($query_data['trip_id']) && !empty($query_data['trip_id'])) ? $query_data['trip_id'] : null ;

        $trips = $this->Trip_model->getTrip($client_id, $site_id,  $finished, $pb_player_id, $trip_id );

        $results =array();
        foreach($trips as $trip){
            $results[] = array( 'trip_id'=> $trip['_id']."",
                                'finished'=> $trip['finished'],
                                'date_start' => datetimeMongotoReadable($trip['date_start']),
                                'date_end' => $trip['date_end'] ? datetimeMongotoReadable($trip['date_end']) : "" );
        }

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $results, 'processing_time' => $t)), 200);
    }

    /**
     * @SWG\Post(
     *     tags={"Trip"},
     *     path="/Trip/startTrip",
     *     description="Start trip",
     *     @SWG\Parameter(
     *         name="token",
     *         in="query",
     *         type="string",
     *         description="Access token returned by Playbasis Authentication",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="player_id",
     *         in="query",
     *         type="string",
     *         description="Player ID as used in client's website",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function startTrip_post()
    {
        $this->benchmark->mark('start');
        $client_id = $this->validToken['client_id'];
        $site_id = $this->validToken['site_id'];
        $query_data = $this->input->post();

        if(!isset($query_data['player_id'])){
            $this->response($this->error->setError('PARAMETER_MISSING', 'player_id'), 200);
        }

        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id'],
            'cl_player_id' => $query_data['player_id']
        ));
        if (empty($pb_player_id)) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $trip = $this->Trip_model->getTrip($client_id, $site_id, false, $pb_player_id);
        if($trip){
            $this->response($this->error->setError('TRIP_ALTREADY_STARTED'), 200);
        }
        $result = $this->Trip_model->addTrip($client_id, $site_id, $pb_player_id);

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('trip_id' => $result."", 'processing_time' => $t)), 200);
    }

    /**
     * @SWG\Post(
     *     tags={"Trip"},
     *     path="/Trip/finishTrip",
     *     description="Finish trip",
     *     @SWG\Parameter(
     *         name="token",
     *         in="query",
     *         type="string",
     *         description="Access token returned by Playbasis Authentication",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="player_id",
     *         in="query",
     *         type="string",
     *         description="Player ID as used in client's website",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="drive_log",
     *         in="query",
     *         type="string",
     *         description="Response including driving log",
     *         required=true,
     *         default="false",
     *         enum={"true", "false"},
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function finishTrip_post()
    {
        $this->benchmark->mark('start');
        $client_id = $this->validToken['client_id'];
        $site_id = $this->validToken['site_id'];

        $query_data = $this->input->post();
        if(!isset($query_data['player_id'])){
            $this->response($this->error->setError('PARAMETER_MISSING', 'player_id'), 200);
        }

        $pb_player_id = $this->player_model->getPlaybasisId(array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id'],
            'cl_player_id' => $query_data['player_id']
        ));
        if (empty($pb_player_id)) {
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);
        }

        $trip = $this->Trip_model->getTrip($client_id, $site_id, false, $pb_player_id);
        if(!$trip){
            $this->response($this->error->setError('TRIP_NOT_STARTED'), 200);
        }

        $trip_id =$trip[0]['_id']."";
        $tripLogs = $this->Trip_model->getTripLog($client_id, $site_id, $trip_id, array('distance','speed','speed_limit'));
        $total_distance = 0;
        $total_point = 0;
        if($tripLogs) {
            $previous_distance = floatval($tripLogs[0]['distance']);
            $checkpoint = floatval($tripLogs[0]['distance']); // for checking distance every 1 km
            $km = 1;
            $range = $km++ . " km(s) (" . ($checkpoint) . " - " . ($checkpoint + 1) . ")";
            $array_log = array();
            $array_log[$range]['min_point'] = 1;
            foreach ($tripLogs as $tripLog) {
                $point = 0;
                $current_distance = floatval($tripLog['distance']);

                if ($tripLog['speed'] <= $tripLog['speed_limit']) {
                    $point = 1;
                } else {
                    $point = -(ceil(($tripLog['speed'] - $tripLog['speed_limit']) / 10));
                }

                if ($current_distance < $checkpoint) {//in case blue drive is restarted
                    if ($array_log[$range]['min_point'] > 0) {
                        $array_log[$range]['min_point'] = 0;
                    }
                    $total_point += $array_log[$range]['min_point'];

                    $checkpoint = $current_distance;
                    $range = $km++ . " km(s) (" . ($checkpoint) . " - " . ($checkpoint + 1) . ")";
                    $array_log[$range]['min_point'] = 1;
                } elseif ($current_distance <= $checkpoint + 1) {
                    $total_distance += $current_distance - $previous_distance;
                } else {
                    $total_point += $array_log[$range]['min_point'];

                    $checkpoint = $checkpoint + 1;
                    $range = $km++ . " km(s) (" . ($checkpoint) . " - " . ($checkpoint + 1) . ")";
                    $array_log[$range]['min_point'] = 1;
                    $total_distance += $current_distance - $previous_distance;
                }
                $array_log[$range][] = $current_distance . ", " . $tripLog['speed'] . ", " . $point;
                $previous_distance = $current_distance;
                $array_log[$range]['min_point'] = min($array_log[$range]['min_point'], $point);
            }

            //for the last 1 kilometre
            if ($array_log[$range]['min_point'] == 1) {
                $array_log[$range]['min_point'] = 0;
            }
            $total_point += $array_log[$range]['min_point'];
        }

        //calculate driving score
        $driving_score = 0;
        if($total_point <= 0 || $total_distance <= 0){
            $driving_score = 0;
        }elseif($total_point >= (int)$total_distance){
            $driving_score = 1;
        }else{
            $driving_score = (ceil(($total_point/$total_distance)*20))/20;
        }

        $this->Trip_model->finishTrip($client_id, $site_id, $trip[0]['_id']."");

        $tripResult= array( 'driving_score'=>$driving_score."",
                            'total_distance'=>$total_distance."");

        if(isset($query_data['drive_log']) && $query_data['drive_log'] == "true" && $tripLogs){
            $tripResult += array( 'total_point'=>$total_point,
                                  'log'=>$array_log);
        }


        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $tripResult,'processing_time' => $t)), 200);
    }

    /**
     * @SWG\Post(
     *     tags={"Trip"},
     *     path="/Trip/addTripLog",
     *     description="Log driving status of the trip that the player has started",
     *     @SWG\Parameter(
     *         name="token",
     *         in="query",
     *         type="string",
     *         description="Access token returned by Playbasis Authentication",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="player_id",
     *         in="query",
     *         type="string",
     *         description="Player ID as used in client's website",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="trip_id",
     *         in="query",
     *         type="string",
     *         description="Trip Id",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="speed",
     *         in="query",
     *         type="string",
     *         description="Current speed | (km/h)",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="latitude",
     *         in="query",
     *         type="string",
     *         description="Current latitude",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="longitude",
     *         in="query",
     *         type="string",
     *         description="Current longitude",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="altitude",
     *         in="query",
     *         type="string",
     *         description="Current altitude",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="rpm",
     *         in="query",
     *         type="string",
     *         description="Current RPM",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="distance",
     *         in="query",
     *         type="string",
     *         description="Distance from start",
     *         required=false,
     *     ),
     *     @SWG\Parameter(
     *         name="speed_limit",
     *         in="query",
     *         type="string",
     *         description="Speed limit at the location | (km/h)",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function addTripLog_post()
    {
        $this->benchmark->mark('start');
        $client_id = $this->validToken['client_id'];
        $site_id = $this->validToken['site_id'];

        $query_data = $this->input->post();
        $trip_id = null;
        if(isset($query_data['trip_id'])){
            $trip_id = $query_data['trip_id'];
            $trip_data = $this->Trip_model->getTrip($client_id, $site_id, null, null, $trip_id);
            if (empty($trip_data)) {
                $this->response($this->error->setError('TRIP_NOT_EXIST'), 200);
            }
        }else{
            if(!isset($query_data['player_id'])){
                $this->response($this->error->setError('PARAMETER_MISSING', 'player_id'), 200);
            }

            $pb_player_id = $this->player_model->getPlaybasisId(array(
                'client_id' => $this->validToken['client_id'],
                'site_id' => $this->validToken['site_id'],
                'cl_player_id' => $query_data['player_id']
            ));
            if (empty($pb_player_id)) {
                $this->response($this->error->setError('USER_NOT_EXIST'), 200);
            }
            $trip = $this->Trip_model->getTrip($client_id, $site_id, false, $pb_player_id);
            if(!$trip){
                $this->response($this->error->setError('TRIP_NOT_STARTED'), 200);
            }

            $trip_id =$trip[0]['_id']."";
        }

        unset($query_data['player_id']);
        unset($query_data['token']);

        $query_data += array(
            'client_id' => $client_id,
            'site_id'   => $site_id,
            'trip_id'   => $trip_id
        );


        $this->Trip_model->addTripLog($query_data);

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('processing_time' => $t)), 200);
    }

    /**
     * @SWG\Get(
     *     tags={"Trip"},
     *     path="/Trip/getTripLog",
     *     description="Retrieve driving status log of specific trip Id",
     *     @SWG\Parameter(
     *         name="player_id",
     *         in="query",
     *         type="string",
     *         description="Player ID as used in client's website",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="trip_id",
     *         in="query",
     *         type="string",
     *         description="Trip Id",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="OK",
     *     )
     * )
     */
    public function getTripLog_get()
    {
        $this->benchmark->mark('start');
        $client_id = $this->validToken['client_id'];
        $site_id = $this->validToken['site_id'];

        $query_data = $this->input->get();
        $trip_id = null;
        if(isset($query_data['trip_id'])){
            $trip_id = $query_data['trip_id'];
            $trip_data = $this->Trip_model->getTrip($client_id, $site_id, null, null, $trip_id);
            if (empty($trip_data)) {
                $this->response($this->error->setError('TRIP_NOT_EXIST'), 200);
            }
        }else{
            if(!isset($query_data['player_id'])){
                $this->response($this->error->setError('PARAMETER_MISSING', 'player_id'), 200);
            }

            $pb_player_id = $this->player_model->getPlaybasisId(array(
                'client_id' => $this->validToken['client_id'],
                'site_id' => $this->validToken['site_id'],
                'cl_player_id' => $query_data['player_id']
            ));
            if (empty($pb_player_id)) {
                $this->response($this->error->setError('USER_NOT_EXIST'), 200);
            }
            $trip = $this->Trip_model->getTrip($client_id, $site_id, false, $pb_player_id);
            if(!$trip){
                $this->response($this->error->setError('TRIP_NOT_STARTED'), 200);
            }

            $trip_id =$trip[0]['_id']."";
        }

        $tripLogs = $this->Trip_model->getTripLog($client_id, $site_id, $trip_id);
        foreach($tripLogs as &$tripLog){
            $tripLog['timestamp'] =  datetimeMongotoReadable($tripLog['timestamp']);
        }


        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $tripLogs, 'processing_time' => $t)), 200);
    }
}