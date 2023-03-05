<?php defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'ionCubeLicense' ) ) :

	class ionCubeLicense {

		protected $name;
		protected $res_string;
		protected $passphrase;
		protected $expiration;
		protected $server;
		protected $headers;
		protected $properties;
		protected $account;
		protected $save;
		protected $settings;

		/**
		 * Constructor
		 *
		 * @param array $license
		 * @return void
		 */
		public function __construct( $license = array() ) {

			$this->name         = $license['name'];
			$this->res_string   = $license['res_string'];
			$this->passphrase   = $license['passphrase'];
			$this->expiration   = $license['expiration'];
			$this->server       = $license['server'];
			$this->headers      = $license['headers'];
			$this->properties   = $license['properties'];
			$this->account      = $license['account'];
			$this->save         = $license['save'];
			$this->settings     = $license['settings'];

		}

		/**
		 * Escape single quotes
		 *
		 * @param string data
		 * @return string
		 */
		public function esc( $data ) {

			return "'" . str_replace( "'", "\'", $data ) . "'";

		}

		/**
		 * License creation using make_license
		 *
		 * @return string
		 */	
		public function create() {

			$path = LIONCUB_TMP_DIR . 'lic/';

			$string = $this->lock();
			$string .= $this->expiry();
			$string .= $this->properties();
			$string .= $this->headers();

			$string = apply_filters( 'lioncub_filter_encoder_string', $string );

			// We have collected debugging inside $this->debug as we built the $string...
			if ( LIONCUB_DEBUG ) {
				$logs = array();
				$logs[] = 'Lioncub - Path to make_license executable: ' . lioncub_new_line() . $this->settings['lic_path'] . lioncub_new_line() . '(Path exists? ' . ( @file_exists( $this->settings['lic_path'] ) ? 'Yes' : 'No' ) . ') ' . lioncub_new_line();
				$logs[] = 'Lioncub - Temp output path: ' . lioncub_new_line() . $path . $this->name . lioncub_new_line() . '(Directory exists? ' . ( is_dir( $path ) ? 'Yes' : 'No' ) . '; Directory writeable? ' . ( is_dir( $path ) && is_writeable( $path ) ? 'Yes' : 'No' ) . ')' . lioncub_new_line();
				$logs[] = 'Lioncub - Full encoder string sent to shell_exec():' . lioncub_new_line() . $string . lioncub_new_line(2);

				foreach ( $logs as $log ) {
					edd_debug_log( $log );
				}
			}

			$output = $path . $this->name;

			if ( ! is_writable( $output ) || ! file_exists( $output ) ) {
				@file_put_contents( $output, '<?php exit(); ?>' );
			}
			if ( ! is_writable( $output ) ) {
				error_log( 'Lioncub output file isn\'t writable.' );
				return 'err';
			}

			$debug = '';
			if ( LIONCUB_DEBUG ) {
				$debug = ' 2>&1';
			}

			$lic_path = $this->settings['lic_path'];

			shell_exec( "$lic_path $string -o $output $debug" );

			// Save and return path..
			if ( 'yes' === $this->save ) {
				if ( @file_exists( $output ) && filesize( $output ) > 0 ) {
					return $output;
				}
			}
			return 'err';

		}

		/**
		 * License passphrase and restrictions
		 *
		 * @return string
		 */	
		protected function lock() {

			$lock = array();
			$lock[] = "--passphrase '" . $this->passphrase . "'";
			
			if ( ! empty( $this->res_string ) ) {
				$res_string = str_replace( 
					array(
						'{DOMAIN}',
						'{IP}',
						'{MAC}'
					), 
					array(
						$this->server['domain'],
						$this->server['ip'],
						$this->server['mac']
					),
					$this->res_string 
				);

				// Check no double commas exist (ie, blank strings). This would cause the license program to fail.
				$lock[] = '--allowed-server ' . str_replace( ',,', ',', $res_string );
				if ( isset( $this->server['expose'] ) && $this->server['expose'] == 'yes' ) {
					$lock[] = '--expose-server-restrictions';
				}
			}
			return implode( ' ', $lock );

		}

		/**
		 * License headers
		 *
		 * @return string
		 */	
		protected function headers() {

			$head = array();
			$tags = array( // Tags are like shortcodes which can be used in headers for dynamic content
				'{NAME}'    => $this->account['name'],
				'{EMAIL}'   => $this->account['email'],
				'{DATE}'    => get_the_date(),
				'{TIME}'    => get_the_time(),
			);
			if ( ! empty( $this->headers ) ) {
				foreach ( $this->headers as $header ) {
					if ( $header ) {
						// Best to keep header lines and passphrases single-quoted
						$head[] = "--header-line '" . escapeshellcmd( strtr( $header, $tags ) ) . "'";
					}
				}
			}
			return ( ! empty( $head ) ? ' ' . implode( ' ', $head ) : '' );

		}

		/**
		 * License expiration
		 *
		 * @return string
		 */	
		protected function expiry() {

			$expire = array();

			if ( ! empty( $this->expiration['expire-on'] ) && $this->expiration['expire-on'] != '0000-00-00' ) {
				$expire[] = '--expire-on ' . $this->expiration['expire-on'];
			}
			if ( isset( $this->expiration['expire-in'] ) && (int) $this->expiration['expire-in'] > 0 ) {
				$expire[] = '--expire-in ' . $this->expiration['expire-in'] . substr($this->expiration['expire-duration'], 0, 1 );
			}
			if ( isset( $this->expiration['expire-expose'] ) && 'yes' === $this->expiration['expire-expose'] ) {
				$expire[] = '--expose-expiry';
			}
			return ( ! empty( $expire ) ? ' ' . implode( ' ', $expire ) : '' );

		}

		/**
		 * License properties
		 *
		 * @return string
		 */
		protected function properties() {

			$prop = [];
			$expose = [];
			$enforce = [];
			$atk = []; // @todo set this up
			$tags = [ // tags are like shortcodes which can be used in headers for dynamic content
				'{NAME}'    => $this->account['name'],
				'{EMAIL}'   => $this->account['email'],
				'{DATE}'    => date_i18n( get_option( 'date_format' ) ),
				'{TIME}'    => date_i18n( get_option( 'time_format' ) ),
			];

			if ( ! empty( $this->properties ) ) {
				for ( $i = 0; $i < count( $this->properties ); $i++ ) {
					if ( $this->properties[$i]['value'] ) {

						$prop[] = strtr( $this->properties[$i]['key'], $tags ) . '=' . escapeshellcmd( self::esc( strtr( $this->properties[$i]['value'], $tags ) ) );

						// Expose?
						if ( 'yes' === $this->properties[$i]['expose'] ) {
							$expose[] = '--expose-property ' . $this->properties[$i]['key'];
						}

						// Enforce?
						if ( 'yes' === $this->properties[$i]['enforce'] ) {
							$enforce[] = '--enforce-property ' . $this->properties[$i]['key'];
						}
					}
				}
			}
			return ( ! empty( $prop ) ? ' --properties "' . implode( ',', $prop ) . '"' : '' ) . ( ! empty( $expose ) ? ' ' . implode( ' ', $expose ) : '' ) . ( ! empty( $enforce ) ? ' ' . implode( ' ', $enforce ) : '' ) . ( ! empty( $atk ) ? ' ' . implode( ' ', $atk ) : '' );

		}

	} // end class ionCubeLicense

endif;