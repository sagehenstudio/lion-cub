<?php if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { // If uninstall not called from WordPress exit
	exit;
}

/**
 * Manages Lion Cub uninstallation
 * The goal is to remove ALL Lion Cub-related data in db
 *
 */
class lionCub_Uninstall {

	/**
	 * Constructor: manages uninstall for multisite
	 *
	 */
	function __construct() {
		global $wpdb;

		// Check if it is a multisite uninstall - if so, run the uninstall function for each blog id
		if ( is_multisite() ) {
			foreach ( $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ) as $blog_id ) {
				switch_to_blog( $blog_id );
				$this->uninstall();
			}
			restore_current_blog();
		}
		else {
			$this->uninstall();
		}
	}

	/**
	 * Removes (most) all plugin data
	 * only when the relevant "LNT" option is active
	 *
	 * @return void
	 */
	function uninstall() {

		// Keep the settings if LNT not requested
		$settings = get_option( 'lioncub', array() );
		if ( isset( $settings['lnt'] ) && 'on' != $settings['lnt'] ) {
			return;
		}
		// Delete main settings
		delete_option( 'lioncub' );

		// Delete EDD licenses in post meta
		global $wpdb;
		$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_lioncub%'" );

		// Stop cron if we could
		// wp_clear_scheduled_hook( '' );

	}

}
new lionCub_Uninstall();