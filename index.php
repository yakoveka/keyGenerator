<?php
/*
Plugin Name: Key Generator and Validator
Description: Show author information and related author post with author contact option
Version: 1.0
Author: Eugene Yakovenko
Author URI: http://vk.com
License: GPLv2 or later
*/
require_once ('vendor/autoload.php');
register_activation_hook( __FILE__, 'create_plugin_database_table' );

function create_plugin_database_table() //creating table after plugin activation
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'keys';
    $sql1 = "DROP TABLE wp_keys;";
    $sql = "CREATE TABLE {$table_name} (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		active smallint (1) DEFAULT '1' NOT NULL,
		subscription_id varchar (2000) NULL,
		license_key varchar (2000),
		type smallint(1),
		UNIQUE KEY id (id)
	) {$charset_collate};";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta($sql1);
    dbDelta( $sql );
}

add_action('asp_stripe_payment_completed', 'asp_after_txn_callback', 10 ,2);
function asp_after_txn_callback ($post_data, $charge) //action is called after Stripe payment submit
{
    \Stripe\Stripe::setApiKey('sk_test_DTa07xqqmpXWaa5WPE6CwBDW001OslBaWA');
    if($post_data['product_id']==7){   //if product is monthly subscription

        $email = $post_data['stripeEmail'];
        $customer = \Stripe\Customer::create(array(
            "email" => $email,
        ));

        $subscription = \Stripe\Subscription::create(array(
            "customer" => $customer->id,
            "plan" => "plan_Esb0Gd8xA6SUta"
        ));
        $subscriptionID = $subscription->id;
    }

    global $wpdb;
    $stripeEmail = $post_data['stripeEmail'];
    $to      = $stripeEmail;
    $subject = 'Activation code of MUL8R';
    $message = 'Hello!
This is your license key: ';
    $table_name = $wpdb->prefix . 'keys';
    $length = 15;
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    if($post_data['product_id']==7) {
        $sql = array('active' => 1, 'subscription_id' => $subscriptionID, 'license_key' => $randomString, 'type' => 1);
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->insert($table_name, $sql);
        $message .= $randomString;
        $message .= "  Link to cancel your subscription: http://wordpress.test/cancel-subscription?subID=".$subscriptionID;
    }
    else{
        $sql = array('active' => 1, 'license_key' => $randomString, 'type' => 2);
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        $wpdb->insert($table_name, $sql);
        $message .= $randomString;
    }
    wp_mail($to, $subject, $message);
}

function key_checker(){
    global $wpdb;
    $trueArray = array("result"=>"true");
    $falseArray = array("result"=>"false");
    $check = $wpdb->get_results( "SELECT * FROM wp_keys where license_key = '{$_GET['key']}' and active = '1'" );
    if(!empty($check))
        return $trueArray;
    else
        return $falseArray;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'keyGenerator/v1', '/check-key', array(
        'methods' => 'GET',
        'callback' => 'key_checker',
    ) );
} );

$request_uri = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );

if($request_uri  == ('/cancel-subscription/')){
    add_filter('the_content', 'cancel_subscription');
}

function cancel_subscription(){
    global $wpdb;
    \Stripe\Stripe::setApiKey('sk_test_DTa07xqqmpXWaa5WPE6CwBDW001OslBaWA');
    $subscription_id = $_GET['subID'];
    if(\Stripe\Subscription::retrieve($subscription_id)) {
        $sub = \Stripe\Subscription::retrieve($subscription_id);
        if ($sub->cancel()) {
            $wpdb->update('wp_keys', array('active'=>0), array('subscription_id'=>$subscription_id));
            return ("Subscription was cancelled");
        }
    }
    else
        return("Oops, something went wrong");
}