<?php

	function lioncub_file_get_contents( $file, $silent = 'no', $input = 'no' ) {

  		switch( $input ) {
    			// Returns input stream
    			case 'yes':
      			return file_get_contents( $file );
      			break;
    			// Standard
    			default:
     	 		if ( @file_exists( $file ) ) {
       	 			return trim( file_get_contents( $file ) );
      			}
      			return ('no' === $silent ? $file . ' template is missing' : '' );
     			break;
  		}

	}

/*

	function io_mswIP() {
 	 	$ips = array();
  		$types = array(
    			'HTTP_CLIENT_IP',
    			'HTTP_X_FORWARDED_FOR',
    			'HTTP_X_FORWARDED',
    			'HTTP_X_CLUSTER_CLIENT_IP',
    			'HTTP_FORWARDED_FOR',
   			'HTTP_FORWARDED',
    			'REMOTE_ADDR'
  		);
  		foreach ( $types AS $key ) {
    			if ( array_key_exists( $key, $_SERVER ) === true ) {
      			foreach ( array_map( 'trim', explode( ',', $_SERVER[$key] ) ) AS $ipA ) {
        				if ( ! in_array( $ipA, $ips ) && filter_var( $ipA, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ) {
          				$ips[] = $ipA;
        				} else {
          				// Double check for localhost..
          				if ( ! in_array( $ipA, $ips ) && in_array( $ipA, array( '::1','127.0.0.1' ) ) ) {
           		 			$ips[] = $ipA;
          				}
        				}
      			}
    			}
  		}
  		return ( ! empty( $ips ) ? implode( ',', $ips ) : '' );
	}
*/

	function lioncub_special_chars( $data ) {

  		$data = htmlspecialchars( $data );
  		$data = str_replace( '&amp;#', '&#', $data );
  		$data = str_replace( '&amp;amp;', '&amp;', $data );
  		return $data;

	}


	function lioncub_new_line( $br = 1 ) {

  		if ( defined( 'PHP_EOL' ) ) {
    			if ( $br > 1 ) {
      			return str_repeat( PHP_EOL, $br );
    			}
    			return PHP_EOL;
  		}
  		$nl = "\r\n";
  		if ( isset( $_SERVER["HTTP_USER_AGENT"] ) && strstr( strtolower( $_SERVER["HTTP_USER_AGENT"] ), 'win' ) ) {
    				$nl = "\r\n";
  		} else if ( isset( $_SERVER["HTTP_USER_AGENT"] ) && strstr( strtolower( $_SERVER["HTTP_USER_AGENT"] ), 'mac' ) ) {
   	 		$nl = "\r";
  		} else {
    			$nl = "\n";
  		}
  		if ( $br > 1 ) {
    			return str_repeat( $nl, $br );
  		}
  		return $nl;

	}
