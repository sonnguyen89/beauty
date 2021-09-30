<?php
/**
 * WP SendGrid Mailer.
 *
 * WP SendGrid Mailer plugin file.
 *
 * @package   WPMailPlus
 * @copyright Copyright (C) 2010-2020, Smackcoders Inc - info@smackcoders.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: WP SendGrid Mailer
 * Version:     1.3
 * Plugin URI:  https://www.smackcoders.com/wp-ultimate-csv-importer-pro.html
 * Description: Configure Wordpress Mail function to send email using SMTP or Sendgrid.
 * Author:      Smackcoders
 * Author URI:  https://www.smackcoders.com/wordpress.html
 * Text Domain: wp-sendgrid-mailer
 * Domain Path: /languages
 * License:     GPL v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class MSP_WPMailPlus
{
	public function __construct()
	{
		$this->defineConstants();
		require_once WPMP_PLUGIN_DIR . 'vendor/autoload.php';
		$this->initHooks();
	}

	/**
	 * Define constants which are needed for the plugin
	 */
	public function defineConstants()
	{
		define('WPMP_NAME', 'SendGrid Mailer');
		define('WPMP_VERSION', '1.3');
		define('WPMP_PLUGIN_URL', plugin_dir_url(__FILE__));
		define('WPMP_PLUGIN_DIR', plugin_dir_path(__FILE__));
	}

	/**
	 * Initiate Hooks
	 */
	public function initHooks()
	{
		if(is_admin())
		{
			// Build Menu
			add_action('admin_menu', array($this, 'build_menu'));
			// Loading scripts and styles
			add_action('admin_enqueue_scripts', array($this, 'load_scripts'));
		}

		add_action( 'phpmailer_init', array($this, 'mailer_init') );
		add_action( 'wp_mail_failed', array($this, 'mailer_failed'), 10, 1 );
		add_action( 'wp_ajax_wp_mailplus_clear_logs', array($this, 'wp_mailplus_clear_logs'));

		add_filter('wp_mail_from', array($this, 'wp_mail_from_mail'));
		add_filter('wp_mail_from_name', array($this, 'wp_mail_from_name'));
	}

	/**
	 * Load admin scripts
	 */
	public function load_scripts()
	{
		if( isset( $_REQUEST['page'] ) && ($_REQUEST['page'] == 'wp-mailplus-settings' || $_REQUEST['page'] == 'wp-mailplus-logs'))
		{
			wp_enqueue_script('_wp_mailplus_admin-custom-js', WPMP_PLUGIN_URL . 'public/js/admin-custom.js', array());
			wp_enqueue_script('_wp_mailplus_bootstrap-js', WPMP_PLUGIN_URL . 'public/js/bootstrap.min.js', array());
			wp_register_script('sweet-alert-js', WPMP_PLUGIN_URL. 'public/js/sweetalert-dev.js', array());
			wp_enqueue_script('sweet-alert-js');
			wp_register_style('_wp_mailplus_bootstrap_css', WPMP_PLUGIN_URL . 'public/css/bootstrap.css', false, '1.0.0');
			wp_enqueue_style('_wp_mailplus_bootstrap_css');
			wp_register_style('_wp_mailplus_admin-custom-css', WPMP_PLUGIN_URL . 'public/css/admin-custom.css', false, '1.0.0');
			wp_enqueue_style('_wp_mailplus_admin-custom-css');
			wp_enqueue_style('sweet-alert-css', WPMP_PLUGIN_URL.'public/css/sweetalert.css', false, '1.0.0');

		}
	}

	/**
	 * Build admin menu
	 */
	public function build_menu()
	{
		add_menu_page(WPMP_NAME, WPMP_NAME, 'manage_options', 'wp-mailplus-settings', array('\WPMailPlus\Settings', 'process'), '', 59.34);
		add_submenu_page('wp-mailplus-settings', 'Settings', 'Settings', 'manage_options', 'wp-mailplus-settings', array('\WPMailPlus\Settings', 'process'));
		add_submenu_page('wp-mailplus-settings', 'Logs', 'Logs', 'manage_options', 'wp-mailplus-logs', array('\WPMailPlus\Logs', 'process'));
	}

	/**
	 * Update PHPMailer Instance
	 * @param $phpmailer
	 */
	public function mailer_init($phpmailer)
	{
		$enabled_service = get_option('_wp_mailplus_enabled_service');
		$service_info = get_option('_wp_mailplus_service_info');
		if($enabled_service == 'smtp')
		{
			$phpmailer->isSMTP();
			$phpmailer->Host = $service_info['smtp_host'];
			$phpmailer->SMTPAuth = false;
			if($service_info['smtp_authentication'])
				$phpmailer->SMTPAuth = true;

			$phpmailer->Port = $service_info['smtp_port'];
			$phpmailer->Username = $service_info['smtp_username'];
			$phpmailer->Password = $service_info['smtp_password'];
			$phpmailer->SMTPSecure = $service_info['smtp_encryption'];
		}
	}

	/**
	 * wp_mail_failed callback
	 * @param $wp_error
	 */
	public function mailer_failed($wp_error)
	{
		$from_info = get_option('_wp_mailplus_from_info');
		$email_from = \WPMailPlus\BaseController::prepare_from_email($from_info['from_name'], $from_info['from_email']);
		$email_service = get_option('_wp_mailplus_enabled_service');
		if($email_service == 'smtp')
			$email_service = 'SMTP';
		else
			$email_service = 'Default';

		$to = null;
		foreach($wp_error->error_data[2]['to'] as $to_key => $mail_to) {
			$to .= $mail_to . ',';
		}

		$to = substr($to, 0, -1);

		$log_data = array('email_from' => $email_from,
			'email_to' => $to,
			'email_service' => $email_service,
			'email_subject' => $wp_error->error_data[2]['subject'],
			'status' => 'Failed',
			'message' => $wp_error->errors[2][0]
		);

		\WPMailPlus\BaseController::addLog($log_data);
	}

	/**
	 * Clear logs
	 */
	public function wp_mailplus_clear_logs()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "mailplus_logs";
		$response = $wpdb->query("truncate table {$table_name}");
		if($response)
			die('Success');

		die('Failed');
	}

	/**
	 * Filter From Name
	 * @param string $from_name
	 * @return string
	 */
	public function wp_mail_from_name($from_name)
	{
		$more_info = get_option('_wp_mailplus_from_info');
		if(isset($more_info['from_name']) && !empty($more_info['from_name']))
			return $more_info['from_name'];
		return $from_name;
	}

	/**
	 * Filter From Email
	 * @param string $from_email
	 * @return string
	 */
	public function wp_mail_from_mail($from_email)
	{
		$more_info = get_option('_wp_mailplus_from_info');
		if(isset($more_info['from_email']) && !empty($more_info['from_email']))
			return $more_info['from_email'];
		return $from_email;
	}

	/**
	 * Function will trigger when user activate the plugin
	 */
	public static function activate()
	{
		global $wpdb;
		add_option('_wp_mailplus_enabled_service', 'default');
		add_option('_wp_mailplus_service_info', array());
		add_option('_wp_mailplus_from_info', array());
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . "mailplus_logs";
		$sql = "CREATE TABLE `$table_name` (
			`id` int(10) NOT NULL AUTO_INCREMENT,
			`mail_from` varchar(255) NOT NULL,
			`mail_to` varchar(255) NOT NULL,
			`email_service` varchar(100) NOT NULL,
			`email_subject` varchar(255) NOT NULL,
			`status` varchar(20) NOT NULL,
			`message` blob NOT NULL,
			`sent_time` datetime DEFAULT NULL,
			PRIMARY KEY (`id`)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Function will trigger when user de-activate the plugin
	 */
	public static function deactivate()
	{
		global $wpdb;
		delete_option('_wp_mailplus_enabled_service');
		delete_option('_wp_mailplus_service_info');
		delete_option('_wp_mailplus_from_info');
		$table_name = $wpdb->prefix . "mailplus_logs";
		$wpdb->query('DROP table ' . $table_name);
	}
}

new MSP_WPMailPlus();


$enabled_email_service = get_option('_wp_mailplus_enabled_service');
// Replacing wp_mail function if enabled email service is other than default and smtp
if(!function_exists('wp_mail') && !in_array($enabled_email_service, array('default', 'smtp')))
{
	function wp_mail($to, $subject, $message, $headers = '', $attachments = array())
	{
		$enabled_email_service = get_option('_wp_mailplus_enabled_service');
		if($enabled_email_service == 'sendgrid') {
			$emailService = new \WPMailPlus\Integrations\SendGridService();
		}

		$emailService->send_mail($to, $subject, $message, $headers, $attachments);
	}
}

function plugin_activate() {    
	global $wpdb;
	add_option('_wp_mailplus_enabled_service', 'default');
	add_option('_wp_mailplus_service_info', array());
	add_option('_wp_mailplus_from_info', array());
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . "mailplus_logs";
	$sql = "CREATE TABLE `$table_name` (
		`id` int(10) NOT NULL AUTO_INCREMENT,
		`mail_from` varchar(255) NOT NULL,
		`mail_to` varchar(255) NOT NULL,
		`email_service` varchar(100) NOT NULL,
		`email_subject` varchar(255) NOT NULL,
		`status` varchar(20) NOT NULL,
		`message` blob NOT NULL,
		`sent_time` datetime DEFAULT NULL,
		PRIMARY KEY (`id`)
	) $charset_collate;";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

register_activation_hook( __FILE__, 'plugin_activate' );

function myplugin_deactivate() {
	global $wpdb;
	delete_option('_wp_mailplus_enabled_service');
	delete_option('_wp_mailplus_service_info');
	delete_option('_wp_mailplus_from_info');
	$table_name = $wpdb->prefix . "mailplus_logs";
	$wpdb->query('DROP table ' . $table_name);
}
register_deactivation_hook( __FILE__, 'myplugin_deactivate' );
