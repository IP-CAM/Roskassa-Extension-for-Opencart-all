<?php
class ModelExtensionPaymentRoskassa extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/roskassa');
		
		if ($this->config->get('payment_roskassa_status')) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_roskassa_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
			
			if (!$this->config->get('payment_roskassa_geo_zone_id')) {
				$status = TRUE;
			} elseif ($query->num_rows) {
				$status = TRUE;
			} else {
				$status = FALSE;
			}
		} else {
			$status = FALSE;
		}
		
		$method_data = array();
		
		if ($status) {
			$method_data = array(
				'code'       => 'roskassa',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_roskassa_sort_order')
			);
		}
		return $method_data;
	}
	
	// Товары заказа
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}
	
	// UPC
	public function getUPCProduct($product_id) {
		$query = $this->db->query("SELECT upc FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");

		return $query->row['upc'];
	}
	
	// shipping
	public function getTotalShipping($order_id, $code = 'shipping') {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' AND code = '" . $code . "'");

		return $query->row;
	}

}