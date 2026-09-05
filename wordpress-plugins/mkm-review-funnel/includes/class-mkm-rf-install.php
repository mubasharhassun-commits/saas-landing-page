<?php
/**
 * Activation, schema creation and upgrades.
 *
 * @package MKM_Review_Funnel
 */

defined( 'ABSPATH' ) || exit;

/**
 * Handles the database schema for the plugin.
 */
class MKM_RF_Install {

	/**
	 * Schema version. Bump when the table definition changes.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Option holding the installed schema version.
	 */
	const DB_VERSION_OPTION = 'mkm_rf_db_version';

	/**
	 * Full table name for the feedback/entry store.
	 *
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'mkm_review_feedback';
	}

	/**
	 * Runs on activation.
	 */
	public static function activate() {
		self::create_tables();
		MKM_RF_Settings::install_defaults();
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
		flush_rewrite_rules();
	}

	/**
	 * Runs on deactivation. Data is intentionally left in place.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Creates or upgrades the entry table on every load when out of date.
	 */
	public static function maybe_upgrade() {
		if ( get_option( self::DB_VERSION_OPTION ) === self::DB_VERSION ) {
			return;
		}

		self::create_tables();
		MKM_RF_Settings::install_defaults();
		update_option( self::DB_VERSION_OPTION, self::DB_VERSION );
	}

	/**
	 * Creates the entry table via dbDelta.
	 */
	public static function create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table           = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		// dbDelta is picky about formatting: two spaces after PRIMARY KEY, lowercase types.
		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			sentiment varchar(20) NOT NULL DEFAULT 'negative',
			entry_type varchar(20) NOT NULL DEFAULT 'feedback',
			destination varchar(50) NOT NULL DEFAULT '',
			first_name varchar(100) NOT NULL DEFAULT '',
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(190) NOT NULL DEFAULT '',
			phone varchar(50) NOT NULL DEFAULT '',
			client_status varchar(100) NOT NULL DEFAULT '',
			message longtext NOT NULL,
			consent tinyint(1) NOT NULL DEFAULT 0,
			consent_text longtext NOT NULL,
			source_url varchar(255) NOT NULL DEFAULT '',
			referrer varchar(255) NOT NULL DEFAULT '',
			user_agent varchar(255) NOT NULL DEFAULT '',
			ip_address varchar(100) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'new',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY sentiment (sentiment),
			KEY entry_type (entry_type),
			KEY status (status),
			KEY created_at (created_at),
			KEY email (email)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}
