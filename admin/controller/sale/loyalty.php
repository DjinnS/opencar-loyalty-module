<?php

/*
* Author  : DjinnS - djinns@chninkel.net
* 
* License : GPL v2 (http://www.gnu.org/licenses/gpl-2.0.html)
*/

class ControllerSaleLoyalty extends Controller {

	private $error = array();

	/*
	*	Name      	: index()
	*	Parameters	: none
	*	Rerturn		: none
	*/	
  	public function index() {

		$this->getForm();
  	}

	/*
	*	Name      	: getForm()
	*	Parameters	: none
	*	Rerturn		: none
	*/
	private function getForm() {
	
		// load model, language and template file
		$this->load->language('sale/loyalty');
		$this->load->model('sale/loyalty');
        $this->template = 'sale/loyalty.tpl';
		
		// template remplacement
		$this->data['heading_title']				= $this->language->get('heading_title');
		$this->data['loyalty_rate']					= $this->language->get('loyalty_rate');
		$this->data['loyalty_rate_explain']			= $this->language->get('loyalty_rate_explain');
		$this->data['loyalty_threshold']			= $this->language->get('loyalty_threshold');
		$this->data['loyalty_threshold_explain']	= $this->language->get('loyalty_threshold_explain');
		$this->data['loyalty_gain']					= $this->language->get('loyalty_gain');
		$this->data['loyalty_gain_explain']			= $this->language->get('loyalty_gain_explain');
		$this->data['loyalty_voucher']				= $this->language->get('loyalty_voucher');
		$this->data['loyalty_voucher_explain']		= $this->language->get('loyalty_voucher_explain');
		
		// get config value
		$this->data['loyalty_config_rate']			= $this->config->set("loyalty_rate");
		$this->data['loyalty_config_threshold']		= $this->config->set("loyalty_threshold");
		$this->data['loyalty_config_gain']			= $this->config->set("loyalty_gain");
		
		// get the currency symbol (right symbol by default)
		if($this->currency->getSymbolRight($this->currency->getCode())) {
			$this->data['loyalty_currency'] = $this->currency->getSymbolRight($this->currency->getCode());
		} else {
			$this->data['loyalty_currency'] = $this->currency->getSymbolLeft($this->currency->getCode());
		}
		
		// template form remplacement
		$this->data['button_save']			= $this->language->get('button_save');
		$this->data['button_cancel']		= $this->language->get('button_cancel');

		// set the action of the form
		$this->data['action']				= $this->url->link('sale/loyalty/update','token='.$this->session->data['token'],'SSL');

		$this->children = array(
			'common/header',
			'common/footer',
		);
		
		// get setting from the setting table
		$this->data['loyalty_config_rate'] 		=  $this->model_sale_loyalty->getSetting("loyalty_rate");
		$this->data['loyalty_config_threshold'] =  $this->model_sale_loyalty->getSetting("loyalty_threshold");
		$this->data['loyalty_config_gain'] 		=  $this->model_sale_loyalty->getSetting("loyalty_gain");

		// print errors or success
		if(isSet($this->session->data['error'])) {
			$this->data['text_error'] = $this->language->get($this->session->data['error']);
			unset($this->session->data['error']); // delete after reload with success
		} elseif(isSet($this->session->data['success'])) {
			$this->data['text_success'] = $this->language->get($this->session->data['success']);
			unset($this->session->data['success']); // delete after reload with success
		}
		
		// get voucher list
		$this->getVoucher();
		
		$this->response->setOutput($this->render());
	}
	
	/*
	*	Name      	: update()
	*	Parameters	: none
	*	Rerturn		: none
	*/
	public function update() {

		// load model and template
		$this->load->model('sale/loyalty');
		$this->template = 'sale/loyalty.tpl';

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
		
			$this->model_sale_loyalty->updateSetting($this->request->post); // query db with the $_POST[] array
			
			$this->session->data['success'] = $this->language->get('text_success'); // reload with success

			$this->redirect($this->url->link('sale/loyalty', 'token=' . $this->session->data['token'] . $url, 'SSL')); // redirect
		}
		
		$this->getForm();
	}

	/*
	*	Name      	: validateForm()
	*	Parameters	: none
	*	Rerturn		: true if form is valid
	*				  false if form is invalid (permission or data format)
	*/
	private function validateForm() {
	
		// check ACL to update loyalty setting
		if (!$this->user->hasPermission('modify', 'sale/loyalty')) $this->session->data['error'] = $this->language->get('error_permission');

		// values must be > 0 otherwise disable this module ;)
		if ($this->request->post['loyalty_rate'] <= 0) 		$this->session->data['error'] = $this->language->get('error_rate');
		if ($this->request->post['loyalty_threshold'] <= 0) $this->session->data['error'] = $this->language->get('error_threshold');
		if ($this->request->post['loyalty_gain'] <= 0) 		$this->session->data['error'] = $this->language->get('error_gain');
		
		if ($this->session->data['error']) {
			return false;
		} else {
			return true;
		}
	}

	/*
	*	Name      	: getVoucher()
	*	Parameters	: none
	*	Rerturn		: 
	*/
	private function getVoucher() {
	
		$this->load->model('sale/voucher_theme');

		$data = array(
			'limit' => 1000
		);
		
		$results = $this->model_sale_voucher_theme->getVoucherThemes($data);
 
    	foreach ($results as $result) {
			$action = array();
			
			$this->data['voucher_themes'][] = array(
				'voucher_theme_id' => $result['voucher_theme_id'],
				'name'             => $result['name'],
				'selected'         => ($this->model_sale_loyalty->getSetting("loyalty_voucherid") == $result['voucher_theme_id']) ? 1 : 0,
			);
		}
	}
}
?> 
