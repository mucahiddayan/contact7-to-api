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
        #add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wpcf7_before_send_mail', array($this, 'wpcf7_c7tA_send_to_api'));
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

    public function admin_enqueue_scripts()
    {
        #wp_enqueue_script('c7tA_admin_js', plugin_dir_url(__FILE__) . 'js/admin.js', '1.0', true);
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

        if (empty($forms)) {
            array_push($fields, array('uid' => 0, 'section' => $this->slug, 'label' => '', 'title' => 'No Formular'));

        } else {
            foreach ($forms as $form) {
                array_push($fields, array('uid' => $form->ID, 'section' => $this->slug, 'label' => $form->post_title . ' (' . $form->ID . ')', 'title' => 'Form ' . $form->ID));
            }
        }

        foreach ($fields as $field) {
            add_settings_field($field['uid'], $field['label'], array($this, 'field_callback'), $this->slug, $field['section'], $field);
            #unregister_setting($this->slug, $field['uid']);
            register_setting($this->slug, 'c7tA_' . $field['uid'], array('type' => 'string'));
        }
    }

    public function clear_from_prefix($value, $prefix = "parameter-")
    {
        $newValue;
        if (is_array($value)) {
            $newValue = array();
            foreach ($value as $key => $val) {
                $key = str_replace($prefix, '', $key);
                $newValue[$key] = $val;
            }
        } else {
            $newValue = str_replace($prefix, '', $value);
        }
        return $newValue;
    }

    public function wpcf7_c7tA_send_to_api($wpcf)
    {

        // get the contact form object
        $submission = WPCF7_Submission::get_instance();
        # $wpcf = WPCF7_ContactForm::get_current();
        $posted_data = $submission->get_posted_data();
        // if you wanna check the ID of the Form $wpcf->id
        if (empty($posted_data)) {
            return;
        }
        if ($c7tA = get_option('c7tA_' . $wpcf->id())) {
            $post_data = $this->clear_from_prefix($posted_data);
            $url = $c7tA['api_url'];
            $auth = base64_encode();
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => array(
                        'Content-type: application/x-www-form-urlencoded',
                        'Authorization: Basic $auth',
                    ),
                    'content' => http_build_query(
                        $post_data
                    ),
                    'timeout' => 60,
                ),
            ));

            $resp = file_get_contents($url, false, $context);
        }
    }

    public function field_callback($arguments)
    {
        $uuid = $arguments['uid'];
        $value = get_option('c7tA_' . $uuid);

        if ($uuid == 0) {
            echo '<h4>Es gibt keine Formulare! Erstelle ein neues Formular <a href="' . get_home_url() . '/wp-admin/admin.php?page=wpcf7-new">hier</a>.</h4>';
            return;
        }

        echo '<div class="c7tA-form" id="' . $uuid . '" title="' . $arguments['title'] . '">
				<input type="text" placeholder="Gib API Url ein" value="' . $value['api_url'] . '" title="Gib API Url ein" name="c7tA_' . $uuid . '[api_url]" id="c7tA_api_url"/>
				<input type="text" placeholder="Gib username für API Zugriff ein" value="' . $value['username'] . '" title="API Username" name="c7tA_' . $uuid . '[username]" id="c7tA_api_username"/>
				<input type="password" placeholder="Gib Passwort für Zugriff ein" value="' . $value['password'] . '" title="API Passwort" name="c7tA_' . $uuid . '[password]" id="c7tA_api_password"/>
			</div>
		';
    }
}

new Contact_Seven_To_Api_Connector();