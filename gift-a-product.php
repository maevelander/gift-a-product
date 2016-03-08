<?php
/**
 * Plugin Name:     Gift a Product - Easy Digital Downloads
 * Plugin URI:      https://enigmaplugins.com
 * Description:     Gift a Product extension for Easy Digital Downloads
 * Version:         1.0.0
 * Author:          Enigma Plugins
 * Author URI:      https://enigmaplugins.com
 * Text Domain:     gift-a-product
 *
 * @package         EDD\PluginName
 * @author          @todo
 * @copyright       Copyright (c) @todo
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('EDD_Gift_Product')) {

    class EDD_Gift_Product {

        private static $instance;

        public static function instance() {
            if (!self::$instance) {
                self::$instance = new EDD_Gift_Product();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
                self::$instance->hooks();
            }

            return self::$instance;
        }

        private function setup_constants() {
            // Plugin version
            define('EDD_GIFT_PRODUCT_VER', '1.0.0');

            // Plugin path
            define('EDD_GIFT_PRODUCT_DIR', plugin_dir_path(__FILE__));

            // Plugin URL
            define('EDD_GIFT_PRODUCT_URL', plugin_dir_url(__FILE__));
        }

        private function includes() {
            // Include scripts
            require_once EDD_GIFT_PRODUCT_DIR . 'includes/scripts.php';
            require_once EDD_GIFT_PRODUCT_DIR . 'includes/functions.php';

        }

        private function hooks() {
            // Register settings
            add_filter('edd_settings_extensions', array($this, 'settings'), 1);

            // Handle licensing
            // @todo        Replace the Plugin Name and Your Name with your data
            if (class_exists('EDD_License')) {
                $license = new EDD_License(__FILE__, 'Gift a Product', EDD_GIFT_PRODUCT_VER, 'Enigma Plugins');
            }
        }

        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = EDD_GIFT_PRODUCT_VER . '/languages/';
            $lang_dir = apply_filters('edd_plugin_name_languages_directory', $lang_dir);

            // Traditional WordPress plugin locale filter
            $locale = apply_filters('plugin_locale', get_locale(), 'edd-plugin-name');
            $mofile = sprintf('%1$s-%2$s.mo', 'edd-plugin-name', $locale);

            // Setup paths to current locale file
            $mofile_local = $lang_dir . $mofile;
            $mofile_global = WP_LANG_DIR . '/gift-a-product/' . $mofile;

            if (file_exists($mofile_global)) {
                // Look in global /wp-content/languages/edd-plugin-name/ folder
                load_textdomain('gift-a-product', $mofile_global);
            } elseif (file_exists($mofile_local)) {
                // Look in local /wp-content/plugins/edd-plugin-name/languages/ folder
                load_textdomain('gift-a-product', $mofile_local);
            } else {
                // Load the default language files
                load_plugin_textdomain('gift-a-product', false, $lang_dir);
            }
        }

        public function settings($settings) {
            $new_settings = array(
                array(
                    'id' => 'edd_plugin_name_settings',
                    'name' => '<strong>' . __('Plugin Name Settings', 'gift-a-product') . '</strong>',
                    'desc' => __('Configure Plugin Name Settings', 'gift-a-product'),
                    'type' => 'header',
                )
            );

            return array_merge($settings, $new_settings);
        }
    }
} // End if class_exists check

function EDD_Gift_Product_load() {
    if (!class_exists('Easy_Digital_Downloads')) {
        if (!class_exists('EDD_Extension_Activation')) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation(plugin_dir_path(__FILE__), basename(__FILE__));
        $activation = $activation->run();
    } else {
        return EDD_Gift_Product::instance();
    }
}

add_action('plugins_loaded', 'EDD_Gift_Product_load');

function edd_plugin_name_activation() {
    /* Activation functions here */
    
    add_option('edd_gift_product','off','','yes');
}

register_activation_hook(__FILE__, 'edd_plugin_name_activation');

// EDD Scripts & Style
add_action('wp_enqueue_scripts', 'edd_gift_product_scripts');
function edd_gift_product_scripts() {
    wp_enqueue_style('edd_gift_style', EDD_GIFT_PRODUCT_URL . 'assets/css/styles.css');

    wp_enqueue_script('edd_gift_script', EDD_GIFT_PRODUCT_URL . 'assets/js/scripts.js', array(), '1.0.0', true);
}

// Creating AJAX for saving value in databsae
add_action('wp_ajax_edd_gift_email_values', 'edd_gift_check_value');
add_action('wp_ajax_nopriv_edd_gift_email_values', 'edd_gift_check_value');

add_action("wp_head", "edd_gift_email_ajax");
function edd_gift_email_ajax() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery("#edd-gift-purchase").click(function(){
                if(jQuery(this).is(":checked")) {
                    jQuery(".edd_gift_product_div").fadeIn(300);
                } else {
                    jQuery(".edd_gift_product_div").fadeOut();
                }
            });
            
            jQuery("#edd-gift-purchase").click(function () {
                var gift_method = jQuery(this).is(":checked") ? 'save' : 'delete';
                
                var data = {
                    action: 'edd_gift_email_values',
                    edd_gift_method: gift_method
                };
                
                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (result) {
                    //alert(result);
                });
            });
        });
    </script>
    <?php
}

// Save and delete values in database
function edd_gift_check_value() {
    $edd_gift_method = $_REQUEST['edd_gift_method'];

    switch( $edd_gift_method ){
        case 'save':
            //echo "Save Data";
            update_option('edd_gift_product', 'on');
        break;
        case 'delete':
            //echo "Delete Data";
            update_option('edd_gift_product', 'off');
        break;  
    }
}
