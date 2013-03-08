<?php defined('BASEPATH') OR exit('No direct script access allowed');
define("CUSTOM_POINT_START_ID",10000);
	
class Client_model extends CI_Model{
	
	public function __construct(){
		parent::__construct();

		$this->config->load('playbasis');
	}
	
	//get action configuration from all rule that relate to site id & client id
	public function getRuleSet($clientData){
		assert($clientData);
		assert(is_array($clientData));
		assert(isset($clientData['client_id']));
		assert(isset($clientData['site_id']));
		
		$this->db->select('jigsaw_set');
		$this->db->where($clientData);
		
		$result = $this->db->get('playbasis_rule');
		
		return $result->result_array();
	}
	
	//get action id use action name
	public function getActionId($clientData){
		assert($clientData);
		assert(is_array($clientData));
		assert(isset($clientData['client_id']));
		assert(isset($clientData['site_id']));
		assert(isset($clientData['action_name']));
		
		$this->db->select('action_id');
		$this->db->where(array('client_id'=>$clientData['client_id'],'site_id'=>$clientData['site_id'],'name'=>$clientData['action_name']));
		
		$result = $this->db->get('playbasis_action_to_client');
		$id = $result->row_array();

		return ($id) ? $id['action_id'] : 0;
	}
	
	//get action configuration from all rule that relate to site id & client id
	public function getRuleSetByActionId($clientData){
		assert($clientData);
		assert(is_array($clientData));
		assert(isset($clientData['client_id']));
		assert(isset($clientData['site_id']));
		assert(isset($clientData['action_id']));
		
		$clientData['active_status'] = '1'; 
		$this->db->select('rule_id,name,jigsaw_set');
		$this->db->where($clientData);
		
		$result = $this->db->get('playbasis_rule');
		
		return $result->result_array();
	}
	
	//get class path relate to jigsaw
	public function getJigsawProcessor($jigsawId){
		assert($jigsawId);
		
		$this->db->where(array('jigsaw_id'=>$jigsawId));
		$this->db->select('class_path');
		
		$result = $this->db->get('playbasis_game_jigsaw_to_client');
		
		$jigsawProcessor = $result->row_array();
		
		return $jigsawProcessor['class_path'];
	}
	
	//update point reward
	public function updatePlayerPointReward($rewardId,$quantity,$pbPlayerId,$clientId,$siteId, $overrideOldValue=FALSE){
		assert(isset($rewardId));
		assert(isset($siteId));
		assert(isset($quantity));
		assert(isset($pbPlayerId));

		$this->db->where(array('pb_player_id'=>$pbPlayerId,'reward_id'=>$rewardId));
		$this->db->from('playbasis_reward_to_player');
		$hasReward = $this->db->count_all_results();
		
		if($hasReward){
			$this->db->where(array('pb_player_id'=>$pbPlayerId,'reward_id'=>$rewardId));
			$this->db->set('date_modified',date('Y-m-d H:i:s'));
			if($overrideOldValue)
				$this->db->set('value', $quantity);
			else
				$this->db->set('value',"`value`+$quantity",FALSE);
			$this->db->update('playbasis_reward_to_player');
		}
		else{
			$this->db->insert('playbasis_reward_to_player',array('pb_player_id'=>$pbPlayerId,'client_id'=>$clientId,'site_id'=>$siteId,'reward_id'=>$rewardId,'value'=>$quantity,'date_added'=>date('Y-m-d H:i:s'),'date_modified'=>date('Y-m-d H:i:s')));
		}
		
		//upadte client reward limit
		$this->db->select('limit');
		$this->db->where(array('reward_id'=>$rewardId,'site_id'=>$siteId));
		$result = $this->db->get('playbasis_reward_to_client');
		
		assert($result->row_array());

		$result = $result->row_array();
		
		if(!is_null($result['limit'])){
			$this->db->where(array('reward_id'=>$rewardId,'site_id'=>$siteId));
			$this->db->set('limit',"`limit`-$quantity",FALSE);
			$this->db->update('playbasis_reward_to_client');
		}
	}
	
	
	public function updateCustomReward($rewardName,$quantity,$input,&$jigsawConfig){
		//check reward available
		$this->db->select('reward_id');
		$this->db->where(array('client_id'=>$input['client_id'],'site_id'=>$input['site_id'],'name'=>strtolower($rewardName)));
		$this->db->from('playbasis_reward_to_client');
		$result  = $this->db->get();
		$result = $result->row_array();
		$customRewardId = isset($result['reward_id'])?$result['reward_id'] : false; 
		
		if(!$customRewardId){
			//check  client custom points
			$this->db->select_max('reward_id');
			$this->db->where(array('client_id'=>$input['client_id'],'site_id'=>$input['site_id']));
			$result = $this->db->get('playbasis_reward_to_client');
			$result = $result->row_array();
			$customRewardId = $result['reward_id']+1;
			
			if($customRewardId < CUSTOM_POINT_START_ID)
				$customRewardId = CUSTOM_POINT_START_ID;
			
			//update client reward
			$this->db->insert('playbasis_reward_to_client',array('reward_id'=>$customRewardId,'client_id'=>$input['client_id'],'site_id'=>$input['site_id'],'group'=>'POINT','name'=>strtolower($rewardName),'date_added'=>date('Y-m-d H:i:s'),'date_modified'=>date('Y-m-d H:i:s')));
		}	
			
		//update player reward
		$this->updatePlayerPointReward($customRewardId,$quantity,$input['pb_player_id'],$input['client_id'],$input['site_id']);
	
		$jigsawConfig['reward_id'] = $customRewardId;
		$jigsawConfig['reward_name'] = $rewardName;
		$jigsawConfig['quantity'] = $quantity;
	}
	
	
	public function updateplayerBadge($badgeId,$quantity,$pbPlayerId){
		assert(isset($badgeId));
		assert(isset($quantity));
		assert(isset($pbPlayerId));
		
		
		//update badge master table
		$this->db->select('substract,quantity');
		$this->db->where(array('badge_id'=>$badgeId));
		$result = $this->db->get('playbasis_badge');
		
		$badgeInfo = $result->row_array();
		
		if($badgeInfo['substract']){
			$remainingQuantity = $badgeInfo['quantity'] - $quantity;
			if($remainingQuantity < 0){
				$remainingQuantity = 0;
				$quantity = $badgeInfo['quantity'];
			}
			$this->db->set('quantity',$remainingQuantity);
			$this->db->set('date_modified',date('Y-m-d H:i:s'));
			$this->db->where('badge_id',$badgeId);			
			$this->db->update('playbasis_badge');
		}
		
		$this->db->where(array('pb_player_id'=>$pbPlayerId,'badge_id'=>$badgeId));
		$this->db->from('playbasis_badge_to_player');
		$hasBadge = $this->db->count_all_results();
		
		if($hasBadge){
			$this->db->where(array('pb_player_id'=>$pbPlayerId,'badge_id'=>$badgeId));
			$this->db->set('date_modified',date('Y-m-d H:i:s'));			
			$this->db->set('amount',"`amount`+$quantity",FALSE);
			$this->db->update('playbasis_badge_to_player');
		}
		else{
			$this->db->insert('playbasis_badge_to_player',array('pb_player_id'=>$pbPlayerId,'badge_id'=>$badgeId,'amount'=>$quantity,'date_added'=>date('Y-m-d H:i:s'),'date_modified'=>date('Y-m-d H:i:s')));
		}
		
		
	}

	public function updateExpAndLevel($exp,$pb_player_id,$clientData){
		assert($exp);
		assert($pb_player_id);
		
		assert($clientData);
		assert(is_array($clientData));
		assert(isset($clientData['client_id']));
		assert(isset($clientData['site_id']));

		//get player exp
		$this->db->select('exp,level');
		$this->db->where('pb_player_id',$pb_player_id);
		$result = $this->db->get('playbasis_player');
		
		$result = $result->row_array();		
		$playerExp = $result['exp'];
		$playerLevel = $result['level'];
		$newExp = $exp+$playerExp;
		
		//check if client have their own exp table setup
		$this->db->select_max('level');
		$this->db->where($clientData);
		$this->db->where("exp <=",$newExp);
		$result = $this->db->get('playbasis_client_exp_table');
		$level = $result->row_array();
		if(!$level['level']){
			//get level from default exp table instead
			$this->db->select_max('level');
			$this->db->where("exp <=",$newExp);
			$result = $this->db->get('playbasis_exp_table');

			$level = $result->row_array();
		}
		if($level['level'] && $level['level'] > $playerLevel)
			$level = $level['level'];
		else
			$level = -1;
		
		$this->db->where('pb_player_id',$pb_player_id);
		$this->db->set('date_modified',date('Y-m-d H:i:s'));		
		$this->db->set('exp', "`exp`+$exp", FALSE);
		if($level>0)
			$this->db->set('level', $level);
		
		$this->db->update('playbasis_player');

		//get reward id to update the reward to player table
		$this->db->select('reward_id');
		$this->db->where('name', 'exp');
		$result = $this->db->get('playbasis_reward');
		$result = $result->row_array();
		
		$this->updatePlayerPointReward($result['reward_id'], $newExp, $pb_player_id, $clientData['client_id'], $clientData['site_id'], TRUE);

		return $level;
	}

	public function log($logData,$jigsawOptionData=array()){
		assert($logData);
		assert(is_array($logData));
		assert($logData['pb_player_id']);
		assert($logData['action_id']);
		assert('$logData["action_name"]');
		assert($logData['client_id']);
		assert($logData['site_id']);
		assert('$logData["domain_name"]');
		
		//get player info 
		//$this->db->select('first_name,last_name');
		//$this->db->where('pb_player_id',$logData['pb_player_id']);
		//$result = $this->db->get('playbasis_player');
		
		//$result = $result->row_array();
		
		if(isset($logData['input']))
			$logData['input'] = serialize(array_merge($logData['input'],$jigsawOptionData));
		else
			$logData['input'] = 'NO-INPUT';
		
		$this->db->set('pb_player_id',$logData['pb_player_id']);
		//$this->db->set('first_name',$result['first_name']);
		//$this->db->set('last_name',$result['last_name']);
		if(isset($logData['action_id']))
			$this->db->set('action_id',$logData['action_id']);
		if(isset($logData['action_name']))
			$this->db->set('action_name',$logData['action_name']);
		if(isset($logData['rule_id']))
			$this->db->set('rule_id',$logData['rule_id']);
		if(isset($logData['rule_name']))
			$this->db->set('rule_name',$logData['rule_name']);
		if(isset($logData['jigsaw_id']))
			$this->db->set('jigsaw_id',$logData['jigsaw_id']);
		if(isset($logData['jigsaw_name']))
			$this->db->set('jigsaw_name',$logData['jigsaw_name']);
		if(isset($logData['jigsaw_category']))
			$this->db->set('jigsaw_category',$logData['jigsaw_category']);
		$this->db->set('input',$logData['input']);
		$this->db->set('client_id',$logData['client_id']);
		$this->db->set('site_id',$logData['site_id']);
		$this->db->set('domain_name',$logData['domain_name']);
		if(isset($logData['site_name']))
			$this->db->set('site_name',$logData['site_name']);
		if(isset($logData['ip_address']))
			$this->db->set('ip_address',$logData['ip_address']);
		if(isset($logData['user_agent']))
			$this->db->set('user_agent',$logData['user_agent']);
		$this->db->set('date_added',date('Y-m-d H:i:s'));
		$this->db->set('date_modified',date('Y-m-d H:i:s'));
		
		$this->db->insert('playbasis_jigsaw_log');
		
	}
	
	public function getBadgeById($badgeId){
		$this->db->select('badge_id,image');
		$this->db->where('badge_id',$badgeId);
		$result = $this->db->get('playbasis_badge');
		$badgeImage = $result->row_array();
		$badgeImage['image'] = $this->config->item('IMG_PATH').$badgeImage['image'];
		
		$this->db->select('name,description');
		$this->db->where('badge_id',$badgeId);
		$result = $this->db->get('playbasis_badge_description');
		$badgeDesc = $result->row_array();
		
		return array_merge($badgeImage,$badgeDesc);
	} 
}
?>