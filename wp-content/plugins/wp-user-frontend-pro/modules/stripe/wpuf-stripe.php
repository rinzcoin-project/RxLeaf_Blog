<?php
/**
 * Plugin Name: Stripe Payment
 * Description: Stripe payment gateway for WP User Frontend
 * Plugin URI: https://wedevs.com/products/plugins/wp-user-frontend-pro/stripe/
 * Thumbnail Name: wpuf-stripe.png
 * Author: weDevs
 * Author URI: http://wedevs.com/
 * Version: 0.1
 * License: GPL2
 * Text Domain: wpuf-stripe
 * Domain Path: languages
 *
 * Copyright (c) 2017 weDevs (email: info@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WPUF_Stripe {
    /**
     * Class constructor.
     */
    public function __construct() {
        // load the addon
        $this->plugin_init();
    }

    /**
     * Initialize the class.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Init the plugin.
     *
     * @return void
     */
    public function plugin_init() {
        include dirname( __FILE__ ) . '/includes/stripe.php';

        // Define constants
        $this->define_constants();

        // Initialize the action hooks
        $this->init_actions();

        // Initialize the filter hooks
        $this->init_filters();
    }

    /**
     * Define the plugin constants.
     *
     * @return void
     */
    private function define_constants() {
        define( 'WPUF_STRIPE_FILE', __FILE__ );
        define( 'WPUF_STRIPE_PATH', dirname( WPUF_STRIPE_FILE ) );
        define( 'WPUF_STRIPE_INCLUDES', WPUF_STRIPE_PATH . '/includes' );
        define( 'WPUF_STRIPE_URL', plugins_url( '', WPUF_STRIPE_FILE ) );
        define( 'WPUF_STRIPE_ASSETS', WPUF_STRIPE_URL . '/assets' );
    }

    /**
     * Init the plugin actions.
     *
     * @return void
     */
    private function init_actions() {
        add_action( 'wp_footer', array( $this, 'footer_scripts' ) );
    }

    /**
     * Init the plugin filters.
     *
     * @return void
     */
    private function init_filters() {
        add_filter( 'wpuf_payment_gateways', array( $this, 'filter_add_gateways' ) );
    }

    /**
     * Filter the gateways
     *
     * @param  array $gateways
     *
     * @return array
     */
    public function filter_add_gateways( $gateways ) {
        $gateways['stripe'] = array(
            'admin_label'    => __( 'Credit Card', 'wpuf-pro' ),
            'checkout_label' => __( 'Credit Card', 'wpuf-pro' ),
            'icon'           => apply_filters( 'wpuf_stripe_checkout_icon', WPUF_STRIPE_ASSETS . '/images/cards.png' )
        );

        return $gateways;
    }

    /**
     * Include JavaScript codes into footer
     *
     * @return void
     */
    public function footer_scripts() {

        if ( ! isset( $_GET['action'] ) || $_GET['action'] != 'wpuf_pay' ) {
            return;
        }

        $stripe_api_key = wpuf_get_option('stripe_api_key', 'wpuf_payment');
        ?>

        <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
        <script>
            Stripe.setPublishableKey('<?php echo $stripe_api_key; ?>');
            jQuery(function($) {
                $('#wpuf-payment-gateway').submit(function(event) {
                    var $form = $(this);
                    $form.find('.payment-errors').hide();

                    if ( $form.find( "input[name='wpuf_payment_method']:checked" ).val() != 'stripe' ) {
                        return true;
                    }

                    if ( $form.find( "input#number" ).val() == '' ) {
                        $form.find('.payment-errors').show();
                        $form.find('.payment-errors').text('<?php _e( 'Credit card number is required.', 'wpuf-pro' ); ?>');

                        return false;
                    }

                    if ( $form.find( "input#expiry" ).val() == '' ) {
                        $form.find('.payment-errors').show();
                        $form.find('.payment-errors').text( '<?php _e( 'Expiry date is required.', 'wpuf-pro' ); ?>' );

                        return false;
                    }

                    if ( $form.find( "input#cvc" ).val() == '' ) {
                        $form.find('.payment-errors').show();
                        $form.find('.payment-errors').text( '<?php _e( 'CVC is required.', 'wpuf-pro' ); ?>' );

                        return false;
                    }

                    // Disable the submit button to prevent repeated clicks
                    $form.find("input[type='submit']").prop('disabled', true);

                    Stripe.card.createToken($form, stripeResponseHandler);
                    // Prevent the form from submitting with the default action
                    return false;
                });
            });

            var stripeResponseHandler = function(status, response) {
                var $form = jQuery('#wpuf-payment-gateway');
                if (response.error) {
                    // Show the errors on the form
                    $form.find('.payment-errors').show();
                    $form.find('.payment-errors').text(response.error.message);
                    $form.find("input[type='submit']").prop('disabled', false);
                } else {
                    $form.find('.payment-errors').hide();
                    // token contains id, last4, and card type
                    var token = response.id;
                    // Insert the token into the form so it gets submitted to the server
                    $form.append(jQuery('<input type="hidden" name="stripeToken" />').val(token));
                    // and submit
                    $form.get(0).submit();
                }
            };
        </script>

        <script type="text/javascript" src="<?php echo WPUF_STRIPE_ASSETS . '/js/card.js'; ?>"></script>
        <script>

        // Split single expiration field into month/year
        jQuery( "form#wpuf-payment-gateway input#expiry" ).keyup(function() {
            var expiration = jQuery(this).val();
            var arr = expiration.split('/');
            var month = jQuery.trim(arr[0]);
            var year = jQuery.trim(arr[1]);

            jQuery( "input#month" ).val(month);
            jQuery( "input#year" ).val(year);
        });

        var card = new Card({
            // a selector or DOM element for the form where users will
            // be entering their information
            form: '#wpuf-payment-gateway', // *required*
            // a selector or DOM element for the container
            // where you want the card to appear
            container: '.card-wrapper', // *required*

            formSelectors: {
                numberInput: 'input#number', // optional — default input[name="number"]
                expiryInput: 'input#expiry', // optional — default input[name="expiry"]
                cvcInput: 'input#cvc', // optional — default input[name="cvc"]
                nameInput: 'input#name' // optional - defaults input[name="name"]
            },

            width: 350, // optional — default 350px
            formatting: true, // optional - default true

            // if true, will log helpful messages for setting up Card
            debug: false // optional - default false
        });
        </script>

        <?php
    }

}

WPUF_Stripe::init();
