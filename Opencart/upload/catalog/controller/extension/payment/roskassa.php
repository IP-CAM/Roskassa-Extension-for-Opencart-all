<?php

class ControllerExtensionPaymentRoskassa extends Controller
{
	public function index()
	{

		$data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$data['payment_url'] = 'https://pay.roskassa.net/';

		$password = $this->config->get('payment_roskassa_password');

		$data['roskassa_login'] = $this->config->get('payment_roskassa_login');

		$data['inv_id'] = $this->session->data['order_id'];
		
		if($order_info['currency_code'] == 'RUB'){
			$data['out_summ_currency'] = $order_info['currency_code'];
		}
		
		$data['out_summ'] = $this->currency->format($order_info['total'], $order_info['currency_code']);
		$data['out_summ'] = str_ireplace(" ", "", $data['out_summ']);
		$data['out_summ'] = preg_replace('/[^0-9 , .]/', '', $data['out_summ']);
		$data['out_summ'] = (float)$data['out_summ'];

		$data['products'] = array();

		$i = 0;
		foreach ($this->cart->getProducts() as $product) {

			$data['products']['receipt[items]['.$i.'][name]'] = $product['name'];
			$data['products']['receipt[items]['.$i.'][count]'] = $product['quantity'];
			$data['products']['receipt[items]['.$i.'][price]']  = $product['total'];

			$i++;

		}

		if ($this->config->get('payment_roskassa_test')) {
			$data['roskassa_test'] = '1';
		} else {
			$data['roskassa_test'] = '0';
		}

		$arrSign = array(
			'shop_id' => $data['roskassa_login'],
			'order_id' => $data['inv_id'],
			'amount' => $data['out_summ'],
			'currency' => $data['out_summ_currency'],
			'test' => $data['roskassa_test'],
		);
		ksort($arrSign);
		$str = http_build_query($arrSign);
		$data['sign'] = md5($str . $password);
		

		return $this->load->view('extension/payment/roskassa', $data);
	}

	public function success()
	{

		$order_id = $this->request->get["order_id"];


		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

		if ($order_info['order_status_id'] == 0) {
			$this->model_checkout_order->addOrderHistory($order_id, $this->config->get('config_order_status_id'));
		}

		$this->response->redirect($this->url->link('checkout/success', '', true));

		return true;
	}

	public function fail()
	{

		$this->response->redirect($this->url->link('checkout/checkout', '', true));

		return true;
	}

	public function result()
	{
		$password = $this->config->get('payment_roskassa_password');

		$out_summ = $this->request->post['amount'];
		$order_id = $this->request->post["order_id"];
		$sign = $this->request->post["sign"];

		$data = $this->request->post;

		unset($data['sign']);
		ksort($data);
		$str = http_build_query($data);
		$my_sign = md5($str . $password);

		if ($my_sign == $sign) {
			$this->load->model('checkout/order');

			$order_info = $this->model_checkout_order->getOrder($order_id);
			$new_order_status_id = $this->config->get('payment_roskassa_order_status_id');

			if ($order_info['order_status_id'] == 0) {
				$this->model_checkout_order->addOrderHistory($order_id, $new_order_status_id);
			}

			if ($order_info['order_status_id'] != $new_order_status_id) {
				$this->model_checkout_order->addOrderHistory($order_id, $new_order_status_id);

				if ($this->config->get('payment_roskassa_test')) {
					$this->log->write('ROSKASSA в заказе: ' . $order_id . '. Статус заказа успешно изменен');
				}

			}


			return true;
		} else {

			if ($this->config->get('payment_roskassa_test')) {
				$this->log->write('ROSKASSA ошибка в заказе: ' . $order_id . '. Контрольные подписи не совпадают');
			}

		}

	}
	
	public function test(){
		$this->load->model('extension/payment/roskassa');

		$this->model_extension_payment_roskassa->sendSecondCheck(82);
	}
}