<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://compte.stan-app.fr
 * @since      1.0.0
 *
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 */


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WC_Stan_Payment_Gateway
 * @subpackage WC_Stan_Payment_Gateway/includes
 * @author     Brightweb <jonathan@brightweb.cloud>
 */
class WC_Stan_Payment_Gateway extends WC_Payment_Gateway {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WC_Stan_Payment_Gateway_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**
	 * The account informations
	 *
	 * @since    2.1.0
	 * @access   private
	 * @var      object    $account    The account informations
	 */
    protected $account_infos;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WC_Stan_Payment_Gateway_VERSION' ) ) {
			$this->version = WC_Stan_Payment_Gateway_VERSION;
		} else {
			$this->version = '1.0.0';
        }
        
		$this->id = 'wc_stan_payment_gateway';
		$this->icon = '';
		$this->has_fields = false;
		$this->plugin_name = 'woo-stan-payment-gateway';
		$this->method_title = 'Paiement sans carte avec Stan';
		$this->method_description = 'Extension de Stan Paiement sans carte pour WooCommerce';

		$this->supports = array(
			'products'
		);

        $this->load_dependencies();
        $this->init_form_fields();
        $this->init_settings();

		$this->title = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
        $this->enabled = $this->get_option( 'enabled' );
        $this->testmode = 'yes' === $this->get_option( 'testmode' );
		$this->stan_connect = 'yes' === $this->get_option( 'stan_connect' );
        $this->only_stanners = 'yes' === $this->get_option( 'only_stanners' );
		$this->secret_key = $this->testmode ? $this->get_option( 'test_secret_key' ) : $this->get_option( 'secret_key' );
        $this->client_id = $this->testmode ? $this->get_option( 'test_client_id' ) : $this->get_option( 'client_id' );
        
        WC_Stan_Payment_Gateway_API::set_client_id( $this->client_id );
		WC_Stan_Payment_Gateway_API::set_secret_key( $this->secret_key );

        $this->update_option( 'stan_api_url', WC_Stan_Payment_Gateway_API::GetAPIURL() );
        $this->update_option( 'stan_api_auth_url', WC_Stan_Payment_Gateway_API::GetAPIURL() );

        if ( $this->stan_connect ) {
            if ( ! function_exists( 'is_plugin_active' ) ) {
                include_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if ( is_plugin_active( 'stan-easy-connect/stan-easy-connect.php' ) || is_plugin_active( 'wp-stan-easy-connect/stan-easy-connect.php' ) ) {
                if ( ! has_action( 'woocommerce_login_form' ) ) {
                    add_action( 'woocommerce_login_form', array( $this, 'display_stan_connect_in_login' ) );
                }
                
                if ( ! has_action( 'woocommerce_before_checkout_billing_form' ) ) {
                    add_action( 'woocommerce_before_checkout_billing_form', array( $this, 'display_stan_connect_in_checkout' ) );
                }
            } else {
                WC_Admin_Settings::add_message( "Vous voulez activer Stan Connect, c'est une très bonne idée ! Cependant vous n'avez pas encore installé Stan Easy Connect sur votre site, installez le dès maintenant en tapant 'Stan easy connect' dans la recherche d'extensions" );
            }
        }

		add_action( 'woocommerce_api_wc_stan_payment', array( $this, 'callback_order' ) );

        add_filter( 'woocommerce_gateway_title', [ $this, 'filter_gateway_title' ], 10, 2 );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'filter_stan_payment' ], 10, 2 );
        
		$this->set_locale();
		$this->define_admin_hooks();
        $this->define_public_hooks();
	}

	/**
	 * Init plugin options
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function init_form_fields() {
        require_once plugin_dir_path( __DIR__ ) . 'admin/settings.php';

        $this->form_fields = get_stan_settings();
        
		$stan_connect_settings_desc = $this->form_fields[ 'stan_connect' ][ 'description' ];
		if ( function_exists('get_stan_connect_settings_link') ) {
			$stan_connect_settings_desc .= "<p><a href=" . get_stan_connect_settings_link() . " target=_blank>Configurer Stan Easy Connect</a></p>";
		} else {
			$install_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'install-plugin',
						'plugin' => 'stan-easy-connect'
					),
					admin_url( 'update.php' )
				),
				'install-plugin_stan-easy-connect'
			);
			$stan_connect_settings_desc .= "<p>👉 <b>Vous devez installer Stan Easy Connect pour activer cette option.</b> <a href=" . $install_url . " target=_blank>Installer Stan Easy Connect</a></p>";
		}
		$this->form_fields[ 'stan_connect' ][ 'description' ] = $stan_connect_settings_desc;
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WC_Stan_Payment_Gateway_Loader. Orchestrates the hooks of the plugin.
	 * - WC_Stan_Payment_Gateway_i18n. Defines internationalization functionality.
	 * - WC_Stan_Payment_Gateway_API. Communicates with Stan API.
	 * - WC_Stan_Payment_Gateway_Logger. Log anything that go through woo-stan-payment-gateway.
	 * - WC_Stan_Payment_Gateway_Admin. Defines all hooks for the admin area.
	 * - WC_Stan_Payment_Gateway_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-stan-payment-gateway-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-stan-payment-gateway-i18n.php';


        /**
		 * The class for helpers
		 */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-stan-payment-gateway-helper.php';

        /**
		 * The class reponsible for exceptions.
		 */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-stan-payment-gateway-exception.php';

		/**
		 * The class responsible for making requests to Stan API.
		 */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-stan-payment-gateway-api.php';
        
        /**
		 * The class responsible for making requests to Stan API.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-stan-payment-gateway-api-wrapper.php';

		/**
		 * The class responsible for logs.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wc-stan-payment-gateway-logger.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wc-stan-payment-gateway-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wc-stan-payment-gateway-public.php';


		$this->loader = new WC_Stan_Payment_Gateway_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WC_Stan_Payment_Gateway_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WC_Stan_Payment_Gateway_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

    /**
	 * Checks account infos.
	 *
	 * @since    2.1.0
	 * @access   private
	 */
    private function check_account_infos() {
        if ( ! empty( $this->client_id ) && ! empty( $this->secret_key ) ) {
            $client = new WC_Stan_Payment_Gateway_API_Wrapper( $this->testmode );

            try {
                $this->account_infos = $client->get_account_infos();
    
                if ( ! $this->account_infos->iban || empty( $this->account_infos->iban ) ) {
                    WC_Admin_Settings::add_error( 
                        "Bientôt fini, votre IBAN pour recevoir les paiements de vos clients n'a pas encore été renseigné. Accédez à votre Compte Stan pour renseigner vos informations"
                    );
                } else if ( 
                    ( isset( $this->account_infos->is_active ) && ! $this->account_infos->is_active )
                    || ! isset( $this->account_infos->is_active )
                ) {
                    WC_Admin_Settings::add_error( 
                        "Bientôt prêt, votre compte n'a pas encore été validé. Pensez à le mettre à jour en accédant à votre Compte Stan si ce n'est pas déjà fait"
                    );
                }
            } catch ( \WC_Stan_Payment_Gateway_Exception $e) {
                // do nothing
            }
        }
    }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WC_Stan_Payment_Gateway_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        
        add_filter( 'woocommerce_order_actions', array( $this, 'add_refresh_order_status_action' ) );
        add_action( 'woocommerce_order_action_wc_check_payment_and_update_order', array( $this, 'wc_check_payment_and_update_order' ) );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WC_Stan_Payment_Gateway_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

    /**
     * Filter for the payment title
     * It update the payment title in case the plugin is in test mode
     * 
     * @since 1.3.0
     */
    public function filter_gateway_title( $title, $id ) {
        if ($this->testmode && $id === 'wc_stan_payment_gateway') {
            return '(MODE TEST) Tester Paiement avec Stan';
        }
        return $title;
    }

    /**
     * Filter the Stan Pay method
     * 
     * @since 2.6.0
     */
    public function filter_stan_payment( $available_gateways ) {
        $user_agent = $_SERVER[ "HTTP_USER_AGENT" ];

		if ( !function_exists('str_contains') )
		{
			function str_contains( $haystack, $needle )
			{
				return empty($needle) || strpos($haystack, $needle) !== false;
			}
		}

        if ( $this->only_stanners && ! str_contains( $user_agent, "StanApp" ) ) {
            unset( $available_gateways[ "wc_stan_payment_gateway" ] );
        }

        return $available_gateways;
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
    }

	/**
	 * Validates that the order meets the minimum order amount for Stan
	 *
	 * @since 2.0.0
	 * @param object $order
	 */
	public function validate_minimum_order_amount( $order ) {
        if ( $order->get_total() * 100 < WC_Stan_Payment_Gateway_Helper::get_minimum_amount() ) {
            throw new WC_Stan_Payment_Gateway_Exception( 'Did not meet minimum amount', sprintf( __( 'Le montant minimum pour payer avec Stan est de %s', 'woo-stan-payment-gateway' ), wc_price( WC_Stan_Payment_Gateway_Helper::get_minimum_amount() / 100 ) ) );
		}
	}

    /**
	 * Validates that the order meets the minimum order amount for Stan
	 *
	 * @since 2.0.0
	 * @param object $order
	 */
	public function validate_maximum_order_amount( $order ) {
		if ( WC_Stan_Payment_Gateway_Helper::get_maximum_amount() > 0 && $order->get_total() * 100 > WC_Stan_Payment_Gateway_Helper::get_maximum_amount() ) {
			throw new WC_Stan_Payment_Gateway_Exception( 'Did not meet maximum amount', sprintf( __( 'Le montant maximum pour payer avec Stan est de %s', 'woo-stan-payment-gateway' ), wc_price( WC_Stan_Payment_Gateway_Helper::get_maximum_amount() / 100 ) ) );
		}
	}
    
    /**
	 * Process payments.
	 *
	 * @param int $order_id Order ID
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		if ( ! is_ssl() ) {
            if ( ! $this->testmode && (function_exists( "wp_get_environment_type" ) && wp_get_environment_type() != "local" ) ) {
                WC_Stan_Payment_Gateway_Logger::log( 'During payment in checkout. Your website must be in https (using SSL)' );
				wc_add_notice( "Le site nécessite d'être en HTTPS pour votre sécurité", 'error' );
				return;
			}
			wc_add_notice( 'Votre site doit être en HTTPS (SSL) pour la sécurité de vos clients. Vous pouvez continuer à tester en MODE TEST, cette erreur empêchera de faire des paiements avec Stan lorsque vous désactiverez le MODE TEST.', 'notice' );
        }

		$order = wc_get_order( $order_id );

        $client = new WC_Stan_Payment_Gateway_API_Wrapper( $this->testmode );

		try {
            // Those 2 will throw exception if not valid.
			$this->validate_minimum_order_amount( $order );
			$this->validate_maximum_order_amount( $order );

            // TODO should be an option to add customer payment
            // Not required in testmode
            $customer_id = null;
            if ( ! $this->testmode ) {
                $customer = $client->create_customer( $order );
                $customer_id = $customer->id;
            }

            $amount = WC_Stan_Payment_Gateway_Helper::format_amount( $order->get_total() );

            $payment = $client->create_payment(
                strval( $order_id ),
                $amount,
                get_site_url() . '/?wc-api=WC_Stan_Payment',
                $customer_id
            );

            $order->set_transaction_id( $payment->payment_id );
            
            $order->set_status( 'wc-pending' );

			$payload = array(
                'subtotal_amount' => $order->get_subtotal(),
				'total_amount' => $order->get_total(),
				'shipping_amount' => $order->get_shipping_total(),
				'discount_amount' => $order->get_discount_total(),
				'vat_amount' => $order->get_total_tax(),
                'payment_id' => $payment->payment_id
			);

            $q = http_build_query( $payload );

			$order->add_meta_data( "wc_stan_payment_id", $payment->payment_id );

            $order->save();

			return array(
				'result' => 'success',
				'redirect' => $payment->redirect_url . '&' . $q
			);
		} catch (\WC_Stan_Payment_Gateway_Exception $e) {
            wc_add_notice( $e->getLocalizedMessage(), 'error' );
			WC_Stan_Payment_Gateway_Logger::log( 'Error: ' . $e->getMessage() );

			return array(
				'result' => 'fail',
				'redirect' => '',
			);
		}
    }

    /**
	 * Webhook after the payment has been processed by Stan
	 *
     * @since     1.0.0
	 *
	 * @return array
	 */
	public function callback_order() {
        $payment_id = sanitize_text_field( $_GET['payment_id'] );

        $client = new WC_Stan_Payment_Gateway_API_Wrapper( $this->testmode );
        
        try {
            $payment = $client->get_payment( $payment_id );

            $order = wc_get_order( intval( $payment->order_id ) );

            if ( !$order ) {
                WC_Stan_Payment_Gateway_Logger::log( 'Order ' . $payment->order_id . ' not found during callback order' );
                return;
            }

            $this->wc_check_payment_and_update_order( $order, $payment );

            switch ($payment->payment_status) {
                case 'payment_success':
                case 'payment_pending':
                    wp_redirect( $this->get_return_url( $order ) );
                    break;
                case 'payment_auth_required':
                case 'payment_expired':
                case 'payment_cancelled':
                case 'payment_prepared':
                default:
                    wp_redirect( $order->get_checkout_payment_url( true ) );
            }

        } catch (\Exception $e) {
            wp_redirect( wc_get_cart_url() );
            WC_Stan_Payment_Gateway_Logger::log( 'Error raised during callback order, reason: ' . "\n" . $e);
        }
    }

    /**
	 * Processes new settings
	 *
     * @since     1.0.0
	 *
	 * @return array
	 */
    public function process_admin_options() {
        static $saved = false;

        if ( ! $saved ) {
            parent::process_admin_options();

            $saved = true;
            
            if ( $this->get_option( 'testmode' ) === 'yes' ) {
                return $saved;
            }
    
            $client = new WC_Stan_Payment_Gateway_API_Wrapper();

            $this->check_account_infos();
    
            try {
                WC_Stan_Payment_Gateway_API::set_client_id( $this->get_option( 'client_id' ) );
                WC_Stan_Payment_Gateway_API::set_secret_key( $this->get_option( 'secret_key' ) );
        
                $client->update_account_client();
    
                $account_infos_payload = array();
                
                $merchant_name = html_entity_decode( get_bloginfo( 'name' ), ENT_QUOTES );
    
                if ( $this->account_infos && ( $this->account_infos->address && ! $this->account_infos->address->street_address ) ) {
                    $address = array(
                        'firstname' => $merchant_name,
                        'lastname' => $merchant_name,
                        'street_address' => WC()->countries->get_base_address(),
                        'street_address_line2' => WC()->countries->get_base_address_2(),
                        'locality' => WC()->countries->get_base_city(),
                        'zip_code' => WC()->countries->get_base_postcode(),
                        'country' => WC()->countries->get_base_country()
                    );
    
                    $account_infos_payload[ 'address' ] = $address;
                }
    
                if ( $this->account_infos && ! $this->account_infos->name && empty( $merchant_name ) ) {
                    $account_infos_payload[ 'name' ] = $merchant_name;
                }

                if ( $this->account_infos && ! $this->account_infos->website && empty( get_site_url() ) ) {
                    $account_infos_payload[ 'website' ] = get_site_url();
                }
    
                if ( $this->account_infos && ! $this->account_infos->favicon_url ) {
                    $favicon_url = get_site_icon_url( 48 );
                    if ( empty( $favicon_url ) ) {
                        $account_infos_payload[ 'favicon_url' ] = $favicon_url;
                    }
                }

                if ( $this->account_infos && ! $this->account_infos->website_description ) {
                    $desc = html_entity_decode( get_bloginfo( 'description' ), ENT_QUOTES );
                    if ( empty( $desc ) ) {
                        $account_infos_payload[ 'website_description' ] = $desc;
                    }
                }
    
                // Update some trivial informations for better experience
                // if some are missing
                if ( sizeof( $account_infos_payload ) > 0 ) {
                    $client->update_account_infos( $account_infos_payload );
                }

                return $saved;
            } catch (\Exception $e) {
                WC_Admin_Settings::add_message( "Votre configuration a été enregistrée. Pour mettre automatiquement à jour votre compte Stan vérifiez que vos identifiants LIVE sont bien configurés et testez vos identifiants avec le bouton « Tester mes identifiants Stan »." );
                WC_Stan_Payment_Gateway_Logger::log( 'Error raised when saving Stan settings, reason: ' . "\n" . $e);
                return $saved;
            }
        }
    }

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WC_Stan_Payment_Gateway_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Display Stan connect in Woocommerce login page.
	 *
	 * @return array
	 */
	public function display_stan_connect_in_login() {
        echo do_shortcode('[stan_easy_connect_button]');
	}

	/**
	 * Display Stan connect in Woocommerce checkout page.
	 *
	 * @return array
	 */
	public function display_stan_connect_in_checkout() {
		if ( is_user_logged_in() ) {
			return;
		}
		echo do_shortcode('[stan_easy_connect_button]');
		echo '<h4 class="stan-connect-checkout-text">— Ou tout compléter manuellement —</h4>';
		echo '<br />';
    }
    
    public function add_refresh_order_status_action( $actions ) {
        $actions[ 'wc_check_payment_and_update_order' ] = 'Vérifier paiement Stan';
        
        return $actions;
    }

    public function wc_check_payment_and_update_order( $order, $payment = null ) {
        try {
            if ( is_null( $payment ) ) {
                $client = new WC_Stan_Payment_Gateway_API_Wrapper( $this->testmode );
                $payment = $client->get_payment( $order->get_transaction_id() );
            }

            switch ($payment->payment_status) {
                case 'payment_success':
                    $order->payment_complete( $payment_id );
                    WC_Stan_Payment_Gateway_Logger::log( 'Order ' . $payment->order_id . ' is complete!' );
                    break;
                case 'payment_pending':
                    $order->set_status( 'wc-on-hold', 'Payment is being processed by the bank.' );
                    WC_Stan_Payment_Gateway_Logger::log( 'Order ' . $payment->order_id . ' is still pending' );
                    break;
                case 'payment_auth_required':
                case 'payment_prepared':
                    $order->set_status( 'wc-pending' );
                    WC_Stan_Payment_Gateway_Logger::log( 'Order ' . $order->id . ' require an authentication during payment.');
                    break;
                case 'payment_expired':
                case 'payment_cancelled':
                    $order->set_status( 'wc-cancelled' );
                    WC_Stan_Payment_Gateway_Logger::log( 'Order ' . $order->id . ' payment has been cancelled.');
                    break;
                default:
                    $order->set_status( 'wc-failed' );
                    WC_Stan_Payment_Gateway_Logger::log( 'Order ' . $order->id . ' payment failed. User got redirected to order checkout for retry.');
            }

            $order->save();
        } catch (\Exception $e) {
            WC_Stan_Payment_Gateway_Logger::log( 'Error raised during order check order_id = ' . $order->id . ', reason: ' . "\n" . $e);
        }
    }

    public function get_icon() {
        $icon = '<img src="' . WC_STAN_PAYMENT_PLUGIN_URL . '/public/images/stan-pay.png" id="stan-pay" class="icon" alt="Stan payment icon" />';
        return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
    }
}
