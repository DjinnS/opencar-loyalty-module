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
}
?>
