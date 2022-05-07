<?php
/**
 * Plugin Name: Lion Cub - IonCube License Generator
 * Description: A simple adapation of Maian Cube license generator API
 *
 * Version: 1.0.1
 * Author: Sagehen Studio
 * Text Domain: lion-cub
 * 
 * Lion Cub License Generator
 * Copyright: (c) 2022 Sagehen Studio
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * Lion Cub is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3 of the License, or
 * any later version.
 
 * Lion Cub is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 
 * You should have received a copy of the GNU General Public License
 * along with Lion Cub. If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * Adapted for Wordpress and EDD from MaianCube by Maian Media
 * https://www.maiancube.com
 *
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

if ( ! class_exists( 'lionCub' ) ) :

    class lionCub {

		/**
		 * Single instance of the lionCub class
		 *
		 * @var lionCub
		 */
		protected static $_instance = null;

		public $data = array(
			'timezone' 	=> 'US/Pacific',
			'license' 	=> [ 
				'expire_in'     =>'7',
				'duration'      => 'd',
				'expose'        => 'yes',
				'passphrase'    => 'passphrase',
			],

		);


		/**
		 * Instantiator
		 *
		 * @return void
		 */
		public static function instance() {

			if ( ! isset( self::$_instance ) && ! ( self::$_instance instanceof lionCub ) ) {
				self::$_instance = new lionCub;
			}
			return self::$_instance;

		}


		/**
		 * Constructor
		 *
		 * @return void
		 */
		public function __construct() {

			register_activation_hook( __FILE__, array( $this, 'setup_temp_dir' ) );

			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );

			add_action( 'init', array( $this, 'init' ) );

		}


		/**
		 * Secure a temp directory for ZIP manipulation
		 *
		 * @return void
		 */
		public function setup_temp_dir() {

			$dirs = array( 
				WP_CONTENT_DIR . '/uploads/lion-cub/',
				WP_CONTENT_DIR . '/uploads/lion-cub/lic/',
			);

			foreach ( $dirs as $key => $dir ) {

				if ( ! is_dir( $dir ) ) {
					wp_mkdir_p( $dir );
				}
				// try again
				if ( ! is_dir( $dir ) ) {
					@mkdir( $dir, 0755 );
				}
				if ( $key === 0 ) {
					$this->check_temp_security( $dir );
				}

			}

		}


		/**
		 * Check on security of temp folder
		 *
		 * @return void
		 */
		private function check_temp_security( $temp_dir ) {

			if ( ! file_exists( $temp_dir . 'index.php' ) ) {
				@file_put_contents( $temp_dir . 'index.php', '<?php' . PHP_EOL . '// Silence is golden.' );
			}

			// Top level .htaccess file
			$rules = 'Options -Indexes';
			if ( file_exists( $temp_dir . '.htaccess' ) ) {
				$contents = @file_get_contents( $temp_dir . '.htaccess' );
				if ( $contents !== $rules || ! $contents ) {
					// Update the .htaccess rules if they don't match
					@file_put_contents( $temp_dir . '.htaccess', $rules );
				}
			} else if ( wp_is_writable( $temp_dir ) ) {
				// Create the file if it doesn't exist
				@file_put_contents( $temp_dir . '.htaccess', $rules );
			}

		}


		/**
		 * Define REST API route
		 *
		 * @return void
		 */
		public function rest_api_init() {

			register_rest_route( 'lion-cub', '/make-license', array(
				'methods'  => array( 'GET', 'POST' ), // get for "public" access (with key), post for internal requests
				'callback' => array( $this, 'handle_api_call' ),
				'args'     => [
					'api_key' => [
						'required'  => true,
						'type'      => 'string',
					],
				],
				'permission_callback' => array( $this, 'permission_callback' ), // check the API key
			) );

		}


		/**
		 * Hooked to WP init()
		 *
		 * @return void
		 */
		public function init() {

			$this->define_constants();

			$this->includes();

			add_action( 'lioncub_cleanup_temp_files', array( $this, 'cleanup_temp_files' ) );

			add_filter( 'edd_requested_file', array( $this, 'edd_requested_file' ), 11, 4 );

		}


		/**
		 * Define constants
		 *
		 * @return void
		 */
		private function define_constants() {

			if ( ! defined( 'LIONCUB_VERSION' ) ) {
				define( 'LIONCUB_VERSION', '1.0' );
			}

			if ( ! defined( 'LIONCUB_BASE_PATH' ) ) {
				define( 'LIONCUB_BASE_PATH', __DIR__ );
			}

			if ( ! defined( 'LIONCUB_API_RESPONSE' ) ) {
				define( 'LIONCUB_API_RESPONSE', 'json' ); // http = http header response, json = json response
			}

			if ( ! defined( 'LIONCUB_DEBUG' ) ) {
				$main_settings = get_option( 'lioncub' );
				if ( $main_settings['debug'] === 'on' ) {
					define( 'LIONCUB_DEBUG', true );
				} else {
					define( 'LIONCUB_DEBUG', false );
				}
			}

			if ( ! defined( 'LIONCUB_TMP_DIR' ) ) {
				define( 'LIONCUB_TMP_DIR', WP_CONTENT_DIR . '/uploads/lion-cub/' );
			}

			if ( ! defined( 'LIONCUB_TMP_LIC_DIR' ) ) {
				define( 'LIONCUB_TMP_LIC_DIR', WP_CONTENT_DIR . '/uploads/lion-cub/lic/' );
			}

		}


		/**
		 * Include files
		 *
		 * @return void
		 */
		private function includes() {

			include_once 'settings.php';
			include_once 'edd.php';

		}


		/**
		 * REST API route permission callback
		 * Verify API key
		 *
		 * @return void
		 */
		public function permission_callback( $req ) {

			// Get API key from settings
			$main_settings = get_option( 'lioncub' );
			$api_key = sanitize_text_field( $main_settings['api_key'] ) ?? '';

			// Get request API key
			$params = $req->get_params();

      		return $api_key === $params['api_key'];

		}


		/**
		 * Write license file when EDD file is requested
		 * Uses data stored in database post_meta
		 *
		 * @param  string $requested file
 		 * @param  array  $download_files
		 * @param  string $file_key
		 * @param  array  $args
		 * @return string
		 */
		public function edd_requested_file( $requested_file, $download_files, $file_key, $args ) {

			$settings = get_post_meta( $args['download'], '_lioncub', true );

			// Continue or not based on if this file has IonCube licensing set up/turned on
			if ( isset( $settings[$file_key]['licensing'] ) ) {
				if ( 'on' !== $settings[$file_key]['licensing'] ) {
					return $requested_file;
				}
			} else {
				return $requested_file;
			}

			$this->data = $this->get_license_string( $file_key, $args, $settings );

			$this->write_license( $file_key, $args, $settings );

			// Get the license content
			$contents = get_post_meta( $args['payment'], '_edd_ioncube_lic', true );

			if ( ! empty( $contents ) ) {

				$this->check_temp_security( LIONCUB_TMP_DIR );

				$resource = false;
				$lic_filepath = sanitize_text_field( $settings[$file_key]['filepath'] );
				$lic_filename = sanitize_text_field( $settings[$file_key]['filename'] );
				$lic_filename = apply_filters( 'lioncub_filter_license_file', $lic_filename, $requested_file, $download_files, $file_key, $args );
				$lic_file = $lic_filepath . $lic_filename;
				$req_filename = basename( $requested_file );
				$file_extension = strtolower( edd_get_file_extension( $requested_file ) );

				if ( class_exists( 'ZipArchive' ) ) {

					$zip = new ZipArchive;
					$temp_file = LIONCUB_TMP_DIR . $req_filename;

					// Requested file is a ZIP?
					if ( 'zip' === $file_extension ) {

						if ( is_writable( LIONCUB_TMP_DIR ) && copy( $requested_file, $temp_file ) ) {
							// Try to open existing ZIP
	   						$resource = $zip->open( $temp_file ); 	 
						} else {
							edd_debug_log( 'Lion Cub - Error: PHP Class "ZipArchive" not found and is required!' );
						}

					} else {

						$req_filename_wo_extension = basename( $requested_file, '.' . $file_extension );

						if ( is_writable( LIONCUB_TMP_DIR ) ) {

							$temp_file = LIONCUB_TMP_DIR . $req_filename_wo_extension . '.zip';

							// Create a new ZIP file
							$resource = $zip->open( $temp_file, ZipArchive::CREATE );
			 		 		// Add the requested file to the ZIP
							$zip->addFile( $requested_file, $req_filename );
							$req_filename = $temp_file;

						}

					}

					// We have successfully opened/created a ZIP file
					if ( $resource === true ) {

						// Add a file new.txt file to zip using the text specified
						$zip->addFromString( $lic_file, $contents );
    						// $zip->addFromString( $lic_file, $contents, ZipArchive::FL_ENC_UTF_8 ); // only works in PHP 8+

					} else {

						edd_debug_log( 'Lion Cub - Error: PHP Class "ZipArchive" did not open a ZIP file' );

					}

					$zip->close();
	
				} else {
					edd_debug_log( 'Lion Cub - Error: PHP Class "ZipArchive" not found and is required!' );
				}

				// Set a transient to schedule deletion; within 10 seconds will be eligible for deletion
				set_transient( md5( $req_filename ), '1', 10 );

				// Schedule deletion function to run in 15 seconds
				if ( ! wp_next_scheduled( 'lioncub_cleanup_temp_files' ) ) {
					wp_schedule_single_event( current_time( 'timestamp' ) + 15, 'lioncub_cleanup_temp_files' );
				}

				return $temp_file;

			}

			return $requested_file;

		}


		/**
		 * CRON to delete download files created during ZIP
		 *
		 * @return void
		 */
		public function cleanup_temp_files() {

			// Bail if not in WordPress cron
			if ( ! edd_doing_cron() ) {
				return;
			}

			$path = LIONCUB_TMP_DIR;
			$dir = opendir( $path );

			while ( ( $file = readdir( $dir ) ) !== false ) {

				if ( $file == 'index.php' || $file == '.htaccess' || $file == 'make_license' || $file == '.' ) {
					continue;
				}
				$transient = get_transient( md5( $file ) );
				if ( $transient === false ) {
					@unlink( $path . '/' . $file );
				}

			}

		}


		/**
		 * Assemble license string from settings
		 *
		 * @param  string $file_key
		 * @param  array  $args
		 * @param  array  $settings
		 * @return array
		 */
		protected function get_license_string( $file_key, $args, $settings ) {

			$main_settings = get_option( 'lioncub' );

			$license = [];
			$license['expire_in'] = '7';
			$license['duration'] = 'd';
			$license['expire_on'] = '';
			if ( isset( $settings[$file_key]['passphrase'] ) ) {
				$license['passphrase'] = sanitize_text_field( $settings[$file_key]['passphrase'] );
			}
			if ( isset( $settings[$file_key]['duration'] ) ) {
				$license['expire_in'] = sanitize_text_field( $settings[$file_key]['duration'] );
			}
			if ( isset( $settings[$file_key]['duration_unit'] ) ) {
				$license['duration'] = sanitize_text_field( $settings[$file_key]['duration_unit'] );
			}
			if ( isset( $settings[$file_key]['expire_on'] ) ) {
				$license['expire_on'] = sanitize_text_field( $settings[$file_key]['expire_on'] );
			}
			if ( isset( $settings[$file_key]['duration_expose'] ) ) {
				$license['expose'] = $settings[$file_key]['duration_expose'] === 'on' ? 'yes' : '';
			}

			$customer = new EDD_Customer( $args['email'] );

			$data = array( 
					'api_key' 	=> sanitize_text_field( $main_settings['api_key'] ) ?? '',
					'timezone' 	=> sanitize_text_field( $main_settings['timezone'] ) ?? 'US/Pacific',
					'license' 	=> $license,
					'email'		=> $args['email'],
					'name'		=> $customer->name,
				
			); // end $data

			if ( ! empty( $settings[$file_key]['header'] ) && '' !== $settings[$file_key]['header'] ) {

				$headers = array_map( 'sanitize_text_field', $settings[$file_key]['header'] );
				$data['headers'] = apply_filters( 'lioncub_filter_headers', $headers, $args );

			}

			if ( isset( $settings[$file_key]['restrictions'] ) && '' !== $settings[$file_key]['restrictions'] ) {

				$restrictions = array( 
					'encoder_string' => sanitize_text_field( $settings[$file_key]['restrictions'] ),
					'expose'			=> $settings[$file_key]['restrictions_expose'] === 'on' ? 'yes' : 'no',
					
				);
				$data['restrictions'] = apply_filters( 'lioncub_filter_restrictions', $restrictions, $args );

			}

			if ( ! empty( $settings[$file_key]['properties'] ) ) {

				$props = [];
				$count = 0;
				$properties = $settings[$file_key]['properties'];
				foreach( $properties as $key => $property ) {

					if ( $key % 2 === 0 ) {
						$prev_prop = $property;
						continue;
					}
					
					$props[] = array( 
						'key' 		=> $prev_prop,
						'value'		=> $property,
						'expose'		=> isset( $settings[$file_key]['properties_expose'][$count] ) ?? '',
						'enforce'	=> isset( $settings[$file_key]['properties_enforce'][$count] ) ?? '',
					);

					if ( $key % 2 !== 0 ) {
						++$count;
						
					}
					
				}
				$data['properties'] = apply_filters( 'lioncub_filter_properties', $props, $args );

			}
			return apply_filters( 'lioncub_filter_data', $data, $file_key, $args, $settings );

		}


		/**
		 * Write license string
		 *
		 * @param string $file_key
		 * @param array 	$args
		 * @param array 	$settings
		 * 
		 * @return void
		 */
		protected function write_license( $file_key, $args, $settings ) {

			$response = $this->wp_remote_post( wp_json_encode( $this->data ) );

			if ( ! $response ) {
				// try one more time
				sleep(1); // TRY AGAIN
				$response = $this->wp_remote_post( wp_json_encode( $this->data ) );
				if ( ! $response ) {
					edd_debug_log( 'Lion Cub - Error: No response attempting to contact make_license' );
					return;
				}
			}

			$response = json_decode( $response, true );

			if ( isset( $response['status'] ) && 'OK' === $response['status'] ) {

				// Sweet. Now get license:
				if ( isset( $response['license'] ) && strlen( $response['license'] ) > 1 ) {
					$license = $response['license'];
				} else { 
					edd_debug_log( 'Lion Cub - Error: License is goofy: ' . print_r( $response['license'], true ) );
				}		
				if ( ! empty( $license ) ) {
					$meta_id = update_post_meta( $args['payment'], '_edd_ioncube_lic', $license );
					if ( ! $meta_id ) {
						edd_debug_log( 'Lion Cub - Error: Failed to add post meta to record Ioncube license.' );
					}
				}

			} else {
				edd_debug_log( 'Lion Cub - Error: Failed response from make_license via API: ' . print_r( $response, true ) );
			}

		}



		/**
		 * Make HTTP request
		 * Hits IonCube make_license executable
		 *
		 * @return array
		 */
		private function wp_remote_post( $data ) {

			$main_settings = get_option( 'lioncub' );
			$api_key = sanitize_text_field( $main_settings['api_key'] ) ?? '';

			$args = array( 
				'body' => array(
        				'data' => $data,
			    ),
				'timeout' => 10,
			);
			$response = wp_remote_post( get_rest_url( null, 'lion-cub/make-license/' ) . '?api_key=' . $api_key, $args );

			// Check for error
			if ( is_wp_error( $response ) ) {
				edd_debug_log( 'Lioncub - Error: wp_remote_post() WP error' );
				return;
			}

			// Parse remote HTML file
			$response = wp_remote_retrieve_body( $response );

			// Check for error
			if ( is_wp_error( $response ) ) {
				edd_debug_log( 'Lioncub - Error: wp_remote_retrieve_body() WP error' );
				return;
			}

			return $response;

		}


		/**
		 * Gather, echo data from API call
		 *
		 * @return void
		 */
		public function handle_api_call( $req ) {

			if ( ! isset( $req['data'] ) || empty( $req['data'] ) ) {
				$data = $this->data;
			} else {
				$data = $req['data'];
				$data = json_decode( $data, true );
			}

			$data = apply_filters( 'lioncub_filter_api_data', $data );


			if ( LIONCUB_DEBUG ) {
				edd_debug_log( 'Lioncub - Data received by API handler: ' . print_r( $data, true ) );
			}

			$api_key   	= $data['api_key'] ?? '';
			$name     	= $data['name'] ?? '';
			$email    	= $data['email'] ?? '';

			//------------------------
			// SET TIMEZONE
			//------------------------

			include( 'functions.php' );
			$timezone = $data['timezone'] ?? 'US/Pacific';
			date_default_timezone_set( $timezone );

			$expiration = array(
				'expire-on' 			=> $data['license']['expire_on'] ?? '0000-00-00',
				'expire-in' 			=> $data['license']['expire_in'] ?? '',
				'expire-duration'	=> $data['license']['duration'] ?? '',
				'expire-expose' 		=> ( isset( $data['license']['expose'] ) && in_array( $data['license']['expose'], array( 'yes', 'no' ) ) ? $data['license']['expose'] : 'no' ),
			 );

			$server = array(
				'domain' 	=> $data['restrictions']['domain'] ?? '',
				'ip' 		=> $data['restrictions']['ip'] ?? '',
				'mac' 		=> $data['restrictions']['mac'] ?? '',
				'expose' 	=> ( isset( $data['restrictions']['expose'] ) && in_array( $data['restrictions']['expose'], array( 'yes', 'no' ) ) ? $data['restrictions']['expose'] : 'no' ),
			 );

			// Some user information to pass for TAG/SHORTCODE use later on in headers
 			$account = array(
		      	'name' => $name,
      			'email' => $email,
    			);

			// Define temp file here so can be deleted afterward (below)
			$file = 'license-' . sha1( time() ) . '.txt';

			//------------------------
			// CREATE LICENSE
			//------------------------

			include( 'licensing.php' );

			$license = new ionCubeLicense( array(
				'name' 		=> $file,
				'res_string'	=> $data['restrictions']['encoder_string'] ?? '',
				'passphrase'	=> $data['license']['passphrase'] ?? '',
				'expiration'	=> $expiration,
				'server' 	=> $server,
				'headers' 	=> ( ! empty( $data['headers'] ) ? $data['headers'] : array() ),
				'properties'	=> ( ! empty( $data['properties'] ) ? $data['properties'] : array() ),
				'account' 	=> $account,
				'save' 		=> 'yes',
				'settings' 	=> get_option( 'lioncub', array() ),
			) );

			$lic_file = $license->create();

			if ( 'err' !== $lic_file ) {

				// Get contents of license file
				$string = lioncub_file_get_contents( $lic_file );

				if ( LIONCUB_DEBUG ) {
					edd_debug_log( 'Lioncub - License Successfully Created: ' . print_r( $string, true ) );
				}

				// Delete contents of license file
				@unlink( $lic_file );

				switch( LIONCUB_API_RESPONSE ) {
					case 'json':
						echo json_encode( 
							array(
								'status' => 'OK',
								'license' => $string,
							) 
						);
						break;
					default:
						header( 'HTTP/1.0 200 OK' );
						echo $string;
						break;
				}

			} else {

				edd_debug_log( 'Lioncub - Error: License creation failed.' );

				switch( LIONCUB_API_RESPONSE ) {
					case 'json':
						echo json_encode(
							array(
								'status' => 'ERR'
							) 
						);
						break;
					default:
						http_response_code( 403 );
						echo 'ERR';
						break;
				}

			}

		}

	} // End class lionCub

endif;

function birth_lion_cub() {
	return lionCub::instance();
}
birth_lion_cub();