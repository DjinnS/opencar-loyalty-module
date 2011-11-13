<?php
/*
* Author  : DjinnS - djinns@chninkel.net
* 
* License : GPL v2 (http://www.gnu.org/licenses/gpl-2.0.html)
*/

class ModelTotalLoyalty extends Model {

	/*
	*	Name      	: index()
	*	Parameters	: none
	*	Rerturn		: none
	*/
	public function getTotal(&$total_data, &$total, &$taxes) {
		$this->load->language('total/loyalty');
		
		$loyalty = $total;
	
		// exclude voucher from loyalty
		if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$loyalti -= $voucher['amount'];
			}
		}

		$loyalty = floor($loyalty / $this->config->get("loyalty_rate"));

		if($loyalty > 1) {
			$total_data[] = array( 
				'code'       => 'loyalty',
				'title'      => $this->language->get('text_loyalties'),
				'text'       => $loyalty,
				'value'      => $loyalty,
				'sort_order' => $this->config->get('loyalty')
			);
		} else {
			$total_data[] = array(
				'code'       => 'loyalty',
                'title'      => $this->language->get('text_loyalty'),
				'text'       => $loyalty,
                'value'      => $loyalty,
                'sort_order' => $this->config->get('loyalty')
			);
		}
	}
}
?>
