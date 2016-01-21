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
}

register_activation_hook(__FILE__, 'edd_plugin_name_activation');

// EDD Scripts & Style
add_action('wp_enqueue_scripts', 'edd_gift_product_scripts');
function edd_gift_product_scripts() {
    wp_enqueue_style('edd_gift_style', EDD_GIFT_PRODUCT_URL . 'assets/css/styles.css');

    wp_enqueue_script('edd_gift_script', EDD_GIFT_PRODUCT_URL . 'assets/js/scripts.js', array(), '1.0.0', true);
}

add_action('wp_ajax_edd_gift_email_values', 'edd_gift_send_email');
add_action('wp_ajax_nopriv_edd_gift_email_values', 'edd_gift_send_email');

// ajax
add_action("wp_head", "edd_gift_email_ajax");
function edd_gift_email_ajax()
{
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery(".edd_gift_product_btn").click(function () {

                var edd_gift_cart_info = jQuery('.cart_info').val();

                var data = {
                    action: 'edd_gift_email_values',
                    edd_email_cart_info: edd_gift_cart_info
                }

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (result) {
                    //alert(result);
                });

            });
        });

        <?php
            global $wpdb;

            $sql = $wpdb->get_results("Select * From ".$wpdb->postmeta." Where meta_key = 'edd_gift_this_product'");

            $edd_gift_this_product = '';
            foreach ($sql as $row) {
                $edd_gift_this_product = $row->meta_value;
            }
            echo $edd_gift_this_product;

            if($edd_gift_this_product == '1') {
        ?>
                jQuery(document).ready(function () {
                    jQuery("#edd-purchase-button").hide();
                    jQuery(".edd_gift_product_btn").show();

                    jQuery("#edd-gift-purchase-button").clone().appendTo(".edd_gift_product_div");
                    jQuery("#edd-gift-purchase-button").show();

                    jQuery(".edd_gift_product_div").show();
                    jQuery(".edd_gift_product_btn").hide();
                });
        <?php
            }
        ?>
    </script>
    <?php
}

function edd_gift_send_email() {
    global $wpdb;

    $edd_gift_info = stripcslashes($_REQUEST['edd_email_cart_info']);

    $edd_cart_info_un_sr = unserialize($edd_gift_info);

    foreach ($edd_cart_info_un_sr as $user_gift_cart) {
        $edd_gift_prod_user_id = $user_gift_cart['edd_gift_prod_id'];

        echo $edd_gift_prod_user_id;

        update_post_meta($edd_gift_prod_user_id, 'edd_gift_this_product', '1');
    }
}
