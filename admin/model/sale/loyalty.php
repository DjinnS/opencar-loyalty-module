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
		
		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_voucher_from'] ."', serialized=0		WHERE `group` like 'loyalty_config'  and `key` like 'loyalty_from_name' ");
		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_voucher_email'] ."', serialized=0		WHERE `group` like 'loyalty_config'  and `key` like 'loyalty_from_email' ");
		$this->db->query("UPDATE ". DB_PREFIX ."setting SET value='". $data['loyalty_voucher_msg'] ."', serialized=0		WHERE `group` like 'loyalty_config'  and `key` like 'loyalty_voucher_message' ");
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
						
			// get the number of vouchers from the total amount of loyalty in fonction of threshold
			$nbofvoucher = floor($results->row['amount']/$threshold);
			
			// get the loyalty point "restant"
			$rest = $results->row['amount'] - ($nbofvoucher*$threshold);
						
			if($rest > 0) {
				// set to 0 all loyalties except the last
				$last_loyalty = $this->db->query("SELECT loyalty_id FROM ". DB_PREFIX ."loyalty WHERE `customer_id`=".$customer_id." order by loyalty_id DESC LIMIT 1");
								
				$this->db->query("UPDATE ". DB_PREFIX ."loyalty set loyalty_points = 0 WHERE `customer_id`=".$customer_id." and loyalty_id not in (".$last_loyalty->row['loyalty_id'].")");
				
				$this->db->query("UPDATE ". DB_PREFIX ."loyalty set loyalty_points = ".$rest." WHERE `customer_id`=".$customer_id." and loyalty_id=".$last_loyalty->row['loyalty_id']);
			} else {
				// there is no more loyalty points, set 0 all orders
				$this->db->query("UPDATE ". DB_PREFIX ."loyalty set loyalty_points = 0 WHERE `customer_id`=".$customer_id);
			}
			
			// randow voucher code
			$len = 13;
			$base='ABCDEFGHKLMNOPQRSTWXYZabcdefghjkmnpqrstwxyz123456789';
			$max=strlen($base)-1;
			$data_voucher['code']='ELT-';
			mt_srand((double)microtime()*1000000);
			while (strlen($data_voucher['code'])<$len+1) $data_voucher['code'].=$base{mt_rand(0,$max)};

			$voucher_dest_infos = $this->db->query("SELECT firstname, lastname, email FROM ". DB_PREFIX ."customer WHERE `customer_id`=".$customer_id);
			
			$data_voucher['from_name']=$this->getSetting("loyalty_from_name");
			$data_voucher['from_email']=$this->getSetting("loyalty_from_email");
			$data_voucher['to_name']=$voucher_dest_infos->row['firstname']." ".$voucher_dest_infos->row['lastname'];
			$data_voucher['to_email']=$voucher_dest_infos->row['email'];
			$data_voucher['message']=$this->getSetting("loyalty_voucher_message");
			$data_voucher['amount']=$nbofvoucher*$this->getSetting("loyalty_threshold");
			$data_voucher['voucher_theme_id']=$this->getSetting("loyalty_voucherid");
			$data_voucher['status']=1;
			
			$this->load->model('sale/voucher');
				
			// add voucher
			$this->model_sale_voucher->addVoucher($data_voucher);
			
			// get the voucher id
			$voucher_id = $this->db->query("SELECT voucher_id FROM ". DB_PREFIX ."voucher WHERE to_email like \"".$voucher_dest_infos->row['email']."\" ORDER BY voucher_id DESC");
			
			// send voucher
			$this->model_sale_voucher->sendVoucher($voucher_id->row['voucher_id']);
		}
	}
}
?>
