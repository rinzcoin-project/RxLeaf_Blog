<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'Stripe' ) ) {
    require_once( dirname( __FILE__ ) . '/../lib/stripe/init.php' );
}

/**
 * WP User Frontend Stripe gateway
 *
 * @since 0.1
 */
class WPUF_Gateway_Stripe {

    public function __construct() {
        add_action( 'wpuf_options_payment', array( $this, 'payment_options' ) );
        add_action( 'wpuf_gateway_form_stripe', array( $this, 'gateway_form' ), 10, 3 );
        add_action( 'wpuf_gateway_stripe', array( $this, 'prepare_to_send' ) );
        add_action( 'wpuf_cancel_subscription_stripe', array( $this, 'handle_cancel_subscription' ) );
    }

    /**
     * Adds stripe specific options to the admin panel
     *
     * @since  0.1
     *
     * @param  array $options
     *
     * @return string
     */
    public function payment_options( $options ) {
        $options[] = array(
            'name'    => 'gate_instruct_stripe',
            'label'   => __( 'Stripe Instruction', 'wpuf-pro' ),
            'type'    => 'textarea',
            'default' => __( 'Enter your credit card information in order to proceed the payment.', 'wpuf-pro' )
        );

        $options[] = array(
            'name'    => 'use_legacy_stripe_api',
            'label'   => __( 'Enable Legacy Stripe API', 'wpuf-pro' ),
            'type'    => 'checkbox',
            'default' => 'on',
            'desc'    => __( 'Check if you want to use legacy Stripe API.', 'wpuf-pro' )
        );

        $options[] = array(
            'name'  => 'stripe_api_secret',
            'label' => __( 'Stripe Secret Key', 'wpuf-pro' )
        );

        $options[] = array(
            'name'  => 'stripe_api_key',
            'label' => __( 'Stripe Publishable Key', 'wpuf-pro' )
        );

        return $options;
    }

    /**
     * Display the credit card form
     *
     * @since  0.1
     *
     * @param  string $type
     * @param  int    $post_id
     * @param  int    $pack_id
     *
     * @return void
     */
    public function gateway_form( $type, $post_id, $pack_id ) {
        ?>
            <div class="card-wrapper" style="display: table;"></div>
            <br />

            <p class="payment-errors wpuf-error" style="display: none;"></p>

            <ul class="wpuf-form">
                <li class="wpuf-el" style="padding: 5px;">
                    <div class="wpuf-fields">
                        <input class="textfield" type="text" id="number" data-stripe="number" placeholder="<?php _e( 'Credit Card Number', 'wpuf-pro' ); ?>">
                    </div>
                </li>
                <li class="wpuf-el" style="padding: 5px;">
                    <div class="wpuf-fields">
                        <input class="textfield" type="text" id="expiry" placeholder="<?php _e( 'MM / YYYY', 'wpuf-pro' ); ?>">
                        <input class="textfield" type="hidden" id="month" data-stripe="exp-month">
                        <input class="textfield" type="hidden" id="year" data-stripe="exp-year">
                    </div>
                </li>
                <li class="wpuf-el" style="padding: 5px;">
                    <div class="wpuf-fields">
                        <input class="textfield" type="text" id="cvc" data-stripe="cvc" placeholder="<?php _e( 'CVC', 'wpuf-pro' ); ?>">
                    </div>
                </li>
                <li class="wpuf-el" style="padding: 5px;">
                    <div class="wpuf-fields">
                        <input class="textfield" type="text" id="name" data-stripe="name" placeholder="<?php _e( 'Name on the Card', 'wpuf-pro' ); ?>">
                    </div>
                </li>
            </ul>
        <?php
    }

    /**
     * Process the payment form with stripe
     *
     * @since  0.1
     *
     * @param  array $data payment info
     *
     * @return void
     */
    public function prepare_to_send( $data ) {

        $user_id          = intval( $data['user_info']['id'] );
        $listener_url     = add_query_arg( 'action', 'wpuf_stripe_success', home_url( '/' ) );
        $redirect_page_id = wpuf_get_option( 'payment_success', 'wpuf_payment' );

        if ( $redirect_page_id ) {
            $return_url = add_query_arg( 'action', 'wpuf_stripe_success', get_permalink( $redirect_page_id ) );
        } else {
            $return_url = add_query_arg( 'action', 'wpuf_stripe_success', get_permalink( wpuf_get_option( 'subscription_page', 'wpuf_payment' ) ) );
        }

        $billing_amount = empty( $data['price'] ) ? 0 : number_format( $data['price'], 2 );

        if ( isset( $_POST['coupon_id'] ) && ! empty( $_POST['coupon_id'] ) ) {
            $billing_amount = WPUF_Coupons::init()->discount( $billing_amount, $_POST['coupon_id'], $data['item_number'] );
            $coupon_id = $_POST['coupon_id'];
        } else {
            $coupon_id = '';
        }

        $data['subtotal'] = floatval ( $billing_amount );
        $billing_amount   = apply_filters( 'wpuf_payment_amount', $data['subtotal'] );

        if ( function_exists( 'wpuf_current_tax_rate' ) ) {
            $tax_rate = wpuf_current_tax_rate();
        }

        if ( $billing_amount == 0 ) {
            WPUF_Subscription::init()->new_subscription( $user_id, $data['item_number'], $profile_id = null, false,'free' );
            wp_redirect( $return_url );
            exit();
        }

        $old_api = false;

        $post_data     = $data['post_data'];
        $stripe_token  = $post_data['stripeToken'];
        $stripe_amount = intval ( floatval( $billing_amount ) * 100 );
        $user_email    = $data['user_info']['email'];
        $stripe_api_secret = wpuf_get_option( 'stripe_api_secret', 'wpuf_payment' );

        \Stripe\Stripe::setApiKey( $stripe_api_secret );
        \Stripe\Stripe::setApiVersion("2018-02-28");

        if ( wpuf_get_option( 'use_legacy_stripe_api', 'wpuf_payment', 'on' ) == 'on' ) {
            $old_api = true;
            \Stripe\Stripe::setApiVersion("2015-01-26");
        }

        $new_plan = array();

        if ( $data['type'] == 'pack' && $data['custom']['recurring_pay'] == 'yes' ) {
            $is_recurring = true;

            $subscription_package_name = $data['custom']['post_title'];
            $subscription_package_id   = intval( $post_data['pack_id'] );

            try {
                $stripe_plan = \Stripe\Plan::retrieve( $subscription_package_id );

                if ( $stripe_plan->id == $subscription_package_id && !empty( $coupon_id ) ) {
                    $new_pack_id = $subscription_package_id . time();
                    $plan_data = array(
                        'amount'         => $stripe_amount,
                        'interval'       => $data['custom']['cycle_period'],
                        'interval_count' => intval( $data['custom']['billing_cycle_number'] ),
                        'product'        => array(
                                                'name' => $subscription_package_name,
                                            ),
                        'currency'       => $data['currency'],
                        'id'             => $new_pack_id,
                    );
                    $new_plan = \Stripe\Plan::create( $plan_data );
                }
            } catch ( Exception $e ) {
                $plan_data = array(
                    'amount'         => $stripe_amount,
                    'interval'       => $data['custom']['cycle_period'],
                    'interval_count' => intval( $data['custom']['billing_cycle_number'] ),
                    'product'        => array(
                                            'name' => $subscription_package_name,
                                        ),
                    'currency'       => $data['currency'],
                    'id'             => $subscription_package_id,
                );

                if ( $data['custom']['trial_status'] == 'yes' ) {
                    $trial_duration = intval( $data['custom']['trial_duration'] );

                    switch ( $trial_duration ) {
                        case 'day':
                            $plan_data['trial_period_days'] = $trial_duration;
                            break;

                        case 'week':
                            $plan_data['trial_period_days'] = $trial_duration * 7;
                            break;

                         case 'month':
                            $plan_data['trial_period_days'] = $trial_duration * 30;
                            break;

                        case 'year':
                            $plan_data['trial_period_days'] = $trial_duration * 365;
                            break;
                    }

                }

                $new_plan = \Stripe\Plan::create( $plan_data );
            }

            $stripe_customer_id = get_user_meta( $user_id, '_wpuf_stripe_customer_id', true );

            if ( ! empty( $stripe_customer_id ) ) {
                try {
                    if ( !empty( $coupon_id ) ) {
                        $subscription_package_id = $new_plan->id;
                    }

                    if ( $old_api ) {
                        $customer     = \Stripe\Customer::retrieve( $stripe_customer_id );
                        $subscription = $customer->subscriptions->create( array( 'plan' => $subscription_package_id, 'tax_percent' => $tax_rate, ) );
                    } else {
                        $subscription = \Stripe\Subscription::create( array(
                                                'customer'    => $stripe_customer_id,
                                                'items'       => [['plan' => $subscription_package_id]],
                                                'tax_percent' => $tax_rate,
                                            )
                                        );
                    }

                    $transaction_id       = $subscription->id;
                    $subscription_id      = $transaction_id;
                    $is_payment_completed = true;

                } catch ( Exception $e ) {
                    $is_payment_completed = false;

                    WP_User_Frontend::log( 'payment', $e->getMessage() );
                }
            } else {
                try {
                    if ( !empty( $coupon_id ) ) {
                        $subscription_package_id = $new_plan->id;
                    }

                    $customer = \Stripe\Customer::create( array(
                        'email'  => $user_email,
                        'source' => $stripe_token,
                        'plan'   => $subscription_package_id,
                    ) );

                    update_user_meta( $user_id, '_wpuf_stripe_customer_id', $customer->id );

                    $transaction_id       = $customer->subscriptions->data[0]->id;
                    $subscription_id      = $transaction_id;
                    $is_payment_completed = true;

                } catch ( Exception $e ) {
                    $is_payment_completed = false;

                    WP_User_Frontend::log( 'payment', $e->getMessage() );
                }
            }

        } else {
            $is_recurring = false;

            try {
                $charge = \Stripe\Charge::create( array( 'source' => $stripe_token, 'amount' => $stripe_amount, 'currency' => $data['currency'] ) );

                $transaction_id       = $charge->id;
                $is_payment_completed = true;
            } catch (Exception $e) {
                $is_payment_completed = false;
                $message              = $e->getMessage();

                WP_User_Frontend::log( 'payment', $message );
            }
        }

        $status = 'completed';

        switch ( $data['type'] ) {
            case 'post':
                $post_id = $data['item_number'];
                $pack_id = 0;
                break;

            case 'pack':
                $post_id = 0;
                $pack_id = $data['item_number'];
                break;
        }

        if ( $is_payment_completed ) {
            $first_name = isset( $data['user_info']['first_name'] ) ? $data['user_info']['first_name'] : '';
            $last_name = isset( $data['user_info']['last_name'] ) ? $data['user_info']['last_name'] : '';

            $payment_data = array (
                'user_id'          => $user_id,
                'status'           => $status,
                'subtotal'         => $data['subtotal'],
                'tax'              => $data['tax'],
                'cost'             => $billing_amount,
                'post_id'          => $post_id,
                'pack_id'          => $pack_id,
                'payment_type'     => 'Stripe',
                'transaction_id'   => $transaction_id,
                'created'          => current_time( 'mysql' ),
                'profile_id'       => isset( $subscription_id ) ? $subscription_id : null,
                'payer_first_name' => $first_name,
                'payer_last_name'  => $last_name,
                'payer_email'      => $user_email,
            );

            if ( wpuf_get_option( 'show_address', 'wpuf_address_options', false ) ) {
                $payment_data['payer_address'] = wpuf_get_user_address();
            }

            WP_User_Frontend::log( 'payment', 'inserting payment to database. ' . print_r( $payment_data, true ) );

            $transaction_id = wp_strip_all_tags( $transaction_id );
            WPUF_Payment::insert_payment( $payment_data, $transaction_id, $is_recurring );

            if ( $coupon_id ) {
                $pre_usage = get_post_meta( $coupon_id, '_coupon_used', true );
                $pre_usage = (empty( $pre_usage )) ? 0 : $pre_usage;
                $new_use   = $pre_usage + 1;

                update_post_meta( $coupon_id, '_coupon_used', $new_use );
            }

            delete_user_meta( $user_id, '_wpuf_user_active' );
            delete_user_meta( $user_id, '_wpuf_activation_key' );

        } else {
            WP_User_Frontend::log( 'payment', 'inserting payment failed.' );
        }

        wp_redirect( $return_url );
        exit();
    }

    /**
     * Handle the cancel subscription
     *
     * @return void
     *
     * @since  0.1
     */
    public function handle_cancel_subscription( $data ) {
        $user_id = get_current_user_id();

        $sub_info        = get_user_meta( $user_id, '_wpuf_subscription_pack', true );
        $subscription_id = $sub_info['profile_id'];

        // Cancel subscription from stripe end
        $stripe_api_secret = wpuf_get_option( 'stripe_api_secret', 'wpuf_payment' );

        \Stripe\Stripe::setApiKey( $stripe_api_secret );
        \Stripe\Stripe::setApiVersion("2018-02-28");

        $customer_id  = get_user_meta( $user_id, '_wpuf_stripe_customer_id', true );
        $customer     = \Stripe\Customer::retrieve( $customer_id );
        $subscription = \Stripe\Subscription::retrieve( $subscription_id );
        $subscription->cancel();

        $sub_meta = 'cancel';
        WPUF_Subscription::init()->update_user_subscription_meta( $user_id, $sub_meta );
    }

}

new WPUF_Gateway_Stripe();
