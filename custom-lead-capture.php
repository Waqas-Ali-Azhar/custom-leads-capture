<?php

/*
 * Plugin Name:       Custom Lead Capture
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Handles form submissions for services page
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Waqas Ali Azhar
 * Author URI:        https://waqasali.pro
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       clc
 */

  define('CLC_PLUGIN_BASE_URL',plugin_dir_url(__FILE__));
  define('CLC_PLUGIN_BASE_URI',plugin_dir_path(__FILE__));
  define('CLC_ADMIN_ASSETS',plugin_dir_url(__FILE__).'admin/assets/');







  function custom_lead_captures_main(){

  	include CLC_PLUGIN_BASE_URI.'admin/templates/custom-leads-main.php';

  } 

  

  function wporg_options_page(){

  	add_menu_page(
	    'Custom Leads',
    	'Custom Leads',
   		'manage_options',
    	'clc_leads',
    	'custom_lead_captures_main'
	);

  }
  add_action( 'admin_menu', 'wporg_options_page' );

  function clc_enqueue_styles(){
  	wp_register_style( 'clc-leads', CLC_ADMIN_ASSETS. '/css/style.css', false, '1.0.0' );
    wp_enqueue_style( 'clc-leads' );
  }
  add_action('admin_enqueue_scripts','clc_enqueue_styles');

  function clc_enqueue_styles_public(){
  	
    wp_enqueue_style( 'clc-leads-public', CLC_PLUGIN_BASE_URL. 'assets/css/style.css', false, '1.0.0' );
    wp_enqueue_script( 'clc-leads-public', CLC_PLUGIN_BASE_URL. 'assets/js/script.js', false, '1.0.0' );
  }


  add_action('wp_enqueue_scripts','clc_enqueue_styles_public');


  function clc_activate() { 
  	$args = array(
  		'post_status' => 'publish',
  		'post_type' => 'page'
  	);
  	$pages = get_posts($args);

  	$found = false;
  	foreach($pages as $page){
  		if($page->post_name == 'services'){
			$found = true;
	  		break;
  		}
  	}

  	if(!$found){

  		$wordpress_post = array(
		'post_title' => 'Services',
		'post_content' => '<form>
		<div class="field-group">
		  <label></label>
		  <input type="text" name="fname" placeholder="please write your first name" />
		</div>
		</form>',
		'post_status' => 'publish',
		'post_author' => 1,
		'post_type' => 'page');
		// print_r($wordpress_post);
		// exit;
	 
	  wp_insert_post( $wordpress_post );

  	}

  	global $wpdb;

  	

	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix.'custom_leads';

	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  first_name varchar(255) NOT NULL,
	  last_name varchar(255) NOT NULL,
	  contact varchar(255) NOT NULL,
	  
	  PRIMARY KEY  (id)
	) $charset_collate;";


	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

}
register_activation_hook( __FILE__, 'clc_activate' );

function clc_lead_form($content){

	

	if(is_page( 'services' ) ){
    	
    	if(!empty($_POST['lead'])){

    		global $wpdb;

    		$sql = "INSERT INTO $wpdb->prefix"."custom_leads (`time`,`first_name`,`last_name`,`contact`	) VALUES('".date("Y-m-d H:i:s")."','".$_POST['first_name']."','".$_POST['last_name']."','".$_POST['contact']."')";

    		$inserted = $wpdb->query($sql);

    	}


      $form = '<form id="clc-lead" method="POST">
		<div class="field-group">
			<label>First Name</label>
			<input type="text" name="first_name" placeholder="Please write your First Name here" />
		</div>
		<div class="field-group">
			<label>Last Name</label>
			<input type="text" name="last_name" placeholder="Please write your Last Name here" />
		</div>
		<div class="field-group">
			<label>Contact</label>
			<input type="text" name="contact" placeholder="Please write your Last Name here" />
		</div>
		<div class="field-group">
			<input type="hidden" name="lead" value="lead" />
			<input type="submit" name="submit" value="Submit" />
		</div>
		</form>';

		if(!empty($inserted)){
			$form = '<div id="success" class="success hide">Thank you for your submission.</div>'.$form;
		}

		
		return $content.$form;
	}
	return $content;

	

}

add_filter('the_content','clc_lead_form',10,1);
