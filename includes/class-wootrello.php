<?php

/**
 * The core plugin class.
 * This is used to define internationalization, admin-specific hooks, and public-facing site hooks.
 * Also maintains the unique identifier of this plugin as well as the current version of the plugin.
 * @since      1.0.0
 * @package    Wootrello
 * @subpackage Wootrello/includes
 * @author     javmah <jaedmah@gmail.com>
 */
class Wootrello {
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Wootrello_Loader    $loader    Maintains and registers all hooks for the plugin.
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
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if ( defined( 'WOOTRELLO_VERSION' ) ) {
            $this->version = WOOTRELLO_VERSION;
        } else {
            $this->version = '3.2.5';
        }
        //
        $this->plugin_name = 'wootrello';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Wootrello_Loader. Orchestrates the hooks of the plugin.
     * - Wootrello_i18n. Defines internationalization functionality.
     * - Wootrello_Admin. Defines all hooks for the admin area.
     * - Wootrello_Public. Defines all hooks for the public side of the site.
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
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wootrello-loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wootrello-i18n.php';
        /**
         * The class responsible for Wootrello  admin Settings.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wootrello-settings.php';
        /**
         * The class responsible for Wootrello  admin hooks.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wootrello-hook.php';
        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wootrello-public.php';
        #
        $this->loader = new Wootrello_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     * Uses the Wootrello_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        #
        $plugin_i18n = new Wootrello_i18n();
        #
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $Wootrello_Settings = new Wootrello_Settings($this->get_plugin_name(), $this->get_version());
        # creating custom post type on wpgsiIntegration
        $this->loader->add_action( 'init', $Wootrello_Settings, 'wootrello_CustomPostType' );
        # For CSS
        $this->loader->add_action( 'admin_enqueue_scripts', $Wootrello_Settings, 'settings_enqueue_styles' );
        #js
        $this->loader->add_action( 'admin_enqueue_scripts', $Wootrello_Settings, 'settings_enqueue_scripts' );
        # Admin menu and Admin Notice
        $this->loader->add_action( 'admin_menu', $Wootrello_Settings, 'Wootrello_menu_pages' );
        $this->loader->add_action( 'admin_notices', $Wootrello_Settings, 'wootrello_settings_notice' );
        # AJAX Calls for WooTrello Settings Page
        $this->loader->add_action( 'wp_ajax_wootrello_ajax_response', $Wootrello_Settings, 'wootrello_ajax' );
        # AJAX for wooTrello Delete single order Trello card History
        $this->loader->add_action( 'wp_ajax_wootrello_ajax_delete_history', $Wootrello_Settings, 'wootrello_ajax_delete_history' );
        # WooCommerce Orders List Column for letting the User about trello card Status
        $this->loader->add_action( 'manage_edit-shop_order_columns', $Wootrello_Settings, 'wootrello_card_status' );
        # trello card Status callback Function
        $this->loader->add_action(
            'manage_shop_order_posts_custom_column',
            $Wootrello_Settings,
            'wootrello_card_status_callback',
            10,
            2
        );
        # New Code Starts
        $this->loader->add_action( 'manage_woocommerce_page_wc-orders_columns', $Wootrello_Settings, 'wootrello_card_status' );
        $this->loader->add_action(
            'manage_woocommerce_page_wc-orders_custom_column',
            $Wootrello_Settings,
            'wootrello_card_status_callback',
            10,
            2
        );
        # Adding Custom WooTrello Meta Box
        $this->loader->add_action(
            'add_meta_boxes',
            $Wootrello_Settings,
            'wootrello_adding_custom_meta_boxes',
            10,
            2
        );
        ###
        $Wootrello_Hooks = new Wootrello_Hooks($this->get_plugin_name(), $this->get_version());
        # Woocommerce new checkout page order # WooCommerce New Order
        $this->loader->add_action(
            'woocommerce_thankyou',
            $Wootrello_Hooks,
            'wootrello_woocommerce_new_order_checkout',
            100,
            1
        );
        $this->loader->add_action( 'admin_notices', $Wootrello_Hooks, 'wootrello_hooks_notice' );
        # AJAX for wooTrello single order Page
        $this->loader->add_action( 'wp_ajax_wootrello_ajax_single_order', $Wootrello_Hooks, 'wootrello_ajax_single_order' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality of the plugin.
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Wootrello_Public($this->get_plugin_name(), $this->get_version());
        # For CSS
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        # js
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
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
     * @since     1.0.0
     * @return    Wootrello_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}
