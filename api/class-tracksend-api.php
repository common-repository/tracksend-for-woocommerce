<?php

/**
 * The api-specific functionality of the plugin.
 *
 * @link       tracksend.co
 * @since      1.0.0
 *
 * @package    Tracksend
 * @subpackage Tracksend/api
 */

/**
 * The api-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the api-specific stylesheet and JavaScript.
 *
 * @package    Tracksend
 * @subpackage Tracksend/api
 * @author     Tracksend <api@tracksend.co>
 */
class Tracksend_api
{
	/**
	 * Define Class constants 
	 */
	const CART_SESSION_KEY    = 'tracksend_cart_session_id';
	const API_URL = 'https://be1.tracksend.co/api/v1/woocommerce/';


	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function tracksend_is_not_integrated()
	{
		if (get_option('tracksend_api_key') !== false) {
			return false;
		}
		return true;
	}



	public function handleOrderUpdate($order_id, $old_status, $new_status)
	{
		if ($this->tracksend_is_not_integrated()) {
			return; // Plugin is not active or we cant read phone number so no point
		}
		// Remove the tracker from session once order is created 
		$this->remove_tracksend_session_id();

		// When Order is Updated Send Order data to Tracksend API to continue processing 
		$customer_key = get_option('tracksend_api_key');
		$orderDetails = new WC_Order($order_id);
		$tracksendOrder = [
			'order_info' => $orderDetails->get_data(),
			'tracksend_key' => $customer_key,
			'checkout_url' => wc_get_checkout_url(),
		];
       
		// Make API Call to the tracksend with the order details 
		$response = $this->send_to_tracksend('woocommerceintegration', $tracksendOrder);


		// Save Log for Reference 
		//tracksend_log('debug', "Order ID {$order_id} was {$old_status} and is now {$new_status}", array('response' => $response['status']));
	}



	public function callback_handler()
	{
		global $wpdb, $_data;
		
		// Get Session ID and check if it exists 
		$session_id = isset( $_GET['session'] ) ? sanitize_text_field($_GET['session']) : '';
		$customer_id = isset( $_GET['user'] ) ?  sanitize_text_field($_GET['user']) : '';

		if ($customer_id){
			$customer = new WC_Customer(intval($customer_id));

			//$tracksend_session_id = $this->get_tracksend_session_id();

			$formatted_cart=[];

			$sessionData = $wpdb->get_results("SELECT session_value FROM {$wpdb->prefix}woocommerce_sessions WHERE session_key=$customer_id ");

			$carts_contents = unserialize($sessionData[0]->session_value)['cart'];

			$cart_totals = unserialize($sessionData[0]->session_value)['cart_totals'];

			$session_trk_id = unserialize($sessionData[0]->session_value)['tracksend_cart_session_id'];

			$cart_contents = unserialize($carts_contents);
			
			foreach($cart_contents as $contentkey => $content){
				$product = wc_get_product(intval($content['product_id']));

				array_push($formatted_cart, [		
						'quantity'=> $content['quantity'],
						'product_name' => $product->get_name(),
						'price' => intval($product->get_price()),
				]);
			}
		
			if ($session_trk_id == $session_id) {
				// Get Cart Contents
				$cart_info = [
					'status'=>'abandoned',
					'api_key' => get_option('tracksend_api_key'),
					'cart_session_id' => $session_id,
					'customer' => $customer->get_billing(),
					'customer_id' => WC()->session->get_customer_id(),
					'user' => $customer_id,
					'cart_url' => wc_get_cart_url(),
					'cart_items' => $formatted_cart,
					'total' => unserialize($cart_totals)['total'],
					'currency' => get_woocommerce_currency(),
				];

				// Remove the tracker from session once order is created 
			   $this->remove_tracksend_session_id();

				// Make API Call to the tracksend with the cart info details 
				$response = $this->send_to_tracksend('cartevents', $cart_info);

				tracksend_log('debug', 'Cart has been tagged as abandoned and sent with response:');
				//array('response' => $response['status']));
			}

		}
		 

		
		
	}

	public function handleCart()
	{
		
		if ($this->tracksend_is_not_integrated() ) {
			return; // Plugin is not active or we cant read phone number so no point
		}


		$tracksend_session_id = $this->get_tracksend_session_id();
		


		$session_details = WC()->customer->get_billing()['phone'];
		$cart_info = [
			'status'=>'updated',
			'api_key' => get_option('tracksend_api_key'),
			'cart_session_id' => $tracksend_session_id,
			'customer' => WC()->session->get_customer_id(),
			'cart_url' => esc_url(wc_get_cart_url()),
			
		];
		// Make API Call to the tracksend with the cart details 
		$response = $this->send_to_tracksend('cartevents', $cart_info);

		tracksend_log('debug', 'Cart action captured and sent with response');
		//array('response' => $response['status']));
	}

	public function generate_tracksend_session_id()
	{
		$random_data = random_bytes(32); //secure 
		return hash('sha256', $random_data);
	}

	public function get_tracksend_session_id()
	{
		$tracksend_id = WC()->session->get(self::CART_SESSION_KEY, false);
		if ($tracksend_id) {
			return $tracksend_id;
		}
		$tracksend_id = $this->generate_tracksend_session_id();
		WC()->session->set(self::CART_SESSION_KEY, $tracksend_id);
		return $tracksend_id;
	}

	// Check User validity 
	public function invalid_customer()
	{
		$wc_user = WC()->customer->get_billing()['phone'];
		if (!empty($wc_user)) {
			return false;
		}
		return true; // No way to reach this user


	}

	public function remove_tracksend_session_id()
	{
		if( WC()->session){
			WC()->session->__unset(self::CART_SESSION_KEY);
		}
		
	}

	public function send_to_tracksend($endpoint, $body)
	{
		return wp_remote_post(self::API_URL . $endpoint, array(
			'timeout'   => 12,

			'method'      => 'POST',
			'data_format' => 'body',
			'headers'     => array('Content-Type' => 'application/json; charset=utf-8'),
			'body'        => json_encode($body),
		));
	}
}
