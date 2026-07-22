<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://jollity.io/about-us
 * @since      1.0.0
 *
 * @package    Event_Client
 * @subpackage Event_Client/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Event_Client
 * @subpackage Event_Client/public
 * @author     Darick <darick.q@jollity.io>
 */
class Event_Client_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->event_client_functionalities();
	}


	/**
	 * Register Event Client functionalities
	 *
	 * @since    1.0.0
	 */	

	 public function event_client_functionalities() { 
	        // Search functionality above the header section

			// Create Event
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/event-client-create-events.php';	
		 	// Register ACF Fields for Events
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/event-client-acf-fields.php';
		 	// Custom Event Place Holder
		 	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/event-client-calendar-template.php';
		 
		 	//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/event-client-get-events.php';
		 	//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/event-client-shortcodes.php';
		 	//require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/event-client-helper-functions.php';
	 }

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Event_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Event_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/event-client-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Event_Client_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Event_Client_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/event-client-public.js', array( 'jquery' ), $this->version, false );

	}

}