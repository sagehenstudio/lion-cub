<?php defined( 'ABSPATH' ) || exit;

// Class declared below

if ( ! class_exists( 'lioncub_EDD' ) ) :

	class lioncub_EDD {

		/**
		 *
		 * CONSTRUCTOR
		 *
		 */
		public function __construct() {

			// For handling of different EDD files inside one post - EDD "repeatable"
			add_action( 'edd_download_file_table_row', array( $this, 'table_row' ), 15, 3 );

			// Sanitize and save settings
			add_action( 'edd_save_download', array( $this, 'edd_save_download' ), 15, 2 );

		}


		/**
		 * Output settings inside each EDD download file
		 * Repeatable
		 *
		 * @param  int    $post_id Download (Post) ID
		 * @param  string $key     Array key
		 * @param  array  $args    Array of all the arguments passed to the function
		 * @return void
		 */
		public function table_row( $post_id, $key, $args ) {

			$settings 		= get_post_meta( $post_id, '_lioncub', true );
			$passphrase 		= $settings[$key]['passphrase'] ?? '';
			$expire_on 		= $settings[$key]['expire_on'] ?? '';
			$duration 		= $settings[$key]['duration'] ?? '';
			$duration_unit	= $settings[$key]['duration_unit'] ?? '';
			$restrictions 	= $settings[$key]['restrictions'] ?? '';
			$filepath 		= $settings[$key]['filepath'] ?? '/';
			$filename 		= $settings[$key]['filename'] ?? 'license.icl';
			?>

			<span style="display:block">&nbsp;</span><br />
			
			<h3><?php esc_html_e( 'Lion Cub', 'lion-cub' ); ?></h3>
			
			<input type="checkbox" name="lioncub[<?php echo esc_attr( $key ); ?>][licensing]" <?php isset( $settings[$key]['licensing'] ) ? checked( $settings[$key]['licensing'], 'on' ) : ''; ?> class="lioncub-toggle" id="lioncub-licensing-<?php echo $key . '-' . $key; ?>" data-id="lioncub-licensing"> <label for="lioncub-licensing-<?php echo $key . '-' . $key; ?>"><?php esc_html_e( sprintf( __( 'Create Ioncube licenses for this download (file ID: %s)?', 'lion-cub' ), $key ) ); ?></label>

			<div class="lioncub-licensing-<?php echo $key . '-' . $key; ?>">

			<script>	jQuery('.lioncub-toggle').on('change', function(){
				var id = jQuery(this).attr('id')
				jQuery("."+id).hide();
				if (jQuery(this).is(':checked')) {
					jQuery("."+id).show();
				}
				}).change();
			</script>

<!-- EXPIRATION -->

			<h4><?php esc_html_e( 'License Expiry', 'lion-cub' ); ?></h4>

			<p><label for="lioncub-expire-on">Expire on specific date?</label><br />
			<input type="text" name="lioncub[<?php echo esc_attr( $key ); ?>][expire_on]" value="<?php echo esc_attr( $expire_on ); ?>" placeholder="yyyy-mm-dd" id="lioncub-expire-on"> OR, expire in <input type="number" name="lioncub[<?php echo esc_attr( $key ); ?>][duration]" value="<?php echo esc_attr( $duration ); ?>" min="1"> <select name="lioncub[<?php echo esc_attr( $key ); ?>][duration_unit]"><option value="n" <?php selected( $duration_unit, ''); ?>>Select</option><option value="s" <?php selected( $duration_unit, 's'); ?>>Seconds</option><option value="m" <?php selected( $duration_unit, 'm'); ?>>Minutes</option><option value="h" <?php selected( $duration_unit, 'h'); ?>>Hours</option><option value="d" <?php selected( $duration_unit, 'd'); ?>>Days</option></select> &nbsp; <input type="checkbox" name="lioncub[<?php echo $key; ?>][duration_expose]" id="lioncub-duration-expose" <?php isset( $settings[$key]['duration_expose'] ) ? checked( $settings[$key]['duration_expose'], 'on' ) : ''; ?>><label for="lioncub-duration-expose">Expose expiration?</label></p>

<!-- PASSPHRASE -->

			<h4><?php esc_html_e( 'Passphrase', 'lion-cub' ); ?></h4>

				<p>
					<label for="lioncub-passphrase">Optional license passphrase, to match passphrase set in IonCube</label><br />
					<input type="text" name="lioncub[<?php echo $key; ?>][passphrase]" value="<?php echo esc_attr( $passphrase ); ?>" id="lioncub-passphrase"></p>


<!-- RESTRICTIONS -->

			<h4><?php esc_html_e( 'License Restrictions', 'lion-cub' ); ?></h4>

				<p>
					<label for="lioncub-restrictions">This string may only include MAC address(es) if running Cerberus version of Ioncube. <a href="https://www.ioncube.com/sa/USER-GUIDE.pdf#page=32" rel="noopener" target="_blank">More info</a>.</label>
					<input type="text" name="lioncub[<?php echo esc_attr( $key ); ?>][restrictions]" value="<?php echo esc_attr( $restrictions ); ?>" style="display:inline-block;width:75%;max-width:75%" id="lioncub-restrictions"> &nbsp; <input type="checkbox" name="lioncub[<?php echo $key; ?>][restrictions_expose]" id="lioncub-restrictions-expose" <?php isset( $settings[$key]['restrictions_expose'] ) ? checked( $settings[$key]['restrictions_expose'], 'on' ) : ''; ?>><label for="lioncub-restrictions-expose">Expose restrictions?</label></p>

<!-- HEADERS -->

			<h4><?php esc_html_e( 'License header line(s)', 'lion-cub' ); ?></h4>

				<p>Use the `lioncub_filter_headers` filter hook for more programmatic control of headers</p>
				<div id="headers">


				<script>
				function AddLionCubHeaderField( id, name, value, tag ) {
					if( ! document.getElementById && document.createElement ) { 
						return; 
					}
					let holder = document.getElementById( id );
					let input = document.createElement( "input" );
					input.name = name;
					input.type = 'text';
					input.value = value;
					input.className = 'large-text';
					if ( tag.length > 0 ) {
						let thetag = document.createElement( tag );
						thetag.appendChild( input );
						holder.appendChild( thetag );
					} else {
						holder.appendChild( input ); 
					}
				}
				</script>		

					<?php 

						$header_values = $settings[$key]['header'] ?? array( '' );
						foreach ( $header_values as $i => $value ) { ?> 
							<p>
								<label for="lioncub-headers-<?php echo esc_attr( $key ) . '-' . $i; ?>" class="screen-reader-text"><?php esc_html_e( 'Header line #', 'lion-cub' ); echo $i + 1; ?></label>
							<?php if ( ! empty( $value ) ) { ?>
								<input type="text" name="lioncub[<?php echo esc_attr( $key ); ?>][header][]" value="<?php echo esc_attr( $value ); ?>" class="large-text" id="lioncub-headers-<?php echo $key . '-' . $i; ?>">
							<?php } else {
								if ( $i < 1 ) { ?>
									<input type="text" name="lioncub[<?php echo esc_attr( $key ); ?>][header][]" value="" class="large-text" id="lioncub-headers-<?php echo $key . '-' . $i; ?>">

								<?php }
							} ?>
							</p>

						<?php } 
					?>

				</div>
				<button type="button" onclick="AddLionCubHeaderField( 'headers', 'lioncub[<?php echo $key; ?>][header][]', '', 'p' )" class="button button-secondary"><?php esc_html_e( 'Add more header lines', 'lion-cub' ); ?></button>	




<!-- PROPERTIES -->

				<h4><?php esc_html_e( 'License Properties', 'lion-cub' ); ?></h4>

				<p>Use the `lioncub_filter_properties` filter hook for more programmatic control of properties</p>
				<div id="properties">


				<script>
				function AddLionCubPropField( id, key, name, value, tag ) {
					if( ! document.getElementById && document.createElement ) { 
						return; 
					}
					let holder = document.getElementById( id );
					let input = [];
					let label = [];
					let number = holder.getElementsByClassName( 'lioncub-props' );
					for (var i = 0; i < 4; i++) {
						input[i] = document.createElement("input");
						input[i].name = name;
						if ( i < 2 ) {
							input[i].type = 'text';
							input[i].value = value;
						} else {
							input[i].id = 'lioncub-properties-expose' + i;
							input[i].type = 'checkbox';
							label[i] = document.createElement("label");
							if ( i === 2 ) {
								label[i].innerHTML = "Expose property?";
								input[i].name = 'lioncub[' + key + '][properties_expose][]';
							} 
							if ( i === 3 ) {
								label[i].innerHTML = "Enforce property?";
								input[i].name = 'lioncub[' + key + '][properties_enforce][]';
							}
							label[i].setAttribute( "for", "lioncub-properties-expose" + i );
						}
					}
					if ( tag.length > 0 ) {

					let thetag = document.createElement( tag );
					let spacer = document.createTextNode( "\u00A0\u00A0\u00A0" );
					let spacer2 = document.createTextNode( "\u00A0\u00A0\u00A0" );
					let spacer3 = document.createTextNode( "\u00A0\u00A0\u00A0" );

					thetag.appendChild( input[0] ); 
					thetag.appendChild( spacer );
					thetag.appendChild( input[1] );
					thetag.appendChild( spacer2 );
					thetag.appendChild( input[2] );
					thetag.appendChild( label[2] );
					thetag.appendChild( spacer3 );
					thetag.appendChild( input[3] );
					thetag.appendChild( label[3] );

					holder.appendChild( thetag );

				} else {
					holder.appendChild( input[0] ); 
					holder.appendChild( input[1] ); 
					holder.appendChild( input[2] ); 
					holder.appendChild( input[3] ); 
				}	
			}
			</script>		

			<?php 
						$count = 0;

						$properties = ! empty( $settings[$key]['properties'] ) ? $settings[$key]['properties'] : array();

						if ( ! empty( $properties ) ) {

							foreach ( $properties as $int => $value ) {

								if ( $int % 2 === 0 ) {

									echo '<p><input type="text" name="lioncub[' . $key . '][properties][]" value="' .  esc_html( $value ) . '"> &nbsp; ';
									
								}
								if ( $int % 2 !== 0 ) { 
									 ?>
							
									<input type="text" class="ouiouiou" name="lioncub[<?php echo $key; ?>][properties][]" value="<?php echo esc_attr( $value ); ?>">
									 &nbsp; 
									<input type="checkbox" name="lioncub[<?php echo $key; ?>][properties_expose][<?php echo $count; ?>]" id="lioncub-properties-expose-<?php echo $int; ?>" <?php isset( $settings[$key]['properties_expose'][$count] ) ? checked( $settings[$key]['properties_expose'][$count], 'on' ) : ''; ?>><label for="lioncub-properties-expose-<?php echo $int; ?>">Expose property?</label>
									 &nbsp; 
									<input type="checkbox" name="lioncub[<?php echo $key; ?>][properties_enforce][<?php echo $count; ?>]" id="lioncub-properties-enforce-<?php echo $int; ?>" <?php isset( $settings[$key]['properties_enforce'][$count] ) ? checked( $settings[$key]['properties_enforce'][$count], 'on' ) : ''; ?>><label for="lioncub-properties-enforce-<?php echo $int; ?>">Enforce property?</label>

									<?php
									// $count counts ROWS of properties
									++$count;

								}
								if ( $int !== 0 && $int % 2 !== 0 ) {
									echo '</p>';
								} else {
									 echo ' &nbsp; ';
								} 

							}

						} else { ?>

							<p><input type="text" name="lioncub[<?php echo $key; ?>][properties][]" value="" class="lioncub-props">
							 &nbsp; 
							<input type="text" name="lioncub[<?php echo $key; ?>][properties][]" value="" class="lioncub-props">
							 &nbsp; 
							<input type="checkbox" name="lioncub[<?php echo $key; ?>][properties_expose][]" id="lioncub-properties-expose" class="lioncub-props"><label for="lioncub-properties-expose">Expose property?</label>
							 &nbsp; 
							<input type="checkbox" name="lioncub[<?php echo $key; ?>][properties_enforce][]" id="lioncub-properties-enforce" class="lioncub-props"><label for="lioncub-properties-enforce">Enforce property?</label></p>

						<?php } 
// @TODO FIX PROPERTIES JS ?>
					</div>
					<button type="button" onclick="AddLionCubPropField( 'properties', <?php echo $key; ?>, 'lioncub[<?php echo $key; ?>][properties][]', '', 'p' )" class="button button-secondary"><?php esc_html_e( 'Add more properties', 'lion-cub' ); ?></button>

<!-- DESTINATION -->

			<h4><?php esc_html_e( 'License File Destination', 'lion-cub' ); ?></h4>

				<p>
					<label for="lioncub-destination-filepath">File path (defaults to `/`). If this download is not a ZIP file, this should be an absolute path to a blank license file</label><br />
					<input type="text" name="lioncub[<?php echo esc_attr( $key ); ?>][filepath]" value="<?php echo esc_attr( $filepath ); ?>" id="lioncub-destination-filepath">
				</p>
				<p><label for="lioncub-destination-filename">File name (defaults to license.icl). If download is not a ZIP file, this file should be found at the absolute path listed above.</label><br />
					<input type="text" name="lioncub[<?php echo esc_attr( $key ); ?>][filename]" value="<?php echo esc_attr( $filename ); ?>" id="lioncub-destination-filename">

				</p>

			</div>
			<?php

		}


		/**
		 * Save EDD download key settings
		 *
		 * @param  int     $post_id
		 * @param  WP_Post $post
		 * @return void
		 */
		public function edd_save_download( $post_id, $post ) {

			if ( ! isset( $_POST['lioncub'] ) ) {
				return;
			}

			$s = get_post_meta( $post_id, '_lioncub', true );

			// Note: $s might contain array of settings for parent download
			if ( empty( $s ) ) {
				$s = array();
			}

			foreach ( $_POST['lioncub'] as $key => $value ) {

				if ( isset( $_POST['lioncub'][$key]['licensing'] ) ) {

					$s[$key]['licensing'] = 'on';

					if ( ! empty( $_POST['lioncub'][$key]['passphrase'] ) ) {
						$s[$key]['passphrase'] = sanitize_text_field( $_POST['lioncub'][$key]['passphrase'] );
					}


					if ( is_array( $_POST['lioncub'][$key]['header'] ) && array_filter( $_POST['lioncub'][$key]['header'] ) ) {
						$s[$key]['header'] = array_map( 'sanitize_text_field', $_POST['lioncub'][$key]['header'] );
					} else {
						unset( $s[$key]['header'] );
					}

					if ( isset( $_POST['lioncub'][$key]['duration_unit'] ) ) {
						$s[$key]['duration_unit'] = sanitize_text_field( $_POST['lioncub'][$key]['duration_unit'] );
					}

					if ( isset( $_POST['lioncub'][$key]['duration'] ) ) {
						if ( is_numeric( $_POST['lioncub'][$key]['duration'] ) ) { 
							$s[$key]['duration'] = sanitize_text_field( $_POST['lioncub'][$key]['duration'] );
						} else {
							$s[$key]['duration'] = '';
							add_settings_error( 'lioncub_notice', 'lioncub_notice', 'License expiry duration must be a number', 'error' );
						}
					}


					if ( ! empty( $_POST['lioncub'][$key]['expire_on'] ) ) {
						if ( preg_match("/^[2-9]{1}[0-9]{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $_POST['lioncub'][$key]['expire_on'] ) ) {
								$s[$key]['expire_on'] = $_POST['lioncub'][$key]['expire_on'];
						} else {
							add_settings_error( 'lioncub_notice', 'lioncub_notice', 'Expiration format MUST be yyyy-mm-dd', 'error' );
							unset( $s[$key]['duration'] );
						}
					} else {
						unset( $s[$key]['expire_on'] );
					}

					// RESTRICTIONS

					if ( ! empty( $_POST['lioncub'][$key]['restrictions'] ) ) {
						$s[$key]['restrictions'] = sanitize_text_field( $_POST['lioncub'][$key]['restrictions'] );
					} else {
						unset( $s[$key]['restrictions'] );
					}

					// PROPERTIES
					
					if ( is_array( $_POST['lioncub'][$key]['properties'] ) && array_filter( $_POST['lioncub'][$key]['properties'] ) ) {
						$s[$key]['properties'] = array();
						foreach ( $_POST['lioncub'][$key]['properties'] as $value ) {
							if ( ! empty( $value ) ) {
								$s[$key]['properties'][] = sanitize_text_field( $value );
							}
						}

						// PROPERTIES CHECKBOXES
						if ( is_array( $_POST['lioncub'][$key]['properties_expose'] ) && ! empty( $_POST['lioncub'][$key]['properties_expose'] ) ) {
							$s[$key]['properties_expose'] = array();
							foreach ( $_POST['lioncub'][$key]['properties_expose'] as $n => $value ) {
								if ( ! empty( $value ) ) {
									$s[$key]['properties_expose'][$n] = 'on';
								} else {
									$s[$key]['properties_expose'][$n] = 'off';
								}
							}
						}
						if ( is_array( $_POST['lioncub'][$key]['properties_enforce'] ) && ! empty( $_POST['lioncub'][$key]['properties_enforce'] ) ) {
							$s[$key]['properties_enforce'] = array();
							foreach ( $_POST['lioncub'][$key]['properties_enforce'] as $n => $value ) {
								if ( ! empty( $value ) ) {
									$s[$key]['properties_enforce'][$n] = 'on';
								} else {
									$s[$key]['properties_enforce'][$n] = 'off';
								}
							}
						}

					} else {
						unset( $s[$key]['properties'] );
						unset( $s[$key]['properties_expose'] );
						unset( $s[$key]['properties_enforce'] );
					}
					

					if ( ! empty( $_POST['lioncub'][$key]['filepath'] ) ) {
						$s[$key]['filepath'] = sanitize_text_field( $_POST['lioncub'][$key]['filepath'] );
					} else {
						$s[$key]['filepath'] = '/';
					}

					if ( ! empty( $_POST['lioncub'][$key]['filename'] ) ) {
						$s[$key]['filename'] = sanitize_text_field( $_POST['lioncub'][$key]['filename'] );
					}

					// CHECKBOXES
					if ( ! empty( $_POST['lioncub'][$key]['duration_expose'] ) ) {
						$s[$key]['duration_expose'] = 'on';
					} else {
						unset( $s[$key]['duration_expose'] );
					}
					if ( ! empty( $_POST['lioncub'][$key]['restrictions_expose'] ) ) {
						$s[$key]['restrictions_expose'] = 'on';
					} else {
						unset( $s[$key]['restrictions_expose'] );
					}

				} else {

					$s[$key]['licensing'] = 'off';

				}

			}


			// Finally, save accumulated settings to post meta
			update_post_meta( $post_id, '_lioncub', $s );

		}


	}

endif;

new lioncub_EDD;


