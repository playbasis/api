<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Goods_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('playbasis');
        $this->load->library('memcached_library');
        $this->load->helper('memcache');
    }
    public function getAllGoods($data)
    {
        //get goods ids
        $this->set_site_mongodb($data['site_id']);
        $this->mongo_db->select(array('goods_id','image','name','description','quantity','redeem','date_start','date_expire','sponsor','sort_order'));
        $this->mongo_db->select(array(),array('_id'));
        $this->mongo_db->where(array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'deleted' => false
        ));
        $goods = $this->mongo_db->get('playbasis_goods_to_client');
        if($goods){
            foreach($goods as &$g){

                if(isset($g['redeem']))
                {
                    if(isset($g['redeem']['badge'])){
                        $redeem = array();
                        foreach($g['redeem']['badge'] as $k => $v){
                            $redeem_inside = array();
                            $redeem_inside["badge_id"] = $k;
                            $redeem_inside["badge_value"] = $v;
                            $redeem[] = $redeem_inside;
                        }
                        $g['redeem']['badge'] = $redeem;
                    }
                    if(isset($g['redeem']['custom'])){
                        $redeem = array();
                        foreach($g['redeem']['custom'] as $k => $v){
                            $this->mongo_db->select(array('name'));
                            $this->mongo_db->select(array(),array('_id'));
                            $this->mongo_db->where(array(
                                'client_id' => $data['client_id'],
                                'site_id' => $data['site_id'],
                                'reward_id' => new MongoId($k),
                            ));
                            $custom = $this->mongo_db->get('playbasis_reward_to_client');
                            if(isset($custom[0]['name'])){
                                $redeem_inside = array();
                                $redeem_inside["custom_id"] = $k;
                                $redeem_inside["custom_name"] = $custom[0]['name'];
                                $redeem_inside["custom_value"] = $v;
                                $redeem[] = $redeem_inside;
                            }
                        }
                        $g['redeem']['custom'] = $redeem;
                    }
                }

                $g['image'] = $this->config->item('IMG_PATH') . $g['image'];
                $g['goods_id'] = $g['goods_id']."";
                $g['date_start'] = $g['date_start'] ? datetimeMongotoReadable($g['date_start']) : null;
                $g['date_expire'] = $g['date_expire'] ? datetimeMongotoReadable($g['date_expire']) : null;
            }
        }
        return $goods;
    }
    public function getGoods($data)
    {
        //get goods id
        $this->set_site_mongodb($data['site_id']);
        // $this->mongo_db->select(array('goods_id','image','name','description','quantity','redeem','date_start','date_expire','sponsor'));
        $this->mongo_db->select(array('goods_id','image','name','description','quantity','per_user','redeem','date_start','date_expire','sponsor','sort_order'));
        $this->mongo_db->select(array(),array('_id'));
        $this->mongo_db->where(array(
            'client_id' => $data['client_id'],
            'site_id' => $data['site_id'],
            'goods_id' => $data['goods_id'],
            'deleted' => false
        ));
        $result = $this->mongo_db->get('playbasis_goods_to_client');

        if(isset($result[0]['redeem']))
        {

            if(isset($result[0]['redeem']['badge'])){
                $redeem = array();
                foreach($result[0]['redeem']['badge'] as $k => $v){
                    $redeem_inside = array();
                    $redeem_inside["badge_id"] = $k;
                    $redeem_inside["badge_value"] = $v;
                    $redeem[] = $redeem_inside;
                }
                $result[0]['redeem']['badge'] = $redeem;
            }
            if(isset($result[0]['redeem']['custom'])){
                $redeem = array();
                foreach($result[0]['redeem']['custom'] as $k => $v){
                    $this->mongo_db->select(array('name'));
                    $this->mongo_db->select(array(),array('_id'));
                    $this->mongo_db->where(array(
                        'client_id' => $data['client_id'],
                        'site_id' => $data['site_id'],
                        'reward_id' => new MongoId($k),
                    ));
                    $custom = $this->mongo_db->get('playbasis_reward_to_client');
                    if(isset($custom[0]['name'])){
                        $redeem_inside = array();
                        $redeem_inside["custom_id"] = $k;
                        $redeem_inside["custom_name"] = $custom[0]['name'];
                        $redeem_inside["custom_value"] = $v;
                        $redeem[] = $redeem_inside;
                    }
                }
                $result[0]['redeem']['custom'] = $redeem;
            }
        }
        if(isset($result[0]['goods_id']))
        {
            $result[0]['goods_id'] = $result[0]['goods_id']."";
        }
        if(isset($result[0]['date_start']))
        {
            $result[0]['date_start'] = datetimeMongotoReadable($result[0]['date_start']);
        }
        if(isset($result[0]['date_expire']))
        {
            $result[0]['date_expire'] = datetimeMongotoReadable($result[0]['date_expire']);
        }
        if(isset($result[0]['image']))
        {
            $result[0]['image'] = $this->config->item('IMG_PATH') . $result[0]['image'];
        }
        return $result ? $result[0] : array();
    }
	public function listActiveItems($data, $from, $to)
	{
		$this->set_site_mongodb($data['site_id']);
		$this->mongo_db->where(array(
			'client_id' => $data['client_id'],
			'site_id' => $data['site_id'],
			'$and' => array(
				array('$or' => array(array('date_start' => array('$lte' => $this->new_mongo_date($to))), array('date_start' => null))),
				array('$or' => array(array('date_expire' => array('$gte' => $this->new_mongo_date($to, '23:59:59'))), array('date_expire' => null)))
//array('$or' => array(array('date_expire' => array('$gte' => $this->new_mongo_date('2014-03-09', '23:59:59'))), array('date_expire' => null)))
			),
			'deleted' => false,
			'status' => true
		));
		return $this->mongo_db->get('playbasis_goods_to_client');
	}
	public function listExpiredItems($data, $from, $to)
	{
		$this->set_site_mongodb($data['site_id']);
		$this->mongo_db->where(array(
			'client_id' => $data['client_id'],
			'site_id' => $data['site_id'],
			'date_expire' => array('$gte' => $this->new_mongo_date($from), '$lte' => $this->new_mongo_date($to, '23:59:59')),
			'deleted' => false,
			'status' => true
		));
		return $this->mongo_db->get('playbasis_goods_to_client');
	}
	public function totalRedemption($data, $goods_id)
	{
		$this->set_site_mongodb($data['site_id']);
		$this->mongo_db->select(array('pb_player_id','value'));
		$this->mongo_db->where(array(
			'client_id' => $data['client_id'],
			'site_id' => $data['site_id'],
			'goods_id' => $goods_id
		));
		return $this->mongo_db->get('playbasis_goods_to_player');
	}
	public function redeemLogDistinctPlayer($data, $goods_id, $from=null, $to=null)
	{
		$this->set_site_mongodb($data['site_id']);
		$query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id'], 'goods_id' => $goods_id->{'$id'});
		if ($from || $to) $query['date_added'] = array();
		if ($from) $query['date_added']['$gte'] = $this->new_mongo_date($from);
		if ($to) $query['date_added']['$lte'] = $this->new_mongo_date($to, '23:59:59');
		$result = $this->mongo_db->command(array(
			'distinct' => 'playbasis_goods_log',
			'key' => 'pb_player_id',
			'query' => $query
		));
		return $result['values'];
	}
	public function redeemLogCount($data, $goods_id, $from=null, $to=null)
	{
		$this->set_site_mongodb($data['site_id']);
		$query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id'], 'goods_id' => $goods_id->{'$id'});
		if ($from || $to) $query['date_added'] = array();
		if ($from) $query['date_added']['$gte'] = $this->new_mongo_date($from);
		if ($to) $query['date_added']['$lte'] = $this->new_mongo_date($to, '23:59:59');
		$result = $this->mongo_db->command(array(
			'count' => 'playbasis_goods_log',
			'query' => $query
		));
		return $result['n'];
	}
	public function redeemLog($data, $goods_id, $from=null, $to=null)
	{
		$this->set_site_mongodb($data['site_id']);
		$this->mongo_db->select(array('pb_player_id','amount'));
		$query = array('client_id' => $data['client_id'], 'site_id' => $data['site_id'], 'goods_id' => $goods_id->{'$id'});
		if ($from || $to) $query['date_added'] = array();
		if ($from) $query['date_added']['$gte'] = $this->new_mongo_date($from);
		if ($to) $query['date_added']['$lte'] = $this->new_mongo_date($to, '23:59:59');
		$this->mongo_db->where($query);
		return $this->mongo_db->get('playbasis_goods_log');
	}
	public function getGoodsByGroupAndPlayerId($client_id, $site_id, $group, $pb_player_id, $amount) {
		$this->set_site_mongodb($site_id);
		$this->mongo_db->where(array(
			'client_id' => $client_id,
			'site_id' => $site_id,
			'group' => $group,
			'deleted' => false,
			'status' => true
		));
		$goodsList = $this->mongo_db->get('playbasis_goods_to_client');
		return is_array($goodsList) && count($goodsList) > 0 && $this->checkGoods($client_id, $site_id, $goodsList[0], $pb_player_id, $amount) ? $goodsList : array();
	}
	public function countGoodsByGroup($client_id, $site_id, $group, $pb_player_id, $amount) {
		return count($this->getGoodsByGroupAndPlayerId($client_id, $site_id, $group, $pb_player_id, $amount));
	}
	private function checkGoods($client_id, $site_id, $goods, $pb_player_id, $amount)
	{
		if (isset($goods['date_start'])) $goods['date_start'] = datetimeMongotoReadable($goods['date_start']);
		if (isset($goods['date_expire'])) $goods['date_expire'] = datetimeMongotoReadable($goods['date_expire']);
		$valid = $this->checkGoodsTime($goods); if (!$valid) return false;
		$valid = $this->checkGoodsAmount($goods, $amount); if (!$valid) return false;
		$playerRecord = $this->getGoodsToPlayerRecord($goods['goods_id'], $pb_player_id);
		$valid = $this->checkGoodsPlayerPerUser($goods, $playerRecord); if (!$valid) return false;
		$valid = $this->checkGoodsPlayerPoint($goods, $pb_player_id, $amount, $client_id, $site_id); if (!$valid) return false;
		$valid = $this->checkGoodsPlayerBadge($goods, $pb_player_id, $amount); if (!$valid) return false;
		$valid = $this->checkGoodsPlayerCustom($goods, $pb_player_id, $amount); if (!$valid) return false;
		return true;
	}
	private function checkGoodsTime($goods)
	{
		if(isset($goods['date_start']) && $goods['date_start']){
			$datetimecheck = new DateTime('now');
			$datetimestart = new DateTime($goods['date_start']);
			if($datetimecheck < $datetimestart)
				return false;
		}
		if(isset($goods['date_expire']) && $goods['date_expire'] ){
			$datetimecheck = new DateTime('now');
			$datetimeexpire = new DateTime($goods['date_expire']);
			if($datetimecheck > $datetimeexpire)
				return false;
		}
		return true;
	}
	private function checkGoodsAmount($goods, $amount)
	{
		if (!isset($goods['quantity']) || is_null($goods['quantity'])) return true;
		return (int)$goods['quantity'] >= (int)$amount;
	}
	private function checkGoodsPlayerPerUser($goods, $playerRecord)
	{
		if ($playerRecord && $goods['per_user'] != null) {
			return $playerRecord['value'] < $goods['per_user'];
		}
		return true;
	}
	private function checkGoodsPlayerPoint($goods, $pb_player_id, $amount, $client_id, $site_id)
	{
		if(isset($goods['redeem']['point']["point_value"]) && ($goods['redeem']['point']["point_value"] > 0)){
			$reward_id = $this->getRewardIdByName($client_id, $site_id, 'point');
			$playerRecord = $this->getRewardToPlayerRecord($reward_id, $pb_player_id);
			$player_point = ($playerRecord && array_key_exists('value', $playerRecord) ? $playerRecord['value'] : 0);
			if((int)($player_point*$amount) < (int)($goods['redeem']['point']["point_value"]*$amount)){
				return false;
			}
		}
		return true;
	}
	private function checkGoodsPlayerBadge($goods, $pb_player_id, $amount)
	{
		if(isset($goods['redeem']['badge'])){
			$redeem_current = 0;
			$redeem_at_least = count($goods['redeem']['badge']);

			$list_badge_id = array();
			foreach ($goods['redeem']['badge'] as $k => $v) $list_badge_id[] = new MongoId($k);
			$playerRecords = $this->getBadgesToPlayerRecords($list_badge_id, $pb_player_id);
			if ($playerRecords) foreach ($playerRecords as $playerRecord) {
				$badge_id = $playerRecord['badge_id'];
				$value = (int)$playerRecord['value'];
				if ($value*$amount >= $goods['redeem']['badge'][$badge_id->{'$id'}]*$amount) $redeem_current++;
			}

			if($redeem_current < $redeem_at_least){
				return false;
			}
		}
		return true;
	}
	private function checkGoodsPlayerCustom($goods, $pb_player_id, $amount)
	{
		if(isset($goods['redeem']['custom'])){
			$redeem_current = 0;
			$redeem_at_least = count($goods['redeem']['custom']);

			$list_custom_id = array();
			foreach ($goods['redeem']['custom'] as $k => $v) $list_custom_id[] = new MongoId($k);
			$playerRecords = $this->getRewardsToPlayerRecords($list_custom_id, $pb_player_id);
			if ($playerRecords) foreach ($playerRecords as $playerRecord) {
				$reward_id = $playerRecord['reward_id'];
				$value = (int)$playerRecord['value'];
				if ($value*$amount >= $goods['redeem']['custom'][$reward_id->{'$id'}]*$amount) $redeem_current++;
			}

			if($redeem_current < $redeem_at_least){
				return false;
			}
		}
		return true;
	}
	private function getRewardIdByName($client_id, $site_id, $name) {
		$this->mongo_db->select(array('reward_id'));
		$this->mongo_db->where(array(
			'client_id' => $client_id,
			'site_id' => $site_id,
			'name' => strtolower($name)
		));
		$result = $this->mongo_db->get('playbasis_reward_to_client');
		return $result ? $result[0]['reward_id'] : array();
	}
	private function getGoodsToPlayerRecord($goods_id, $pb_player_id) {
		$this->mongo_db->where(array(
			'pb_player_id' => $pb_player_id,
			'goods_id' => $goods_id,
		));
		$playerRecord = $this->mongo_db->get('playbasis_goods_to_player');
		return $playerRecord ? $playerRecord[0] : null;
	}
	private function getRewardToPlayerRecord($reward_id, $pb_player_id) {
		$this->mongo_db->where(array(
			'pb_player_id' => $pb_player_id,
			'reward_id' => $reward_id
		));
		$playerRecord = $this->mongo_db->get('playbasis_reward_to_player');
		return $playerRecord ? $playerRecord[0] : null;
	}
	private function getRewardsToPlayerRecords($list_reward_id, $pb_player_id) {
		$this->mongo_db->where(array('pb_player_id' => $pb_player_id));
		$this->mongo_db->where_in('reward_id', $list_reward_id);
		return $this->mongo_db->get('playbasis_reward_to_player');
	}
	private function getBadgesToPlayerRecords($list_badge_id, $pb_player_id) {
		$this->mongo_db->where(array('pb_player_id' => $pb_player_id));
		$this->mongo_db->where_in('badge_id', $list_badge_id);
		return $this->mongo_db->get('playbasis_reward_to_player');
	}
}
?>