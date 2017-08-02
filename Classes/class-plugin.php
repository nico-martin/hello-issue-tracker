<?php

namespace AUTHOR_NAMESPACE\PLUGIN_NAMESPACE;

class Plugin {

	private static $instance;

	public $name = '';
	public $prefix = '';
	public $version = '';
	public $file = '';

	public static $option_key = 'PLUGIN_PREFIX_data';

	/**
	 * Creates an instance if one isn't already available,
	 * then return the current instance.
	 *
	 * @param  string $file The file from which the class is being instantiated.
	 *
	 * @return object       The class instance.
	 */
	public static function get_instance( $file ) {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Plugin ) ) {

			self::$instance = new Plugin;

			if ( get_option( awpp_get_instance()->option_key ) ) {
				$data = get_option( awpp_get_instance()->option_key );
			} else {
				$data = get_plugin_data( $file );
			}

			self::$instance->name    = $data['Name'];
			self::$instance->version = $data['Version'];

			self::$instance->prefix = 'awpp';
			self::$instance->debug  = true;
			self::$instance->file   = $file;

			self::$instance->run();
		}

		return self::$instance;
	}

	/**
	 * Non-essential dump function to debug variables.
	 *
	 * @param  mixed $var The variable to be output
	 * @param  boolean $die Should the script stop immediately after outputting $var?
	 */
	public function dump( $var, $die = false ) {
		echo '<pre>' . print_r( $var, 1 ) . '</pre>';
		if ( $die ) {
			die();
		}
	}

	/**
	 * Execution function which is called after the class has been initialized.
	 * This contains hook and filter assignments, etc.
	 */
	private function run() {
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
		add_action( 'admin_init', array( $this, 'update_plugin_data' ) );
		register_deactivation_hook( PLUGIN_PREFIX_get_instance()->file, array( $this, 'deactivate' ) );
	}

	/**
	 * Load translation files from the indicated directory.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'TEXT_DOMAIN', false, dirname( plugin_basename( PLUGIN_PREFIX_get_instance()->file ) ) . '/languages' );
	}

	/**
	 * Update Plugin Data
	 */
	public function update_plugin_data() {

		$db_data   = get_option( self::$option_key );
		$file_data = get_plugin_data( self::$instance->file );

		if ( ! $db_data || version_compare( $file_data['Version'], $db_data['Version'], '>' ) ) {

			$new_option = array(
				'Version' => $file_data['Version'],
				'Name'    => $file_data['Name'],
			);

			self::$instance->name    = $new_option['Name'];
			self::$instance->version = $new_option['Version'];

			update_option( self::$option_key, $new_option );

			if ( ! $db_data ) {
				do_action( 'PLUGIN_PREFIX_on_activate' );
			} else {
				do_action( 'PLUGIN_PREFIX_on_update', $db_data['Version'], $file_data['Version'] );
			}
		}
	}

	public function update_plugin_data() {

		$db_data   = get_option( PLUGIN_PREFIX_get_instance()->option_key );
		$file_data = get_plugin_data( PLUGIN_PREFIX_get_instance()->file );

		if ( ! $db_data || version_compare( $file_data['Version'], $db_data['Version'], '>' ) ) {

			PLUGIN_PREFIX_get_instance()->name    = $file_data['Name'];
			PLUGIN_PREFIX_get_instance()->version = $file_data['Version'];

			update_option( PLUGIN_PREFIX_get_instance()->option_key, $file_data );

			if ( ! $db_data ) {
				do_action( 'PLUGIN_PREFIX_on_activate' );
			} else {
				do_action( 'PLUGIN_PREFIX_on_update', $db_data['Version'], $file_data['Version'] );
			}
		}
	}

	public function deactivate() {
		delete_option( PLUGIN_PREFIX_get_instance()->option_key );
	}
}
