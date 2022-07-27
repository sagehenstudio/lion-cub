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

	} // End class lionCub_Settings

endif;

new lionCub_Settings;