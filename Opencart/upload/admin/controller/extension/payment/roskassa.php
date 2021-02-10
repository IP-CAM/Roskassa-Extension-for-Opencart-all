<?php

class ControllerExtensionPaymentRoskassa extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('extension/payment/roskassa');
		
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$this->model_setting_setting->editSetting('payment_roskassa', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true));
		}
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['merch_login'])) {
			$data['error_merch_login'] = $this->error['merch_login'];
		} else {
			$data['error_merch_login'] = '';
		}
		
		if (isset($this->error['e_password'])) {
			$data['error_password'] = $this->error['e_password'];
		} else {
			$data['error_password'] = '';
		}

		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/roskassa', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_ru'] = $this->language->get('text_ru');

		$data['entry_login'] = $this->language->get('entry_login');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_result_url'] = $this->language->get('entry_result_url');
		$data['entry_success_url'] = $this->language->get('entry_success_url');
		$data['entry_fail_url'] = $this->language->get('entry_fail_url');
		$data['entry_test'] = $this->language->get('entry_test');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_country'] = $this->language->get('entry_country');
		
		$data['action'] = $this->url->link('extension/payment/roskassa', 'user_token=' . $this->session->data['user_token'], true);
		
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true);

		if (isset($this->request->post['payment_roskassa_login'])) {
			$data['payment_roskassa_login'] = $this->request->post['payment_roskassa_login'];
		} else {
			$data['payment_roskassa_login'] = $this->config->get('payment_roskassa_login');
		}
		
		if (isset($this->request->post['payment_roskassa_password'])) {
			$data['payment_roskassa_password'] = $this->request->post['payment_roskassa_password'];
		} else {
			$data['payment_roskassa_password'] = $this->config->get('payment_roskassa_password');
		}
		
		if(!empty($_SERVER['HTTPS']) && 'off' !== strtolower($_SERVER['HTTPS'])) {
			$data['payment_roskassa_result_url'] 		= 'https://' . $_SERVER['SERVER_NAME'] . '/index.php?route=extension/payment/roskassa/result';
			$data['payment_roskassa_success_url'] 	= 'https://' . $_SERVER['SERVER_NAME'] . '/index.php?route=extension/payment/roskassa/success';
			$data['payment_roskassa_fail_url'] 		= 'https://' . $_SERVER['SERVER_NAME'] . '/index.php?route=extension/payment/roskassa/fail';
		}else{
			$data['payment_roskassa_result_url'] 		= HTTP_CATALOG . 'index.php?route=extension/payment/roskassa/result';
			$data['payment_roskassa_success_url'] 	= HTTP_CATALOG . 'index.php?route=extension/payment/roskassa/success';
			$data['payment_roskassa_fail_url'] 		= HTTP_CATALOG . 'index.php?route=extension/payment/roskassa/fail';
		}
		
		if (isset($this->request->post['payment_roskassa_test'])) {
			$data['payment_roskassa_test'] = $this->request->post['payment_roskassa_test'];
		} else {
			$data['payment_roskassa_test'] = $this->config->get('payment_roskassa_test');
		}
		
		if (isset($this->request->post['payment_roskassa_country'])) {
			$data['payment_roskassa_country'] = $this->request->post['payment_roskassa_country'];
		} elseif($this->config->get('payment_roskassa_country')) {
			$data['payment_roskassa_country'] = $this->config->get('payment_roskassa_country');
		}else{
			$data['payment_roskassa_country'] = "RUB";
		}
		
		if (isset($this->request->post['payment_roskassa_order_status_id'])) {
			$data['payment_roskassa_order_status_id'] = $this->request->post['payment_roskassa_order_status_id'];
		} else {
			$data['payment_roskassa_order_status_id'] = $this->config->get('payment_roskassa_order_status_id'); 
		}
		
		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payment_roskassa_geo_zone_id'])) {
			$data['payment_roskassa_geo_zone_id'] = $this->request->post['payment_roskassa_geo_zone_id'];
		} else {
			$data['payment_roskassa_geo_zone_id'] = $this->config->get('payment_roskassa_geo_zone_id'); 
		}
		
		$this->load->model('localisation/geo_zone');
		
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		
		
		if (isset($this->request->post['payment_roskassa_status'])) {
			$data['payment_roskassa_status'] = $this->request->post['payment_roskassa_status'];
		} else {
			$data['payment_roskassa_status'] = $this->config->get('payment_roskassa_status');
		}
		
		if (isset($this->request->post['payment_roskassa_status_iframe'])) {
			$data['payment_roskassa_status_iframe'] = $this->request->post['payment_roskassa_status_iframe'];
		} else {
			$data['payment_roskassa_status_iframe'] = $this->config->get('payment_roskassa_status_iframe');
		}
		
		if (isset($this->request->post['payment_roskassa_sort_order'])) {
			$data['payment_roskassa_sort_order'] = $this->request->post['payment_roskassa_sort_order'];
		} else {
			$data['payment_roskassa_sort_order'] = $this->config->get('payment_roskassa_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/roskassa', $data));
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/roskassa')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_roskassa_login']) {
			$this->error['merch_login'] = $this->language->get('error_merch_login');
		}
		
		if (!$this->request->post['payment_roskassa_password']) {
			$this->error['e_password'] = $this->language->get('error_password');
		}
		
		return !$this->error;
	}
}