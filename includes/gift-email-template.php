<div class="edd_gift_template_body">
    <h3>EDD Gift Email Template</h3>

    <div class="edd_gift_template_editor">
        <form method="post" action="options.php">
            <?php
                settings_fields('ajc_settings_group');

                $edd_gift_email_template = wpautop(get_option('edd_gift_email_template'));

                wp_editor(
                        $edd_gift_email_template,
                        'gift_email_template_editor',
                        $settings = array(
                                        'textarea_name' => edd_gift_email_template,
                                        'wpautop' => true
                                        )
                        );
            ?>
            <input type="submit" value="Save Template" name="edd_gift_template_submit" id="edd-gift-template-submit">
        </form>
    </div>

    <div class="edd_gift_template_tags">
        <p>
            <strong>Note:</strong>Enter the text that is sent as gift receipt email to users after completion of a successful purchase. HTML is accepted.
        </p>
        <h4>
            Available template tags:
        </h4>

        <ul>
            <li>{gift_recipient_name} - The gift recipient's name</li>
            <li>{gift_purchaser} - The buyer's name</li>
            <li>{your_gift_message} - The message that send to gift recipient</li>
            <li>{gift_time_limit} - The time limit of product(s) download</li>
        </ul>
    </div>
</div>