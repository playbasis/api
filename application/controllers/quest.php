<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST2_Controller.php';

class Quest extends REST2_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('auth_model');
        $this->load->model('action_model');
        $this->load->model('player_model');
        $this->load->model('client_model');
        $this->load->model('tracker_model');
        $this->load->model('point_model');
        $this->load->model('social_model');
        $this->load->model('quest_model');
        $this->load->model('reward_model');
        $this->load->model('email_model');
        $this->load->model('sms_model');
        $this->load->model('tool/error', 'error');
        $this->load->model('tool/utility', 'utility');
        $this->load->model('tool/respond', 'resp');
        $this->load->model('tool/node_stream', 'node');
    }

    public function QuestProcess($pb_player_id, $validToken, $test_id=NULL){
        $this->load->helper('vsort');

        $client_id = $validToken["client_id"];
        $site_id = $validToken["site_id"];
        $domain_name = $validToken['domain_name'];

        if ($test_id)
            $quests = $this->player_model->getQuestsByID($site_id, $test_id);
        else
            $quests = $this->player_model->getAllQuests($pb_player_id, $site_id, "join");

        $questEvent = array();

        $questResult = array(
            'events_missions' => array(),
            'events_quests' => array()
        );

        /* [quest usage] check quest usage against the associated plan */
        if (!$test_id) {
            try {
                $this->client_model->permissionCheck(
                    $client_id,
                    $site_id,
                    "others",
                    "quest_usage"
                );
            } catch (Exception $e) {
                /* we have to suppress LIMIT_EXCEED exception here because this quest processing is just a part of /Engine/rule call */
                if ($e->getMessage() === "LIMIT_EXCEED") return $questResult; // stop processing next quest as we already hit the plan usage
                throw $e; // otherwise, throw the error
            }
        }

        $badge_player_check = array();
        $player_badges = $this->player_model->getBadge($pb_player_id, $site_id);
        if($player_badges){
            foreach($player_badges as $b){
                $badge_player_check[$b["badge_id"]] = $b["amount"];
            }
        }

        $completeMissionActionId = $this->action_model->findAction(array(
            'client_id' => $validToken['client_id'],
            'site_id' => $validToken['site_id'],
            'action_name' => COMPLETE_MISSION_ACTION,
        ));

        foreach($quests as $q){

            $missionEvent = array();

            $data = array(
                "client_id" => $validToken['client_id'],
                "site_id" => $validToken['site_id'],
                "quest_id" => $q["quest_id"]
            );

            $quest = $this->quest_model->getQuest($data, $test_id);

            if (!$test_id)
                $event_of_quest = $this->checkConditionQuest($quest, $pb_player_id, $validToken);
            else
                $event_of_quest = array();

            $mission_count = count($quest["missions"]);
            $player_finish_count = 0;

            if(!(count($event_of_quest) > 0)){
                $player_missions = array();
                foreach($q["missions"] as $pm){
                    $player_missions[$pm["mission_id"].""] = isset($pm["status"])?$pm["status"]:"unjoin";
                }

                if((bool)$quest["mission_order"]){
                    $missions = vsort($quest["missions"], "mission_number");
                    $first_mission = key($missions);

                    //for check first mission of player that will be automatic join
                    $mission_status_check_unjoin = array("join", "finish");
                    if(isset($player_missions[$missions[$first_mission]["mission_id"].""])
                        && !in_array($player_missions[$missions[$first_mission]["mission_id"].""], $mission_status_check_unjoin)){
                            if (!$test_id)
                                $this->updateMissionStatusOfPlayer($pb_player_id, $q["quest_id"], $missions[$first_mission]["mission_id"], $validToken, "join");
                            $player_missions[$missions[$first_mission]["mission_id"].""] = "join";
                    }

                    $next_mission = false;

                    foreach($missions as $m){

                        //if player pass mission so next mission status will change to join
                        if($next_mission && $player_missions[$m["mission_id"].""] == "unjoin"){
                            if (!$test_id)
                                $this->updateMissionStatusOfPlayer($pb_player_id, $q["quest_id"], $m["mission_id"], $validToken, "join");
                            $player_missions[$m["mission_id"].""] = "join";
                            $next_mission = false;
                        }

                        if(isset($player_missions[$m["mission_id"].""]) && $player_missions[$m["mission_id"].""] == "join"){
                            //echo "join";
                            if (!$test_id)
                                $event_of_mission = $this->checkCompletionMission($quest, $m, $pb_player_id, $validToken, $badge_player_check);
                            else
                                $event_of_mission = array();

                            if(!(count($event_of_mission) > 0)){

                                if (!$test_id) {
                                    $this->updateMissionStatusOfPlayer($pb_player_id, $q["quest_id"], $m["mission_id"], $validToken, "finish");
                                    $this->updateMissionRewardPlayer($pb_player_id, $q["quest_id"], $m["mission_id"], $validToken, $questResult);
                                    /* process "feedbacks" */
                                    if (isset($m["feedbacks"]) && is_array($m["feedbacks"])) foreach ($m["feedbacks"] as $feedback) {
                                        $this->processFeedback($feedback["feedback_type"], array(
                                            'client_id' => $client_id,
                                            'site_id' => $site_id,
                                            'pb_player_id' => $pb_player_id,
                                            'input' => array(
                                                'template_id' => $feedback["template_id"],
                                                'subject' => isset($feedback["subject"]) ? $feedback["subject"] : null,
                                            ),
                                        ));
                                    }
                                    /* [quest usage] increase usage value on client's account */
                                    $this->quest_model->insertQuestUsage(
                                        $client_id,
                                        $site_id,
                                        $q["quest_id"],
                                        $m["mission_id"],
                                        $pb_player_id
                                    );
                                    /* fire complete-mission action */
                                    if ($completeMissionActionId) {
                                        $this->tracker_model->trackAction(array(
                                            'client_id' => $client_id,
                                            'site_id' => $site_id,
                                            'pb_player_id' => $pb_player_id,
                                            'action_id' => $completeMissionActionId,
                                            'action_name' => COMPLETE_MISSION_ACTION,
                                            'url' => $m["mission_id"],
                                        ));
                                    }
                                }

                                //for check total mission finish
                                $player_finish_count++;
                                $next_mission = true;
                            }
                        }else if(isset($player_missions[$m["mission_id"].""]) && $player_missions[$m["mission_id"].""] == "finish"){
                            //echo "finish";
                            //for check total mission finish
                            $player_finish_count++;
                            $next_mission = true;
                            continue;
                        }else{
                            //echo "unjoin";
                            break;
                        }

                        $event = array(
                            'mission_id' => $m["mission_id"],
                            'mission_status' => (count($event_of_mission)>0 ? false : true),
                            'mission_events' => $event_of_mission
                        );
                        array_push($missionEvent, $event);
                    }
                }else{

                    foreach($quest["missions"] as $m){

                        if(isset($player_missions[$m["mission_id"].""]) && $player_missions[$m["mission_id"].""] != "finish"){

                            if(isset($player_missions[$m["mission_id"].""]) && $player_missions[$m["mission_id"].""] == "unjoin"){
                                if (!$test_id)
                                    $this->updateMissionStatusOfPlayer($pb_player_id, $q["quest_id"], $m["mission_id"], $validToken,"join");
                            }

                            if (!$test_id)
                                $event_of_mission = $this->checkCompletionMission($quest, $m, $pb_player_id, $validToken, $badge_player_check);
                            else
                                $event_of_mission = array();

                            if(!(count($event_of_mission) > 0)){
                                if (!$test_id) {
                                    $this->updateMissionStatusOfPlayer($pb_player_id, $q["quest_id"], $m["mission_id"], $validToken,"finish");
                                    $this->updateMissionRewardPlayer($pb_player_id, $q["quest_id"], $m["mission_id"], $validToken, $questResult);
                                    /* process "feedbacks" */
                                    if (isset($m["feedbacks"]) && is_array($m["feedbacks"])) foreach ($m["feedbacks"] as $feedback) {
                                        $this->processFeedback($feedback["feedback_type"], array(
                                            'client_id' => $client_id,
                                            'site_id' => $site_id,
                                            'pb_player_id' => $pb_player_id,
                                            'input' => array(
                                                'template_id' => $feedback["template_id"],
                                                'subject' => isset($feedback["subject"]) ? $feedback["subject"] : null,
                                            ),
                                        ));
                                    }
                                    /* [quest usage] increase usage value on client's account */
                                    $this->quest_model->insertQuestUsage(
                                        $client_id,
                                        $site_id,
                                        $q["quest_id"],
                                        $m["mission_id"],
                                        $pb_player_id
                                    );
                                    /* fire complete-mission action */
                                    if ($completeMissionActionId) {
                                        $this->tracker_model->trackAction(array(
                                            'client_id' => $client_id,
                                            'site_id' => $site_id,
                                            'pb_player_id' => $pb_player_id,
                                            'action_id' => $completeMissionActionId,
                                            'action_name' => COMPLETE_MISSION_ACTION,
                                            'url' => $m["mission_id"],
                                        ));
                                    }
                                }
                                //for check total mission finish
                                $player_finish_count++;
                            }
                        }else{
                            //for check total mission finish
                            $player_finish_count++;
                            continue;
                        }

                        $event = array(
                            'mission_id' => $m["mission_id"],
                            'mission_status' => (count($event_of_mission)>0 ? false : true),
                            'mission_events' => $event_of_mission
                        );
                        array_push($missionEvent, $event);
                    }
                }
            }

            if($mission_count == $player_finish_count){
                //echo "finish all mission";
                if (!$test_id) {
                    $this->updateQuestRewardPlayer($pb_player_id, $q["quest_id"], $validToken, $questResult);
                    $this->updateQuestStatusOfPlayer($pb_player_id, $q["quest_id"], $validToken, "finish");
                    /* process "feedbacks" */
                    if (isset($quest["feedbacks"]) && is_array($quest["feedbacks"])) foreach ($quest["feedbacks"] as $feedback) {
                        $this->processFeedback($feedback["feedback_type"], array(
                            'client_id' => $client_id,
                            'site_id' => $site_id,
                            'pb_player_id' => $pb_player_id,
                            'input' => array(
                                'template_id' => $feedback["template_id"],
                                'subject' => isset($feedback["subject"]) ? $feedback["subject"] : null,
                            ),
                        ));
                    }
                    /* [quest usage] increase usage value on client's account */
                    $this->quest_model->insertQuestUsage(
                        $client_id,
                        $site_id,
                        $q["quest_id"],
                        null,
                        $pb_player_id
                    );
                    /* fire complete-quest action */
                    $completeQuestActionId = $this->action_model->findAction(array(
                        'client_id' => $client_id,
                        'site_id' => $site_id,
                        'action_name' => COMPLETE_QUEST_ACTION,
                    ));
                    if ($completeQuestActionId) {
                        $this->tracker_model->trackAction(array(
                            'client_id' => $client_id,
                            'site_id' => $site_id,
                            'pb_player_id' => $pb_player_id,
                            'action_id' => $completeQuestActionId,
                            'action_name' => COMPLETE_QUEST_ACTION,
                            'url' => $q["quest_id"],
                        ));
                    }
                    try {
                        $this->client_model->permissionProcess(
                            $client_id,
                            $site_id,
                            "others",
                            "quest_usage"
                        );
                    } catch (Exception $e) {
                        /* we have to suppress LIMIT_EXCEED exception here because this quest processing is just a part of /Engine/rule call */
                        if ($e->getMessage() === "LIMIT_EXCEED") break; // stop processing next quest as we already hit the plan usage
                        throw $e; // otherwise, throw the error
                    }
                }
            }

            $event = array(
                'quest_id' => $q["quest_id"],
                'quest_status' => (count($event_of_quest)>0 ? false : true),
                'quest_events' => $event_of_quest,
                'missions' => $missionEvent
            );
            array_push($questEvent, $event);
        }

        return $questResult;
    }

    private function checkConditionQuest($quest, $pb_player_id, $validToken){
        if(empty($quest)){
            $event = array(
                'event_type' => 'QUEST_NOT_EXIT',
                'message' => 'quest not exit'
            );
            return $event;
        }
        //read player information
        $player = $this->player_model->readPlayer($pb_player_id, $validToken['site_id'], array(
            'username',
            'first_name',
            'last_name',
            'gender',
            'image',
            'exp',
            'level',
            'date_added',
            'birth_date'
        ));

        $player_badges = $this->player_model->getBadge($pb_player_id, $validToken['site_id']);

        if($player_badges){
            $badge_player_check = array();
            foreach($player_badges as $b){
                $badge_player_check[$b["badge_id"]] = $b["amount"];
            }
        }

        $questEvent = array();

        if($quest && isset($quest["condition"])){

            foreach($quest["condition"] as $c){
                if($c["condition_type"] == "DATETIME_START"){
//                    if($c["condition_value"]->sec > time()){
                    if(strtotime($c["condition_value"]) > time()){
                        $event = array(
                            'event_type' => 'QUEST_DID_NOT_START',
                            'message' => 'quest did not start'
                        );
                        array_push($questEvent, $event);
                    }
                }else if($c["condition_type"] == "DATETIME_END"){
//                    if($c["condition_value"]->sec < time()){
                    if(strtotime($c["condition_value"]) < time()){
                        $event = array(
                            'event_type' => 'QUEST_ALREADY_FINISHED',
                            'message' => 'quest already finished'
                        );
                        array_push($questEvent, $event);
                    }
                }else if($c["condition_type"] == "LEVEL_START"){
                    if((int)$c["condition_value"] > (int)$player['level']){
                        $event = array(
                            'event_type' => 'LEVEL_IS_LOWER',
                            'message' => 'Your level is under satisfied'
                        );
                        array_push($questEvent, $event);
                    }
                }else if($c["condition_type"] == "LEVEL_END"){
                    if((int)$c["condition_value"] < (int)$player['level']){
                        $event = array(
                            'event_type' => 'LEVEL_IS_HIGHER',
                            'message' => 'Your level is abrove satisfied'
                        );
                        array_push($questEvent, $event);
                    }
                }else if($c["condition_type"] == "POINT"){
                    $point_a = $this->player_model->getPlayerPoint($pb_player_id, $c["condition_id"], $validToken['site_id']);

                    if(isset($point_a[0]['value'])){
                        $point = $point_a[0]['value'];
                    }else{
                        $point = 0;
                    }
                    if((int)$c["condition_value"] > (int)$point){
                        $event = array(
                            'event_type' => 'POINT_NOT_ENOUGH',
                            'message' => 'Your point not enough',
                            'incomplete' => array($c["condition_id"]."" => ((int)$c["condition_value"] - (int)$point))
                        );
                        array_push($questEvent, $event);
                    }
                }else if($c["condition_type"] == "CUSTOM_POINT"){
                    $point_a = $this->player_model->getPlayerPoint($pb_player_id, $c["condition_id"], $validToken['site_id']);

                    if(isset($point_a[0]['value'])){
                        $custom_point = $point_a[0]['value'];
                    }else{
                        $custom_point = 0;
                    }
                    if((int)$c["condition_value"] > (int)$custom_point){
                        $event = array(
                            'event_type' => 'CUSTOM_POINT_NOT_ENOUGH',
                            'message' => 'Your point not enough',
                            'incomplete' => array($c["condition_id"]."" => ((int)$c["condition_value"] - (int)$custom_point))
                        );
                        array_push($questEvent, $event);
                    }
                }else if($c["condition_type"] == "BADGE"){
                    if(isset($badge_player_check[$c["condition_id"].""])){
                        $badge = $badge_player_check[$c["condition_id"].""];
                    }else{
                        $badge = 0;
                    }
                    if((int)$badge < (int)$c["condition_value"]){
                        $event = array(
                            'event_type' => 'BADGE_NOT_ENOUGH',
                            'message' => 'user badge not enough',
                            'incomplete' => array($c["condition_id"]."" => ((int)$c["condition_value"] - (int)$badge))
                        );
                        array_push($questEvent, $event);
                    }
                }
            }
        }

        return $questEvent;
    }

    private function checkCompletionMission($quest, $mission, $pb_player_id, $validToken, $badge_player_check=array(), $player_mission=array()){

        if(empty($quest)){
            $event = array(
                'event_type' => 'QUEST_NOT_EXIT',
                'message' => 'quest not exit'
            );
            return $event;
        }
        
        if(isset($mission["status"]) && $mission["status"] == "finish")
            return array();

        if(!$badge_player_check){
            $player_badges = $this->player_model->getBadge($pb_player_id, $validToken['site_id']);

            if($player_badges){
                $badge_player_check = array();
                foreach($player_badges as $b){
                    $badge_player_check[$b["badge_id"]] = $b["amount"];
                }
            }
        }

        $missionEvent = array();

        if(!$player_mission) {
            $obj = $this->player_model->getMission($pb_player_id, $quest["_id"], $mission["mission_id"], $validToken['site_id']);
            $player_mission = $obj["missions"][0];
        }
        $datetime_check = (isset($player_mission["date_modified"]))?$player_mission["date_modified"]:new MongoDate(time());

        if($mission && isset($mission["completion"])){

            foreach($mission["completion"] as $c){

                if($c["completion_type"] == "ACTION"){

                    $action = $this->player_model->getActionCountFromDatetime($pb_player_id, $c["completion_id"], isset($c["completion_filter"])?$c["completion_filter"]:null, $validToken['site_id'], $datetime_check);

                    if((int)$c["completion_value"] > (int)$action["count"]){
                        $event = array(
                            'event_type' => 'ACTION_NOT_ENOUGH',
                            'message' => 'Your action not enough',
                            'incomplete' => array(
                                'incompletion_id' => $c["completion_id"]."",
                                'incompletion_type' => "ACTION",
                                'incompletion_value' => ((int)$c["completion_value"] - (int)$action["count"])
                            )
                        );
                        if(isset($c["completion_element_id"])){
                            $event['incomplete']['incompletion_element_id'] = $c["completion_element_id"]."";
                        }
                        if(isset($c["completion_filter"])){
                            $event['incomplete']['incompletion_filter'] = $c["completion_filter"];
                        }

                        array_push($missionEvent, $event);
                    }
                }
                if($c["completion_type"] == "POINT"){
                    $point_a = $this->player_model->getPlayerPoint($pb_player_id, $c["completion_id"], $validToken['site_id']);

                    if(isset($point_a[0]['value'])){
                        $point = $point_a[0]['value'];
                    }else{
                        $point = 0;
                    }

                    if((int)$c["completion_value"] > (int)$point){
                        $event = array(
                            'event_type' => 'POINT_NOT_ENOUGH',
                            'message' => 'Your point not enough',
                            'incomplete' => array(
                                'incompletion_id' => $c["completion_id"]."",
                                'incompletion_type' => "POINT",
                                'incompletion_value' => ((int)$c["completion_value"] - (int)$point)
                            )
                        );
                        array_push($missionEvent, $event);
                    }
                }
                if($c["completion_type"] == "CUSTOM_POINT"){
                    $point_a = $this->player_model->getPlayerPoint($pb_player_id, $c["completion_id"], $validToken['site_id']);

                    if(isset($point_a[0]['value'])){
                        $custom_point = $point_a[0]['value'];
                    }else{
                        $custom_point = 0;
                    }

                    if((int)$c["completion_value"] > (int)$custom_point){
                        $event = array(
                            'event_type' => 'CUSTOM_POINT_NOT_ENOUGH',
                            'message' => 'Your point not enough',
                            'incomplete' => array(
                                'incompletion_id' => $c["completion_id"]."",
                                'incompletion_type' => "CUSTOM_POINT",
                                'incompletion_value' => ((int)$c["completion_value"] - (int)$custom_point)
                            )
                        );
                        array_push($missionEvent, $event);
                    }
                }
                if($c["completion_type"] == "BADGE"){
                    if(isset($badge_player_check[$c["completion_id"].""])){
                        $badge = $badge_player_check[$c["completion_id"].""];
                    }else{
                        $badge = 0;
                    }
                    if((int)$badge < (int)$c["completion_value"]){
                        $event = array(
                            'event_type' => 'BADGE_NOT_ENOUGH',
                            'message' => 'user badge not enough',
                            'incomplete' => array(
                                'incompletion_id' => $c["completion_id"]."",
                                'incompletion_type' => "BADGE",
                                'incompletion_value' => ((int)$c["completion_value"] - (int)$badge)
                            )
                        );
                        array_push($missionEvent, $event);
                    }
                }
            }
        }

        return $missionEvent;
    }

    private function updateMissionRewardPlayer($player_id, $quest_id, $mission_id, $validToken, &$questResult){

        $data = array(
            "client_id" => $validToken['client_id'],
            "site_id" => $validToken['site_id'],
            "quest_id" => $quest_id,
            "mission_id" => $mission_id
        );

        $mission = $this->quest_model->getMission($data);

        $cl_player_id = $this->player_model->getClientPlayerId($player_id, $validToken['site_id']);

        $sub_events = array(
            "events" => array(),
            "mission_id" => $mission_id."",
            "mission_number" => $mission["missions"][0]["mission_number"],
            "mission_name" => $mission["missions"][0]["mission_name"],
            "description" => $mission["missions"][0]["description"],
            "hint" => $mission["missions"][0]["hint"],
            "image" => $mission["missions"][0]["image"],
            "quest_id" => $quest_id.""
        );

        if(isset($mission["missions"][0]["rewards"])){
            $sub_events = $this->updateReward($mission["missions"][0]["rewards"], $sub_events, $player_id, $cl_player_id, $validToken);
        }

        array_push($questResult['events_missions'], $sub_events);

        return $questResult;
    }

    private function updateQuestRewardPlayer($player_id, $quest_id, $validToken, &$questResult){
        $data = array(
            "client_id" => $validToken['client_id'],
            "site_id" => $validToken['site_id'],
            "quest_id" => $quest_id
        );

        $quest = $this->quest_model->getQuest($data);

        $cl_player_id = $this->player_model->getClientPlayerId($player_id, $validToken['site_id']);

        $sub_events = array(
            "events" => array(),
            "quest_id" => $quest_id."",
            "quest_name" => $quest["quest_name"],
            "description" => $quest["description"],
            "hint" => $quest["hint"],
            "image" => $quest["image"],
        );

        if(isset($quest["rewards"])){
            $sub_events = $this->updateReward($quest["rewards"], $sub_events, $player_id, $cl_player_id, $validToken);
        }

        array_push($questResult['events_quests'], $sub_events);

        return $questResult;
    }

    private function updateReward($array_reward, $sub_events, $player_id, $cl_player_id, $validToken){

        $update_config = array(
            "client_id" => $validToken['client_id'],
            "site_id" => $validToken['site_id'],
            "pb_player_id" => $player_id,
            "player_id" => $cl_player_id
        );

        foreach($array_reward as $r){

            if($r["reward_type"] == "BADGE"){

                $this->client_model->updateplayerBadge($r["reward_id"], $r["reward_value"], $player_id, $cl_player_id, $validToken['client_id'], $validToken['site_id']);
                $badgeData = $this->client_model->getBadgeById($r["reward_id"], $validToken['site_id']);

                if(!$badgeData)
                    break;
                $event = array(
                    'event_type' => 'REWARD_RECEIVED',
                    'reward_type' => 'badge',
                    'reward_data' => $badgeData,
                    'value' => $r["reward_value"]
                );
                array_push($sub_events['events'], $event);
                $eventMessage = $this->utility->getEventMessage('badge', '', '', $event['reward_data']['name']);
                //log event - reward, badge
                $data_reward = array(
                    'reward_type'	=> $r["reward_type"],
                    'reward_id'	    => $r["reward_id"],
                    'reward_name'	=> $event['reward_data']['name'],
                    'reward_value'	=> $r["reward_value"],
                );
                $this->trackQuest($player_id, $validToken, $data_reward, $sub_events["quest_id"], isset($sub_events["mission_id"])?$sub_events["mission_id"]:null);

                //publish to node stream
                $this->node->publish(array_merge($update_config, array(
                    'action_name' => isset($sub_events["mission_id"])?'mission_reward':'quest_reward',
                    'action_icon' => 'fa-trophy',
                    'message' => $eventMessage,
                    'badge' => $event['reward_data'],
                    'mission' => isset($sub_events["mission_id"])?$sub_events:null,
                    'quest' => (!isset($sub_events["mission_id"]))?$sub_events:null
                )), $validToken['domain_name'], $validToken['site_id']);
            }else{
                // for POINT ,CUSTOM_POINT and EXP

                if($r["reward_type"] == "EXP"){
                    //check if player level up
                    $lv = $this->client_model->updateExpAndLevel($r["reward_value"], $player_id, $cl_player_id, array(
                        'client_id' => $validToken['client_id'],
                        'site_id' => $validToken['site_id']
                    ));
                    if($lv > 0)
                    {
                        $eventMessage = $this->levelup($lv, $sub_events, $update_config);
                        //publish to node stream
                        $this->node->publish(array_merge($update_config, array(
                            'action_name' => isset($sub_events["mission_id"])?'mission_reward':'quest_reward',
                            'action_icon' => 'fa-trophy',
                            'message' => $eventMessage,
                            'level' => $lv
                        )), $validToken['domain_name'], $validToken['site_id']);
                    }

                    $reward_type_message = 'point';
                    $reward_type_name = 'exp';
                }else{
                    $reward_config = array(
                        "client_id" => $validToken['client_id'],
                        "site_id" => $validToken['site_id']
                    );
                    $reward_name = $this->reward_model->getRewardName($reward_config, $r["reward_id"]);

                    $return_data = array();
                    $reward_update = $this->client_model->updateCustomReward($reward_name, $r["reward_value"], $update_config, $return_data);

                    $reward_type_message = 'point';
                    $reward_type_name = $return_data['reward_name'];
                }

                $event = array(
                    'event_type' => 'REWARD_RECEIVED',
                    'reward_type' => $reward_type_name,
                    'value' => $r["reward_value"]
                );
                array_push($sub_events['events'], $event);
                $eventMessage = $this->utility->getEventMessage($reward_type_message, $r["reward_value"], $reward_type_name);
                //log event - reward, non-custom point
                $data_reward = array(
                    'reward_type'	=> $r["reward_type"],
                    'reward_id'	    => $r["reward_id"],
                    'reward_name'	=> $reward_type_name,
                    'reward_value'	=> $r["reward_value"],
                );
                $this->trackQuest($player_id, $validToken, $data_reward, $sub_events["quest_id"], isset($sub_events["mission_id"])?$sub_events["mission_id"]:null);

                //publish to node stream
                $this->node->publish(array_merge($update_config, array(
                    'action_name' => isset($sub_events["mission_id"])?'mission_reward':'quest_reward',
                    'action_icon' => 'fa-trophy',
                    'message' => $eventMessage,
                    'amount' => $r["reward_value"],
                    'point' => $reward_type_name,
                    'mission' => isset($sub_events["mission_id"])?$sub_events:null,
                    'quest' => (!isset($sub_events["mission_id"]))?$sub_events:null
                )), $validToken['domain_name'], $validToken['site_id']);
            }

        }

        return $sub_events;
    }

    private function trackQuest($player_id, $validToken, $data_reward, $quest_id, $mission_id=null){
        if($data_reward['reward_type'] == 'CUSTOM_POINT' || $data_reward['reward_type'] == 'EXP'){
            $reward_type = 'point';
        }else{
            $reward_type = strtolower($data_reward['reward_type']);
        }
        $eventMessage = $this->utility->getEventMessage($reward_type,$data_reward['reward_value'],$data_reward['reward_name'],$data_reward['reward_name']);
        $data = array(
            'pb_player_id'	=> $player_id,
            'client_id'		=> $validToken['client_id'],
            'site_id'		=> $validToken['site_id'],
            'quest_id'		=> new MongoId($quest_id),
            'mission_id'	=> $mission_id? new MongoId($mission_id) :null,
            'reward_type'	=> $data_reward['reward_type'],
            'reward_id'	    => $data_reward['reward_id'],
            'reward_name'	=> $data_reward['reward_name'],
            'amount'	    => $data_reward['reward_value'],
            'message'       => $eventMessage
        );
        $this->tracker_model->trackQuest($data);
    }

    private function updateMissionStatusOfPlayer($player_id, $quest_id, $mission_id, $validToken, $status="join"){
        $data = array(
            'site_id' => $validToken['site_id'],
            'pb_player_id' => $player_id,
            'quest_id' => $quest_id,
            'mission_id' => $mission_id
        );
        $this->quest_model->updateMissionStatus($data, $status);
    }

    private function updateQuestStatusOfPlayer($player_id, $quest_id, $validToken, $status="join"){
        $data = array(
            'site_id' => $validToken['site_id'],
            'pb_player_id' => $player_id,
            'quest_id' => $quest_id
        );
        $this->quest_model->updateQuestStatus($data, $status);
    }

    private function levelup($lv, &$sub_events, $config)
    {
        $event = array(
            'event_type' => 'LEVEL_UP',
            'value' => $lv
        );
        array_push($sub_events['events'], $event);
        $eventMessage = $this->utility->getEventMessage('level', '', '', '', $lv);
        //log event - level
        $this->tracker_model->trackEvent('LEVEL', $eventMessage, array_merge($config, array(
            'action_log_id' => null,
            'amount' => $lv
        )));

        return $eventMessage;
    }

    /*public function testQuest_get(){
        //process regular data
        $required = $this->input->checkParam(array(
            'player_id'
        ));
        if($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);
        //get playbasis player id from client player id
        $cl_player_id = $this->input->get('player_id');
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $cl_player_id
        )));

//        $validToken = $this->validToken;
//
//        $this->load->model('service_model');
//        $res = $this->player_model->getPlayerPointFromDateTime($pb_player_id, new MongoId("53c551c69880409d6c8b45ac"), $validToken['site_id'], new MongoDate(strtotime("2014-07-01")));
//        $res = $this->player_model->getLastEventTime($pb_player_id, $validToken['site_id'], "REWARD");
//        $res = $this->player_model->getPointHistoryFromPlayerID($pb_player_id,  $validToken['site_id'], null, 0, 100);
//        $res = $this->player_model->getPointHistoryFromPlayerID($pb_player_id,  $validToken['site_id'], new MongoId("53bbecbb988040116c8b462f"), 0, 100);
//        $res = $this->service_model->getRecentPoint($validToken['site_id'], new MongoId("53c551c69880409d6c8b45ac"), 0, 100);
//        $res = $this->service_model->getRecentPoint($validToken['site_id'], null, 0, 100);

//        $site_id = new MongoId("53a9422f988040355a8b45d3");
//        $reward_id = new MongoId("53bbecbb988040116c8b462f");
//        $starttime = new MongoDate(strtotime("2014-01-01"));
//        $endtime = new MongoDate(strtotime("2014-10-15"));

        $apiResult = $this->QuestProcess($pb_player_id, $this->validToken);
//
        $this->response($this->resp->setRespond($apiResult), 200);
//        $this->response($this->resp->setRespond($res), 200);
    }*/

    /**
     * Check quest available for the player.
     * Quest available depends on quest condition and player information.
     * return empty array if any available quest for the player, otherwise array of quest(s).
     * @param string $quest_id
     * @param string $this->input->get("api_key")
     * @param string $this->input->get("player_id")
     * @return mixed array of quest(s)
     * @throws PARAMETER_MISSING if api_key or player_id is not given
     * @throws INVALID_API_KEY_OR_SECRET if apikey is invalid
     * @throws USER_NOT_EXIST if user not found
     */
    public function available_get($quest_id = 0) {
        // check user exists
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            "cl_player_id" => $this->input->get("player_id")
        )));
        if (!$pb_player_id)
            $this->response($this->error->setError("USER_NOT_EXIST"), 200);

        $data = $this->validToken;

        // get specific available quest
        if ($quest_id) {
            try {
                $quest_id = new MongoId($quest_id);
            } catch(MongoException $ex) {
                $quest_id = null;
            }

            $data['quest_id'] = $quest_id;
            $quest = $this->quest_model->getQuest($data); // get quest detail

            if (!$this->checkConditionQuest($quest, $pb_player_id, $this->validToken)) {
//                array_walk_recursive($quest, array($this, "convert_mongo_object"));
//                $resp["quest"] = $quest;
//                $resp["quest"]["quest_id"] = $quest["_id"];
//                unset($resp["quest"]["_id"]);
//                $this->response($this->resp->setRespond($resp), 200);
                $event = array(
                    'event_type' => "QUEST_AVAILABLE",
                    'event_message' => "quest available",
                    'event_status' => true,
                );
                $this->response($this->resp->setRespond($event), 200);
            } else {
                $event = array(
                    'event_type' => "QUEST_NOT_AVAILABLE",
                    'event_message' => "quest not available",
                    'event_status' => false,
                );
                $this->response($this->resp->setRespond($event), 200);
            }
        } else {
            // get all available quests related to clients
            $resp["quests"] = array();
            $quests = $this->quest_model->getQuests($data);
            foreach ($quests as $quest => $value) {
                // condition failed
                if ($this->checkConditionQuest($quests[$quest], $pb_player_id, $this->validToken))
                    continue;
                else {
                    $quests[$quest]["quest_id"] = $quests[$quest]["_id"];
                    unset($quests[$quest]["_id"]);
                    array_push($resp["quests"], $quests[$quest]);
                }
            }
            array_walk_recursive($resp["quests"], array($this, "convert_mongo_object"));
            $this->response($this->resp->setRespond($resp), 200);
        }
    }

    /**
     * Get quest information.
     * Get paticular quest if quest_id is given,
     * otherwise get all quests for the Client's quest.
     * @param string $quest_id
     * @param string $this->input->get("api_key")
     * @return mixed array of quest(s)
     * @throws PARAMETER_MISSING if api_key is not given
     * @throws INVALID_API_KEY_OR_SECRET if apikey is invalid
     */
    public function index_get($quest_id = 0) {
        $data = $this->validToken;

        if ($quest_id) {
            // get specific quest
            try {
                $quest_id = new MongoId($quest_id);
            } catch(MongoException $ex) {
                $quest_id = null;
            }

            $data['quest_id'] = $quest_id;
            $quest = $this->quest_model->getQuest($data);
            if($quest){
                array_walk_recursive($quest, array($this, "convert_mongo_object"));
                $resp['quest'] = $quest;
                $resp['quest']['quest_id'] = $quest['_id'];
                unset($resp['quest']['_id']);
            }else{
                $resp['quest'] = array();
            }
        } else {
            // get all questss related to clients
            $quest = $this->quest_model->getQuests($data);
            array_walk_recursive($quest, array($this, "convert_mongo_object"));
            foreach ($quest as $key => $value) {
                $quest[$key]['quest_id'] = $quest[$key]['_id'];
                unset($quest[$key]['_id']);
            }
            $resp['quests'] = $quest;
        }
        $this->response($this->resp->setRespond($resp), 200);
    }

    /**
     * Client join particular quest
     * @param string $quest_id
     * @param string $this->input->post("token")
     * @param string $this->input->post("player_id")
     * @return array()
     * @throws PARAMETER_MISSING if token, player_id or quest_id not given
     * @throws INVALID_API_KEY_OR_SECRET if token is invalid
     * @throws USER_NOT_EXIST if user not found
     * @throws QUEST_CONDITION if player did not meet the quest condition
     * @throws QUEST_JOINED if player joined the quest
     * @throws QUEST_FINISHED if player finished this quest
     */
    public function join_post($quest_id = 0) {
        // check put parameter
        $required = $this->input->checkParam(array("player_id"));
        if ($required)
            $this->response($this->error->setError("PARAMETER_MISSING", $required), 200);

        // check quest_id input
        if (!$quest_id)
            $this->response($this->error->setError("PARAMETER_MISSING", array("quest_id")), 200);

        // check user exists
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            "cl_player_id" => $this->input->post("player_id")
        )));
        if (!$pb_player_id)
            $this->response($this->error->setError("USER_NOT_EXIST"), 200);

        try {
            $quest_id = new MongoId($quest_id);
        } catch(MongoException $ex) {
            $quest_id = null;
        }

        $data = array(
            "client_id" => $this->validToken["client_id"],
            "site_id" => $this->validToken["site_id"],
            "pb_player_id" => $pb_player_id,
            "quest_id" => $quest_id
        );

        // get quest detail
        $quest = $this->quest_model->getQuest($data);

        if (!$quest) {
            $this->response($this->error->setError("QUEST_JOIN_OR_CANCEL_NOTFOUND"), 200);
        }

        // check quest_to_client
        $player_quest = $this->quest_model->getPlayerQuest($data);

        // not join yet, let check condition
        if (!$player_quest) {
            $condition_quest = $this->checkConditionQuest($quest, $pb_player_id, $this->validToken);
            // condition passed
            if (!$condition_quest)
                $this->quest_model->joinQuest(array_merge($data, $quest));
            else
                // condition failed
                $this->response($this->error->setError("QUEST_CONDITION"), 200);
        } else {
            // already join, let check quest_to_client status
            if ($player_quest["status"] == "join")
                // joined
                $this->response($this->error->setError("QUEST_JOINED"), 200);
            else if ($player_quest["status"] == "finish")
                // finished
                $this->response($this->error->setError("QUEST_FINISHED"), 200);
            else if ($player_quest["status"] == "unjoin") {
                // unjoin, let him join again
                $this->quest_model->updateQuestStatus($data, "join");
            }
        }
        $this->response($this->resp->setRespond(
            (isset($condition_quest) && $condition_quest) ? $condition_quest : array('events' => array('event_type' => 'QUEST_JOIN', 'quest_id' => $quest_id.""))), 200);
    }

    public function joinAll_post(){
        // check user exists
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            "cl_player_id" => $this->input->post("player_id")
        )));
        if (!$pb_player_id)
            $this->response($this->error->setError("USER_NOT_EXIST"), 200);

        $data = $this->validToken;

        // get all available quests related to clients
        $resp["quests"] = array();
        $quests = $this->quest_model->getQuests($data);

        foreach ($quests as $index => $quest) {

            $data_sub = array(
                "client_id" => $this->validToken["client_id"],
                "site_id" => $this->validToken["site_id"],
                "pb_player_id" => $pb_player_id,
                "quest_id" => $quests[$index]["_id"]
            );

            // check quest_to_client
            $player_quest = $this->quest_model->getPlayerQuest($data_sub);

            // not join yet, let check condition
            if (!$player_quest) {
                $condition_quest = $this->checkConditionQuest($quest, $pb_player_id, $this->validToken);
                // condition passed
                if (!$condition_quest)
                    $this->quest_model->joinQuest(array_merge($data_sub, $quest));

            } else {
                // already join, let check quest_to_client status
                if ($player_quest["status"] == "unjoin") {
                    // unjoin, let him join again
                    $this->quest_model->updateQuestStatus($data_sub, "join");
                }
            }
        }

        $this->response($this->resp->setRespond(array("join_all" => "finish")), 200);
    }

    /**
     * Client cancel particular quest
     * @param string $quest_id
     * @param string $this->input->post("token")
     * @param string $this->input->post("player_id")
     * @return array()
     * @throws PARAMETER_MISSING if token, player_id or quest_id not given
     * @throws INVALID_API_KEY_OR_SECRET if token is invalid
     * @throws USER_NOT_EXIST if user not found
     * @throws QUEST_CANCEL_FAILED if player not yet join the quest or
     *                             already join but cancelled
     * @throws QUEST_FINISHED if player finished this quest
     */
    public function cancel_post($quest_id = 0)
    {
        // check delete parameter
        $required = $this->input->checkParam(array("player_id"));
        if ($required)
            $this->response($this->error->setError("PARAMETER_MISSING", $required), 200);

        // check quest_id input
        if (!$quest_id)
            $this->response($this->error->setError("PARAMETER_MISSING", array("quest_id")), 200);

        // check user exists
        $pb_player_id = $this->player_model->getPlaybasisId(
            array_merge($this->validToken, array(
            "cl_player_id" => $this->input->post("player_id")
        )));
        if (!$pb_player_id)
            $this->response($this->error->setError("USER_NOT_EXIST"), 200);

        try {
            $quest_id = new MongoId($quest_id);
        } catch(MongoException $ex) {
            $quest_id = null;
        }

        $data = array(
            "client_id" => $this->validToken["client_id"],
            "site_id" => $this->validToken["site_id"],
            "pb_player_id" => $pb_player_id,
            "quest_id" => $quest_id
        );

        // check quest_to_client
        $player_quest = $this->quest_model->getPlayerQuest($data);

        // not join yet, cannot cancel
        if (!$player_quest) {
            $this->response($this->error->setError("QUEST_CANCEL_FAILED"), 200);
        } else {
            // already join, let check quest_to_client status
            if ($player_quest["status"] == "join")
                // joined, let unjoin
                $this->quest_model->updateQuestStatus($data, "unjoin");
            else if ($player_quest["status"] == "finish")
                // finished
                $this->response($this->error->setError("QUEST_FINISHED"), 200);
            else if ($player_quest["status"] == "unjoin") {
                // unjoin
                $this->response($this->error->setError("QUEST_CANCEL_FAILED"), 200);
            }
        }
        $this->response($this->resp->setRespond(array('events' => array('event_type' => 'QUEST_UNJOIN', 'quest_id' => $quest_id.""))), 200);
    }

    public function mission_get($quest_id = '', $mission_id = ''){
        $data = $this->validToken;

        if ($quest_id && $mission_id) {
            // get specific quest
            try {
                $quest_id = new MongoId($quest_id);
            } catch(MongoException $ex) {
                $quest_id = null;
            }

            try {
                $mission_id = new MongoId($mission_id);
            } catch(MongoException $ex) {
                $mission_id = null;
            }

            $data['quest_id'] = $quest_id;
            $data['mission_id'] = $mission_id;
            $quest = $this->quest_model->getMission($data);
            if($quest){
                array_walk_recursive($quest, array($this, "convert_mongo_object"));
                $resp = $quest['missions'][0];
                $resp['quest_id'] = $quest['_id'];
                unset($resp['quest']['_id']);
            }else{
                $resp = array();
            }
        }
        $this->response($this->resp->setRespond($resp), 200);
    }

    public function questOfPlayer_get($quest_id = 0)
    {
        $required = $this->input->checkParam(array('player_id'));
        if ($required)
            $this->response($this->error->setError('PARAMETER_MISSING', $required), 200);

        $player_id = $this->input->get('player_id');
        //get playbasis player id
        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));
        if(!$pb_player_id)
            $this->response($this->error->setError('USER_NOT_EXIST'), 200);

        $badge_player_check = array();
        $player_badges = $this->player_model->getBadge($pb_player_id, $this->validToken['site_id']);
        if($player_badges){
            foreach($player_badges as $b){
                $badge_player_check[$b["badge_id"]] = $b["amount"];
            }
        }

        $data = array(
            'client_id' => $this->validToken['client_id'],
            'site_id' => $this->validToken['site_id'],
            'pb_player_id' => $pb_player_id
        );

        if ($quest_id) {
            // get specific quest
            try {
                $quest_id = new MongoId($quest_id);
            } catch(MongoException $ex) {
                $quest_id = null;
            }

            $data['quest_id'] = $quest_id;
            $data['status'] = array("join","finish");
            $quest_player = $this->quest_model->getPlayerQuest($data);

            if($quest_player){
                $quest = $this->quest_model->getQuest(array_merge($data, array('quest_id' => $quest_player['quest_id'])));

                foreach($quest_player["missions"] as $k=>$m){
                    $quest["missions"][$k]["date_modified"] = isset($m["date_modified"])?$m["date_modified"]:"";
                    $quest["missions"][$k]["status"] = isset($m["status"])?$m["status"]:"";
                    $quest["missions"][$k]["pending"] = $this->checkCompletionMission($quest, $m, $pb_player_id, $this->validToken, $badge_player_check, $m);
                }

                $quest['status'] = $quest_player['status'];
                $quest['quest_id'] = $quest_player['quest_id'];
                unset($quest['_id']);

                array_walk_recursive($quest, array($this, "convert_mongo_object"));

                $resp['quest'] = $quest;
            }else{
                $resp['quest'] = array();
            }
        } else {
            // get all questss related to clients
            $data['status'] = array("join","finish");
            $quests_player = $this->quest_model->getPlayerQuests($data);

            $quests = array();
            foreach ($quests_player as $q) {
                $quest = $this->quest_model->getQuest(array_merge($data, array('quest_id' => $q['quest_id'])));

                foreach($q["missions"] as $k=>$m){
                    $quest["missions"][$k]["date_modified"] = isset($m["date_modified"])?$m["date_modified"]:"";
                    $quest["missions"][$k]["status"] = isset($m["status"])?$m["status"]:"";
                    $quest["missions"][$k]["pending"] = $this->checkCompletionMission($quest, $m, $pb_player_id, $this->validToken, $badge_player_check, $m);
                }

                $quest['status'] = $q['status'];
                $quest['quest_id'] = $q['quest_id'];
                unset($quest['_id']);

                $quests[] = $quest;
            }

            array_walk_recursive($quests, array($this, "convert_mongo_object"));

            $resp['quests'] = $quests;
        }
        $this->response($this->resp->setRespond($resp), 200);
    }

    public function questAll_get($player_id = 0)
    {
        $pb_player_id = null;
        $badge_player_check = null;
        if ($player_id) {
            $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
                'cl_player_id' => $player_id
            )));
            if(!$pb_player_id)
                $this->response($this->error->setError('USER_NOT_EXIST'), 200);

            $badge_player_check = array();
            $player_badges = $this->player_model->getBadge($pb_player_id, $this->site_id);
            if($player_badges){
                foreach($player_badges as $b){
                    $badge_player_check[$b["badge_id"]] = $b["amount"];
                }
            }
        }

        $quests = $this->quest_model->getQuests($this->validToken);
        array_walk_recursive($quests, array($this, "convert_mongo_object"));
        foreach ($quests as &$quest) {
            if ($pb_player_id) {
                $quest_player = $this->quest_model->getPlayerQuest(array('site_id' => $this->site_id, 'pb_player_id' => $pb_player_id, 'quest_id' => new MongoId($quest['_id'])));
                if ($quest_player) {
                    $quest['player_status'] = $quest_player['status'];
                    foreach($quest_player["missions"] as $k=>$m){
                        $quest["missions"][$k]["date_modified"] = isset($m["date_modified"])?$m["date_modified"]:"";
                        $quest["missions"][$k]["status"] = isset($m["status"])?$m["status"]:"";
                        $quest["missions"][$k]["pending"] = $this->checkCompletionMission($quest, $m, $pb_player_id, $this->validToken, $badge_player_check, $m);
                    }
                }
            }
            $quest['quest_id'] = $quest['_id'];
            unset($quest['_id']);
        }

        $resp['quests'] = $quests;
        $this->response($this->resp->setRespond($resp), 200);
    }

    /*
     * reset quest
     *
     * @param player_id string player id of client
     * @param quest_id string (optional) id of quest
     * return array
     */
    public function reset_post(){
        $this->benchmark->mark('start');

        $player_id = $this->input->post('player_id') ? $this->input->post('player_id') : $this->response($this->error->setError('PARAMETER_MISSING', array('player_id')), 200);

        $pb_player_id = $this->player_model->getPlaybasisId(array_merge($this->validToken, array(
            'cl_player_id' => $player_id
        )));

        if (!$pb_player_id) $this->response($this->error->setError('USER_NOT_EXIST'), 200);

        $quest_id = $this->input->post('quest_id') ? new MongoId($this->input->post('quest_id')) : null;
        $results = $this->quest_model->delete($this->client_id, $this->site_id, $pb_player_id, $quest_id);

        $this->benchmark->mark('end');
        $t = $this->benchmark->elapsed_time('start', 'end');
        $this->response($this->resp->setRespond(array('result' => $results, 'processing_time' => $t)), 200);
    }

    protected function processFeedback($type, $input) {
        switch (strtolower($type)) {
        case 'email':
            $this->processEmail($input);
            break;
        case 'sms':
            $this->processSms($input);
            break;
        default:
            log_message('error', 'Unknown feedback type: '.$type);
            break;
        }
    }

    protected function processEmail($input) {
        /* check permission according to billing cycle */
        $access = true;
        try {
            $this->client_model->permissionProcess(
                $input['client_id'],
                $input['site_id'],
                "notifications",
                "email"
            );
        } catch(Exception $e) {
            if ($e->getMessage() == "LIMIT_EXCEED")
                $access = false;
        }
        if (!$access) return false;

        /* get email */
        $player = $this->player_model->getById($input['site_id'], $input['pb_player_id']);
        $email = $player && isset($player['email']) ? $player['email'] : null;
        if (!$email) return false;

        /* check blacklist */
        $res = $this->email_model->isEmailInBlackList($email, $input['site_id']);
        if ($res) return false; // banned

        /* check valid template_id */
        $template = $this->email_model->getTemplateById($input['site_id'], $input['input']['template_id']);
        if (!$template) return false;

        /* send email */
        $from = EMAIL_FROM;
        $to = $email;
        $subject = $input['input']['subject'];
        $message = $this->utility->replace_template_vars($template['body'], $player);
        $response = $this->utility->email($from, $to, $subject, $message);
        $this->email_model->log(EMAIL_TYPE_USER, $input['client_id'], $input['site_id'], $response, $from, $to, $subject, $message);
        return $response != false;
    }

    protected function processSms($input) {
        /* check permission according to billing cycle */
        $access = true;
        try {
            $this->client_model->permissionProcess(
                $input['client_id'],
                $input['site_id'],
                "notifications",
                "sms"
            );
        } catch(Exception $e) {
            if ($e->getMessage() == "LIMIT_EXCEED")
                $access = false;
        }
        if (!$access) return false;

        /* get phone number */
        $player = $this->player_model->getById($input['site_id'], $input['pb_player_id']);
        $phone = $player && isset($player['phone_number']) ? $player['phone_number'] : null;
        if (!$phone) return false;

        /* check valid template_id */
        $template = $this->sms_model->getTemplateById($input['site_id'], $input['input']['template_id']);
        if (!$template) return false;

        /* send SMS */
        $this->config->load("twilio",TRUE);
        $config = $this->sms_model->getSMSClient($input['client_id'], $input['site_id']);
        $twilio = $this->config->item('twilio');
        $config['api_version'] = $twilio['api_version'];
        $this->load->library('twilio/twiliomini', $config);
        $from = $config['number'];
        $to = $phone;
        $message = $this->utility->replace_template_vars($template['body'], $player);
        $response = $this->twiliomini->sms($from, $to, $message);
        $this->sms_model->log($input['client_id'], $input['site_id'], 'user', $from, $to, $message, $response);
        return $response->IsError;
    }

    /**
     * Use with array_walk and array_walk_recursive.
     * Recursive iterable items to modify array's value
     * from MongoId to string and MongoDate to readable date
     * @param mixed $item this is reference
     * @param string $key
     */
    private function convert_mongo_object(&$item, $key) {
        if (is_object($item)) {
            if (get_class($item) === 'MongoId') {
                $item = $item->{'$id'};
            } else if (get_class($item) === 'MongoDate') {
                $item =  datetimeMongotoReadable($item);
            }
        }
    }
}
?>
