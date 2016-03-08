<?php
/**
 * Helper Functions
 *
 * @package     EDD\PluginName\Functions
 * @since       1.0.0
 */


// Exit if accessed directly
if (!defined('ABSPATH')) exit;

// EDD Gift Form
function edd_gift_form() {
    global $wpdb;
    
    $edd_gift_status = get_option('edd_gift_product');
?>
    <div class="gift_a_product">
        <label class="edd-label" for="edd-gift-purchase">
            <?php
                _e('Purchase as gift', 'gift-a-product');
                
                $edd_checked_checkbox = '';
                if($edd_gift_status == 'on') {
                    $edd_checked_checkbox = 'checked';
                }
            ?>
            <input type="checkbox" id="edd-gift-purchase" <?php echo $edd_checked_checkbox; ?> />
        </label>
    </div>

    <div class="edd_gift_product_div" style="display: <?php echo ($edd_gift_status == 'on' ? 'block' : 'none') ?>;">
        <div class="edd_gift_fields">
            <label class="edd-label" for="edd-gift-name">
                <?php _e('Recipient Name', 'gift-a-product'); ?>
                <span class="edd-required-indicator">*</span>
            </label>
            <input class="edd-input" type="text" name="edd_gift_name" id="edd-gift-name"
                   placeholder="<?php _e('Recipient Name', 'gift-a-product'); ?>"/>
        </div>
        <div class="edd_gift_fields">
            <label class="edd-label" for="edd-gift-email">
                <?php _e('Recipient Email', 'gift-a-product'); ?>
                <span class="edd-required-indicator">*</span>
            </label>
            <input class="edd-input" type="text" name="edd_gift_email" id="edd-gift-email"
                   placeholder="<?php _e('Recipient Email', 'gift-a-product'); ?>"/>
        </div>
        <div class="edd_gift_fields">
            <label class="edd-label" for="edd-gift-message">
                <?php _e('Message', 'gift-a-product'); ?>
            </label>
            <textarea class="edd-input" name="edd_gift_message" id="edd-gift-message"
                      placeholder="<?php _e('Message', 'gift-a-product'); ?>"></textarea>
        </div>
    </div>
<?php
}
add_action('edd_purchase_form_user_info', 'edd_gift_form');

// EDD Form Submit Button
add_action('edd_purchase_form_after_submit', 'edd_gift_product_button');
function edd_gift_product_button() {
    $edd_gift_color = edd_get_option( 'checkout_color', 'blue' );
    $edd_gift_color = ( $edd_gift_color == 'inherit' ) ? '' : $edd_gift_color;
    $edd_gift_style = edd_get_option( 'button_style', 'button' );
    $edd_gift_label = edd_get_option( 'checkout_label', '' );

    if ( edd_get_cart_total() ) {
        $gift_complete_purchase = ! empty( $edd_gift_label ) ? $edd_gift_label : __( 'Purchase', 'gift-a-product' );
    } else {
        $gift_complete_purchase = ! empty( $edd_gift_label ) ? $edd_gift_label : __( 'Free Download', 'gift-a-product' );
    }

    ob_start();
}

$edd_gift_this_product = get_option('edd_gift_product');
if($edd_gift_this_product == 'on') {

    function edd_gift_required_checkout_fields($required_fields) {
        $required_fields = array(
            'edd_gift_email' => array(
                'error_id' => 'invalid_gift_email',
                'error_message' => 'Please enter a valid receipt email'
            ),
            'edd_gift_name' => array(
                'error_id' => 'invalid_gift_name',
                'error_message' => 'Please enter receipt name'
            ),
            'edd_first' => array(
                'error_id' => 'invalid_name',
                'error_message' => 'Please enter your name'
            ),
        );

        return $required_fields;
    }

    add_filter('edd_purchase_form_required_fields', 'edd_gift_required_checkout_fields');

// check for errors with out custom fields
    function edd_gift_validate_checkout_fields($valid_data, $data) {
        if (empty($data['edd_gift_email'])) {
            edd_set_error('invalid_gift_email', 'Please enter a valid receipt email');
        }

        if (empty($data['edd_gift_name'])) {
            edd_set_error('invalid_gift_name', 'Please enter receipt name');
        }

        if (empty($data['edd_first'])) {
            edd_set_error('invalid_name', 'Please enter your name');
        }
    }

    add_action('edd_checkout_error_checks', 'edd_gift_validate_checkout_fields', 10, 2);

// EDD Form Value Save
    function edd_gift_value_store($payment_meta) {
        $payment_meta['edd_gift_name'] = isset($_POST['edd_gift_name']) ? $_POST['edd_gift_name'] : '';
        $payment_meta['edd_gift_email'] = isset($_POST['edd_gift_email']) ? $_POST['edd_gift_email'] : '';
        $payment_meta['edd_gift_message'] = isset($_POST['edd_gift_message']) ? $_POST['edd_gift_message'] : '';

        return $payment_meta;
    }

    add_filter('edd_payment_meta', 'edd_gift_value_store');

    function pw_edd_on_complete_purchase($payment_id) {
        global $wpdb;

        // Basic payment meta
        $payment_meta = edd_get_payment_meta($payment_id);

        // User Info
        $edd_gift_user_info_id = $payment_meta['user_info']['id'];
        $edd_gift_user_info_email = $payment_meta['user_info']['email'];
        $edd_gift_user_info_fname = $payment_meta['user_info']['first_name'];
        $edd_gift_user_info_lname = $payment_meta['user_info']['last_name'];

        // Gift Receipt Details
        $edd_gift_name = $payment_meta['edd_gift_name'];
        $edd_gift_email = $payment_meta['edd_gift_email'];
        $edd_gift_message = $payment_meta['edd_gift_message'];

        // Key
        $edd_gift_key = $payment_meta['key'];

        // Cart details
        $cart_items = edd_get_payment_meta_cart_details($payment_id);

        // Mail Style
        $edd_gift_mail_body = "style='
                                background-color: #F6F6F6;
                                float: left;
                                width: 100%;
                                height: auto;
                                padding: 18px;'";
        $edd_gift_mail_content = "style='
                                    background-color: #fff;
                                    padding: 4px 10px;
                                    width: 91%;
                                    border: 1px solid #eceaea;
                                    margin-bottom: 14px;
                                    float: left;'";
        $edd_gift_mail_h4 = "style='
                                font-size: 15px;
                                margin: 10px 0 0;
                                padding: 0;'";
        $edd_gift_mail_list_body = "style='
                                    float: left;
                                    width: 102%;
                                    margin-bottom: 14px;'";
        $edd_gift_mail_list = "style='
                                float: left;
                                background-color: #fff;
                                width: 42%;
                                margin: 0 15px 12px 0;
                                padding: 0 10px;
                                border-radius: 6px;
                                border: 1px solid #dfdfdf;'";
        $edd_gift_mail_ul = "style='
                                margin: 10px 0 10px 10px;
                                padding: 0;'";
        $edd_gift_mail_li = "style='
                                padding: 6px 0 12px;
                                line-height: 18px;'";

        $edd_gift_settings = get_option('edd_settings');
        $edd_gift_expire_time = $edd_gift_settings['download_link_expiration'];

        $edd_gift_email_body .= "<div " . $edd_gift_mail_body . ">";
        $edd_gift_email_body .= "<div " . $edd_gift_mail_content . ">";
        $edd_gift_email_body .= "<h3>Hi $edd_gift_name,</h3>";
        $edd_gift_email_body .= "<p>You lucky thing! " . $edd_gift_user_info_fname . ' ' . $edd_gift_user_info_lname . " has just sent you a gift.</p>";
        $edd_gift_email_body .= "<p>Please click on the links(s) below to download your files. Best to grab 'em now as  the linkes expire in  <strong>$edd_gift_expire_time hrs</strong></p>";
        $edd_gift_email_body .= "<strong>Message From " . $edd_gift_user_info_fname . ' ' . $edd_gift_user_info_lname . "</strong>" . "<br>";
        $edd_gift_email_body .= "<p>" . $edd_gift_message . "</p>";
        $edd_gift_email_body .= "</div>";

        $edd_gift_email_body .= "<div " . $edd_gift_mail_list_body . ">";

        $i = 0;

        foreach ($cart_items as $data) {

            $i++;

            $gift_prod_ID = $data['id'];
            $price_ID = get_post_meta($gift_prod_ID, 'edd_price', true);

            $files = edd_get_download_files($gift_prod_ID, $price_ID);

            $get_gift_prod_name = $wpdb->get_row("Select * From " . $wpdb->posts . " Where ID IN($gift_prod_ID)");
            $get_gift_prod_name = $get_gift_prod_name->post_title;

            $get_gift_prod_thumb = $wpdb->get_row("Select * From " . $wpdb->posts . " Where post_parent IN($gift_prod_ID)");
            $get_gift_prod_thumb_ID = $get_gift_prod_thumb->ID;

            $edd_gift_email_body .= "<div " . $edd_gift_mail_list . ">";
            $edd_gift_email_body .= "<h4 " . $edd_gift_mail_h4 . ">" . $get_gift_prod_name . " </h4>";
            $edd_gift_email_body .= "<ul " . $edd_gift_mail_ul . ">";

            foreach ($files as $filekey => $file) {
                $file_url = edd_get_download_file_url($edd_gift_key, $edd_gift_user_info_email, $filekey, $gift_prod_ID, $price_ID);

                $edd_gift_email_body .= "<li " . $edd_gift_mail_li . ">
                                        <strong>" . $file['name'] . "</strong><br>
                                        <a href='" . $file_url . "' target='_blank' download title='" . $file['name'] . "'>Download</a>
                                     </li>";
            }
            $edd_gift_email_body .= "</ul>";
            $edd_gift_email_body .= "</div>";

            if ($i % 2 == 0) {
                $edd_gift_email_body .= '<br clear="all">';
            }

        }

        $edd_gift_email_body .= "</div>";

        $edd_gift_email_body .= "</div>";

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <' . $edd_gift_user_info_email . '>' . "\r\n";

        @mail($edd_gift_email, $edd_gift_user_info_fname . ' send you gift', $edd_gift_email_body, $headers);

    }

    add_action('edd_complete_purchase', 'pw_edd_on_complete_purchase');
}

function edd_gift_message() {
    $edd_gift_status = get_option('edd_gift_product');

    if($edd_gift_status == 'on') {
        global $edd_receipt_args;
        $payment   = get_post( $edd_receipt_args['id'] );

        $edd_gift_payment_ID =  edd_get_payment_number( $payment->ID );
        $_edd_payment_meta = get_post_meta($edd_gift_payment_ID, '_edd_payment_meta', true);

    //    echo "<pre>";
    //    print_r($_edd_payment_meta);
    //    echo "</pre>";

        $edd_gift_first_name = $_edd_payment_meta['user_info']['first_name'];
        $edd_gift_reciept_name = $_edd_payment_meta['edd_gift_name'];
?>
        <div class="edd_gift_payment_message">
            <?php echo $edd_gift_first_name; ?>, <?php _e('you purchased this product(s) as a gift. An email has been sent to','gift-a-product'); ?>
            <strong><?php echo $edd_gift_reciept_name; ?></strong> <?php _e('with the downloads and message.','gift-a-product'); ?>
        </div>
<?php
    } 

    update_option('edd_gift_product', 'off');
}
add_action('edd_payment_receipt_before', 'edd_gift_message');