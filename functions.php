<?php

function lioncub_file_get_contents( $file, $silent = 'no', $input = 'no' ) {

	switch( $input ) {
		// Returns input stream
		case 'yes':
			return file_get_contents( $file );
		// Standard
		default:
			if ( @file_exists( $file ) ) {
				return trim( file_get_contents( $file ) );
			}
			return ('no' === $silent ? $file . ' template is missing' : '' );
	}

}

function lioncub_special_chars( $data ) {

	$data = htmlspecialchars( $data );
	$data = str_replace( '&amp;#', '&#', $data );
	return str_replace( '&amp;amp;', '&amp;', $data );

}

/**
 * Return appropriate new line style
 *
 * @param int $br
 * @return string
 */
function lioncub_new_line( $br = 1 ) {

	if ( defined( 'PHP_EOL' ) ) {
		if ( $br > 1 ) {
			return str_repeat( PHP_EOL, $br );
		}
		return PHP_EOL;
	}
	$nl = "\n";
	if ( isset( $_SERVER["HTTP_USER_AGENT"] ) && strstr( strtolower( $_SERVER["HTTP_USER_AGENT"] ), 'win' ) ) {
		$nl = "\r\n";
	} else if ( isset( $_SERVER["HTTP_USER_AGENT"] ) && strstr( strtolower( $_SERVER["HTTP_USER_AGENT"] ), 'mac' ) ) {
		$nl = "\r";
	}
	if ( $br > 1 ) {
		return str_repeat( $nl, $br );
	}
	return $nl;

}
