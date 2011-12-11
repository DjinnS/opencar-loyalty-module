<?php
/*
* Author  : DjinnS - djinns@chninkel.net
* 
* License : GPL v2 (http://www.gnu.org/licenses/gpl-2.0.html)
*/

class ModelSaleLoyalty extends Model {

	/*
	*	Name      	: updateSetting()
	*	Parameters	: $data: post array() ($_POST[])
	*	Return		: none
	*/
	public function updateSetting($data) {
	
		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_rate'] ."', serialized=0	 	WHERE `group` like 'loyalty_config'  and `key` like 'loyalty_rate' ");

		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_threshold'] ."', serialized=0 	WHERE `group` like 'loyalty_config' and `key` like 'loyalty_threshold' ");
                
		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_gain'] ."', serialized=0		WHERE `group` like 'loyalty_config'  and `key` like 'loyalty_gain' ");
		
		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_voucherid'] ."', serialized=0		WHERE `group` like 'loyalty_config'  and `key` like 'loyalty_voucherid' ");
		
		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_order_statusid'] ."', serialized=0		WHERE `group` like 'loyalty_config'  and `key` like 'loyalty_order_status' ");
	}
	
	/*
	*	Name      	: getSetting()
	*	Parameters	: param key
	*	Return		: array with parameters
	*/	
	public function getSetting($key) {
	
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE `group` like 'loyalty_config' and `key`= '". $key ."' ");
	
		return $query->row['value'];
	}
	
	/*
	*	Name      	: setLoyalty()
	*	Parameters	: $data with order informations
	*	Return		: none, add loyalty point to the customer
	*/	
	public function setLoyalty($data) {
	
		// get history to avoid duplicate loyalty
		$results = $this->db->query("SELECT `loyalty_id` FROM ". DB_PREFIX ."loyalty WHERE `order_id`=".$data['order_id']);
		
		if($results->num_rows == 0) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "loyalty (`order_id`,`customer_id`,`loyalty_points`) VALUES (".$data['order_id'].",".$data['customer_id'].",".$data['loyalty'].")");
			
			// calculate amount of loyalty and send voucher if necessary
			$this->applyLoyalty($data['customer_id']);
		}
	}
	
	/*
	*	Name      	: applyLoyalty()
	*	Parameters	: $customer_id
	*	Return		: none, send voucher if necessary
	*/	
	public function applyLoyalty($customer_id) {
	
		// get all loyalty points
		$results = $this->db->query("SELECT sum(loyalty_points) as amount FROM ". DB_PREFIX ."loyalty WHERE `customer_id`=".$customer_id);
		
		// get thresold
		$threshold = $this->getSetting("loyalty_threshold");
		
		// if threhsold
		if($results->row['amount'] >= $threshold) {
			
			// soustrace the threshold of the total loyalty point, by default, delete all order except the last
			$rest = $results->row['amount'] - $threshold;
			
			if($rest > 0) {
				// delete all loyalties except the last
				$last_loyalty = $this->db->query("SELECT loyalty_id FROM ". DB_PREFIX ."loyalty WHERE `customer_id`=".$customer_id." order by loyalty_id DESC LIMIT 1");
				
				$this->db->query("DELETE FROM ". DB_PREFIX ."loyalty WHERE `customer_id`=".$customer_id." and loyalty_id NOT IN (".$last_loyalty->row['loyalty_id'].")");
				
				$this->db->query("UPDATE ". DB_PREFIX ."loyalty set loyalty_points = ".$rest." WHERE `customer_id`=".$customer_id." and loyalty_id=".$last_loyalty->row['loyalty_id']);
			} else {
				// there is no more loyalty point, delete all order from the loyalty history
				$this->db->query("DELETE FROM ". DB_PREFIX ."loyalty WHERE `customer_id`=".$customer_id);
			}
			
			// finaly, add the voucher
			
			// randow voucher code
			$len = 16;
			$base='ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
			$max=strlen($base)-1;
			$voucher_code='';
			mt_srand((double)microtime()*1000000);
			while (strlen($voucher_code)<$len+1) $voucher_code.=$base{mt_rand(0,$max)};

			//addVoucher();
		}
	}
}
?>
