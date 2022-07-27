<?php defined( 'ABSPATH' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'lionCub_Settings' ) ) :

	class lionCub_Settings {

		/**
		 * Constructor
		 */
		public function __construct() {

			// Add a handy settings link on the plugins listing page
			$plugin_file =  basename( __DIR__ ) . '/lion-cub.php';
			add_filter( 'plugin_action_links_' . $plugin_file,  array( $this, 'add_settings_link' ) );

			add_action( 'admin_init',                           array( $this, 'register_option' ) );
			add_action( 'admin_menu',                           array( $this, 'settings' ) );

		}

		/**
		 * Add settings link to WP plugin listing
		 *
		 * @param array $links
		 * @return array
		 */
		public function add_settings_link( $links ) {

			$settings_link = array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=lion-cub-settings' ) . '" aria-label="' . esc_attr__( 'View Lion Cub main settings', 'lion-cub' ) . '">' . esc_html__( 'Settings', 'lion-cub' ) . '</a>',
			);
			return array_merge( $settings_link, $links );

		}

		/**
		 * Creates our settings in the options table
		 *
		 * @param void
		 * @return void
		 */
		public function register_option() {

			register_setting( 'lioncub', 'lioncub', array( 'sanitize_callback' => array( $this, 'settings_sanitizer' ) ) );

		}

		/**
		 * Sanitizer callback for register_option()
		 *
		 * @param array $new
		 * @return array
		 */
		public function settings_sanitizer( $new ) {

			if ( ! empty( $new['lic_path'] ) ) {
				$new['lic_path'] = sanitize_text_field( $new['lic_path'] );
				if ( ! is_file( $new['lic_path'] ) && false === filter_var( $new['lic_path'], FILTER_VALIDATE_URL ) && ! @fopen( $new['lic_path'], 'r' ) ) {
					add_settings_error( 'lioncub_notice', 'lioncub_notice', 'Sorry, that doesn\'t seem to be a valid path to your make_license file', 'warning' );
				}
			}
			$new['api_key'] = sanitize_text_field( $new['api_key'] );

			return $new;

		}

		/**
		 * Add settings to the WP Admin menu (under Settings)
		 *
		 * @return void
		 */
		public function settings() {

			add_options_page( esc_html__( 'Lion Cub', 'lion-cub' ), esc_html__( 'Lion Cub', 'lion-cub' ), apply_filters( 'iontube_settings_manage_options', 'manage_options' ), 'lion-cub-settings', array( $this, 'settings_page' ) );

		}

		/**
		 * Settings page called from add_options_page()
		 *
		 * @return void
		 */
		public function settings_page() {

			if ( ! current_user_can( apply_filters( 'lioncub_settings_manage_options', 'manage_options' ) ) ) {
				return;
			}

			// Get settings array
			$lioncub = (array) get_option( 'lioncub', array() );

			$make_lic_path = $lioncub['lic_path'] ?? '';
			$api_key = $lioncub['api_key'] ?? '';
			$timezone = $lioncub['timezone'] ?? '';

			?>

			<div class="wrap">
				<h2><?php esc_html_e( 'Lion Cub Setup', 'lion-cub' ); ?></h2>

				<form method="post" action="options.php">

				<?php
					settings_fields( 'lioncub' );
					do_settings_sections( 'lioncub' );
				?>

					<table class="form-table">

						<tr>
							<th>
								<label for="lioncub_make_lic"><?php esc_html_e( 'Path to make_license file', 'lion-cub' ); ?></label>
							</th>
							<td>
								<input id="lioncub_make_lic" type="text" name="lioncub[lic_path]" class="regular-text ltr" value="<?php echo esc_attr( $make_lic_path ); ?>" />
							</td>
						</tr>

<tr>
							<th>
								<label for="lioncub_timezone"><?php esc_html_e( 'Timezone for licenses', 'lion-cub' ); ?></label>
							</th>
							<td>
								<select id="lioncub_timezone" name="lioncub[timezone]" />
								<?php foreach ( $this->get_timezones() as $key => $tz ) { ?>
									<option value="<?php echo esc_attr( $key ); ?>" <?php if ( $key === $timezone ) echo 'selected="selected"'; ?>><?php echo esc_html( $tz ); ?></option>
								<?php } ?>
							</td>
						</tr>
<script>function GetRandom(){let myElement = document.getElementById("lioncub_api");myElement.value = Array.from(Array(28), () => Math.floor(Math.random() * 36).toString(36)).join('')}</script>
						<tr>
							<th>
								<label for="lioncub_api"><?php esc_html_e( 'API Key', 'lion-cub' ); ?></label>
							</th>
							<td>
								<input id="lioncub_api" type="text" name="lioncub[api_key]" class="regular-text ltr" value="<?php echo esc_attr( $api_key ); ?>" /> <button OnClick="GetRandom()" type="button" class="btn button-secondary button btn-secondary">Generate one for me</button>
							</td>
						</tr>

						<tr>	
							<th>
								<label for="lioncub_debug"><?php esc_html_e( 'Debugging', 'wp-tcpdf-bridge' ); ?></label>
							</th>
							<td>
								<input id="lioncub_debug" type="checkbox" name="lioncub[debug]" <?php checked( 'on', $lioncub['debug'] ?? '' ); ?> />
								<label class="description" for="lioncub_debug">Check to turn on debugging. Easy Digital Downloads debug logging must be turned on in EDD "Misc" settings and are then found under Downloads->Tools.</label>
							</td>
						</tr>

						<tr>
							<th>
								<label for="lioncub_lnt"><?php esc_html_e( 'Leave No Trace', 'wp-tcpdf-bridge' ); ?></label>
							</th>
							<td>
								<input id="lioncub_lnt" type="checkbox" name="lioncub[lnt]" <?php checked( 'on', $lioncub['lnt'] ?? '' ); ?> />
								<label class="description" for="lioncub_lnt">If/when you delete Lion Cub License Generator from your Wordpress Installation,<br />would you like all the settings data removed from your database? Check if yes.</label>
							</td>
						</tr>
					</table>
				<?php submit_button(); ?>
				</form>
			</div>

		<?php }


		/**
		 * Return array of timezones
		 *
		 * @return array
		 */
		private function get_timezones() {

			//--------------------------------------------------------------------
			// TIMEZONES
			// http://php.net/manual/en/timezones.php
			//--------------------------------------------------------------------

			return array(
				'Pacific/Midway'        => '(GMT-11:00) Midway Island',
				'US/Samoa'              => '(GMT-11:00) Samoa',
				'US/Hawaii'             => '(GMT-10:00) Hawaii',
				'US/Alaska'             => '(GMT-09:00) Alaska',
				'US/Pacific'            => '(GMT-08:00) Pacific Time (US &amp; Canada)',
				'America/Tijuana'       => '(GMT-08:00) Tijuana',
				'US/Arizona'            => '(GMT-07:00) Arizona',
				'US/Mountain'           => '(GMT-07:00) Mountain Time (US &amp; Canada)',
				'America/Chihuahua'     => '(GMT-07:00) Chihuahua',
				'America/Mazatlan'      => '(GMT-07:00) Mazatlan',
				'America/Mexico_City'   => '(GMT-06:00) Mexico City',
				'America/Monterrey'     => '(GMT-06:00) Monterrey',
				'Canada/Saskatchewan'   => '(GMT-06:00) Saskatchewan',
				'US/Central'            => '(GMT-06:00) Central Time (US &amp; Canada)',
				'US/Eastern'            => '(GMT-05:00) Eastern Time (US &amp; Canada)',
				'US/East-Indiana'       => '(GMT-05:00) Indiana (East)',
				'America/Bogota'        => '(GMT-05:00) Bogota',
				'America/Lima'          => '(GMT-05:00) Lima',
				'America/Caracas'       => '(GMT-04:30) Caracas',
				'Canada/Atlantic'       => '(GMT-04:00) Atlantic Time (Canada)',
				'America/La_Paz'        => '(GMT-04:00) La Paz',
				'America/Santiago'      => '(GMT-04:00) Santiago',
				'Canada/Newfoundland'   => '(GMT-03:30) Newfoundland',
				'America/Buenos_Aires'  => '(GMT-03:00) Buenos Aires',
				'Greenland'             => '(GMT-03:00) Greenland',
				'Atlantic/Stanley'      => '(GMT-02:00) Stanley',
				'Atlantic/Azores'       => '(GMT-01:00) Azores',
				'Atlantic/Cape_Verde'   => '(GMT-01:00) Cape Verde Is.',
				'Africa/Casablanca'     => '(GMT) Casablanca',
				'Europe/Dublin'         => '(GMT) Dublin',
				'Europe/Lisbon'         => '(GMT) Lisbon',
				'Europe/London'         => '(GMT) London',
				'Africa/Monrovia'       => '(GMT) Monrovia',
				'Europe/Amsterdam'      => '(GMT+01:00) Amsterdam',
				'Europe/Belgrade'       => '(GMT+01:00) Belgrade',
				'Europe/Berlin'         => '(GMT+01:00) Berlin',
				'Europe/Bratislava'     => '(GMT+01:00) Bratislava',
				'Europe/Brussels'       => '(GMT+01:00) Brussels',
				'Europe/Budapest'       => '(GMT+01:00) Budapest',
				'Europe/Copenhagen'     => '(GMT+01:00) Copenhagen',
				'Europe/Ljubljana'      => '(GMT+01:00) Ljubljana',
				'Europe/Madrid'         => '(GMT+01:00) Madrid',
				'Europe/Paris'          => '(GMT+01:00) Paris',
				'Europe/Prague'         => '(GMT+01:00) Prague',
				'Europe/Rome'           => '(GMT+01:00) Rome',
				'Europe/Sarajevo'       => '(GMT+01:00) Sarajevo',
				'Europe/Skopje'         => '(GMT+01:00) Skopje',
				'Europe/Stockholm'      => '(GMT+01:00) Stockholm',
				'Europe/Vienna'         => '(GMT+01:00) Vienna',
				'Europe/Warsaw'         => '(GMT+01:00) Warsaw',
				'Europe/Zagreb'         => '(GMT+01:00) Zagreb',
				'Europe/Athens'         => '(GMT+02:00) Athens',
				'Europe/Bucharest'      => '(GMT+02:00) Bucharest',
				'Africa/Cairo'          => '(GMT+02:00) Cairo',
				'Africa/Harare'         => '(GMT+02:00) Harare',
				'Europe/Helsinki'       => '(GMT+02:00) Helsinki',
				'Europe/Istanbul'       => '(GMT+02:00) Istanbul',
				'Asia/Jerusalem'        => '(GMT+02:00) Jerusalem',
				'Europe/Kiev'           => '(GMT+02:00) Kyiv',
				'Europe/Minsk'          => '(GMT+02:00) Minsk',
				'Europe/Riga'           => '(GMT+02:00) Riga',
				'Europe/Sofia'          => '(GMT+02:00) Sofia',
				'Europe/Tallinn'        => '(GMT+02:00) Tallinn',
				'Europe/Vilnius'        => '(GMT+02:00) Vilnius',
				'Asia/Baghdad'          => '(GMT+03:00) Baghdad',
				'Asia/Kuwait'           => '(GMT+03:00) Kuwait',
				'Europe/Moscow'         => '(GMT+03:00) Moscow',
				'Africa/Nairobi'        => '(GMT+03:00) Nairobi',
				'Asia/Riyadh'           => '(GMT+03:00) Riyadh',
				'Europe/Volgograd'      => '(GMT+03:00) Volgograd',
				'Asia/Tehran'           => '(GMT+03:30) Tehran',
				'Asia/Baku'             => '(GMT+04:00) Baku',
				'Asia/Muscat'           => '(GMT+04:00) Muscat',
				'Asia/Tbilisi'          => '(GMT+04:00) Tbilisi',
				'Asia/Yerevan'          => '(GMT+04:00) Yerevan',
				'Asia/Kabul'            => '(GMT+04:30) Kabul',
				'Asia/Yekaterinburg'    => '(GMT+05:00) Ekaterinburg',
				'Asia/Karachi'          => '(GMT+05:00) Karachi',
				'Asia/Tashkent'         => '(GMT+05:00) Tashkent',
				'Asia/Kolkata'          => '(GMT+05:30) Kolkata',
				'Asia/Kathmandu'        => '(GMT+05:45) Kathmandu',
				'Asia/Almaty'           => '(GMT+06:00) Almaty',
				'Asia/Dhaka'            => '(GMT+06:00) Dhaka',
				'Asia/Novosibirsk'      => '(GMT+06:00) Novosibirsk',
				'Asia/Bangkok'          => '(GMT+07:00) Bangkok',
				'Asia/Jakarta'          => '(GMT+07:00) Jakarta',
				'Asia/Krasnoyarsk'      => '(GMT+07:00) Krasnoyarsk',
				'Asia/Chongqing'        => '(GMT+08:00) Chongqing',
				'Asia/Hong_Kong'        => '(GMT+08:00) Hong Kong',
				'Asia/Irkutsk'          => '(GMT+08:00) Irkutsk',
				'Asia/Kuala_Lumpur'     => '(GMT+08:00) Kuala Lumpur',
				'Australia/Perth'       => '(GMT+08:00) Perth',
				'Asia/Singapore'        => '(GMT+08:00) Singapore',
				'Asia/Taipei'           => '(GMT+08:00) Taipei',
				'Asia/Ulaanbaatar'      => '(GMT+08:00) Ulaan Bataar',
				'Asia/Urumqi'           => '(GMT+08:00) Urumqi',
				'Asia/Seoul'            => '(GMT+09:00) Seoul',
				'Asia/Tokyo'            => '(GMT+09:00) Tokyo',
				'Asia/Yakutsk'          => '(GMT+09:00) Yakutsk',
				'Australia/Adelaide'    => '(GMT+09:30) Adelaide',
				'Australia/Darwin'      => '(GMT+09:30) Darwin',
				'Australia/Brisbane'    => '(GMT+10:00) Brisbane',
				'Australia/Canberra'    => '(GMT+10:00) Canberra',
				'Pacific/Guam'          => '(GMT+10:00) Guam',
				'Australia/Hobart'      => '(GMT+10:00) Hobart',
				'Australia/Melbourne'   => '(GMT+10:00) Melbourne',
				'Pacific/Port_Moresby'  => '(GMT+10:00) Port Moresby',
				'Australia/Sydney'      => '(GMT+10:00) Sydney',
				'Asia/Vladivostok'      => '(GMT+10:00) Vladivostok',
				'Asia/Magadan'          => '(GMT+11:00) Magadan',
				'Pacific/Auckland'      => '(GMT+12:00) Auckland',
				'Pacific/Fiji'          => '(GMT+12:00) Fiji',
				'Asia/Kamchatka'        => '(GMT+12:00) Kamchatka',
			);

		}


	} // End class lionCub_Settings

endif;

new lionCub_Settings;