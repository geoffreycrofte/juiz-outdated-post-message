<?php
/*
Plugin Name: Juiz OutDated Post Message
Description: Add a message when your post is out to date (according to your own criteria)
Author: Geoffrey Crofte
Version: 1.0.5
Author URI: http://geoffrey.crofte.fr
License: GPLv2 or later
Text domain: juiz-outdated-post-message
Domain Path: /languages

Copyright 2014  Geoffrey Crofte  (email : support@creativejuiz.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Prevent direct access to the plugin
if ( ! defined('ABSPATH') ) {
    exit;
}

define( 'JUIZ_ODPM_PLUGIN_NAME',	'Juiz Outdated Post Message' );
define( 'JUIZ_ODPM_VERSION',		'1.0.5' );
define( 'JUIZ_ODPM_FILE',		     __FILE__ );
define( 'JUIZ_ODPM_DIRNAME',		basename( dirname( __FILE__ ) ) );
define( 'JUIZ_ODPM_PLUGIN_URL',		trailingslashit( WP_PLUGIN_URL ) . JUIZ_ODPM_DIRNAME );
define( 'JUIZ_ODPM_SLUG',			'juiz-outdated-post-message' );
define( 'JUIZ_ODPM_SETTING_NAME',	'jodpm_settings' );
define( 'JUIZ_ODPM_SETTINGS_URL', 	admin_url('options-general.php?page='.JUIZ_ODPM_SLUG) );

// multilingue
add_action( 'init', 'make_juiz_odpm_multilang' );
function make_juiz_odpm_multilang() {
	load_plugin_textdomain( 'juiz-outdated-post-message', false, JUIZ_ODPM_DIRNAME . '/languages' );
}

if ( is_admin() || ( defined( 'DOING_AJAX' ) && ! DOING_AJAX ) ) {
	include( 'admin/jodpm-admin.inc.php' );
} // end if is_admin

// add styles 
if ( ! function_exists( 'juiz_odpm_add_styles' ) ) {
	if ( FALSE === apply_filters( 'juiz_odpm_deactivate_styles', FALSE ) ) {
		add_action ( 'wp_enqueue_scripts', 'juiz_odpm_add_styles' );

		function juiz_odpm_add_styles() {
			wp_enqueue_style( 'juiz_outdated_post_message', plugins_url( JUIZ_ODPM_DIRNAME . '/css/juiz-odpm-styles.css'), array(), JUIZ_ODPM_VERSION, 'all' );
		}
	}
}

// function to get the message
if ( ! function_exists( 'get_odpm_outdated_message' ) ) {
	function get_odpm_outdated_message( $shortcode = false ) {
		global $post;

		$post_meta 	= get_post_meta( $post->ID, '_jodpm_metabox_options', true );
		$show_me 	=  isset( $post_meta['hide'] ) && $post_meta['hide'] === 'on' ? false : true;
		$custom_delay = isset( $post_meta['custom_delay'] ) ? intval( $post_meta['custom_delay'] ) : 0;

		// if hide option is not checked for this post
		if ( $show_me ) {

			$options 	= get_option( JUIZ_ODPM_SETTING_NAME );

			$where 		= in_array( $options['where_to_display'], array( 'top', 'bottom', 'both', 'nowhere' ) ) ? $options['where_to_display'] : 'top';
			$delay 		= $custom_delay === 0 ? intval( $options['delay_before_outdated'] ) : $custom_delay;
			$post_types = is_array( $options['post_type_concerned'] ) ? $options['post_type_concerned'] : array('post');
			$message 	= trim( strip_tags( $options['outdated_message'] ) ); //string
			$now 		= time();
			$date_article = get_post_time( 'U', false, $post -> ID );

			$seconds_between = $now - $date_article;
			$days_between = round( $seconds_between / 60 / 60 / 24, 0, PHP_ROUND_HALF_ODD );

			if ( in_array( get_post_type( $post -> ID ), $post_types ) && ( $where !== 'nowhere' || ( $where === 'nowhere' && $shortcode ) ) && $days_between >= $delay ) {
				
				$div 	 		= strip_tags( apply_filters( 'juiz_odpm_container', 'div' ) );
				$classes 		= esc_attr( apply_filters( 'juiz_odpm_container_classes', '' ) );
				$classes 		= $classes !== '' ? ' '.$classes : '';
				$classes_top 	= esc_attr( apply_filters( 'juiz_odpm_container_classes_top', 'jodpm-top' ) );
				$classes_top 	= $classes_top !== '' ? ' '.$classes_top : '';
				$classes_bott 	= esc_attr( apply_filters( 'juiz_odpm_container_classes_bottom', 'jodpm-bottom' ) );
				$classes_bott 	= $classes_bott !== '' ? ' '.$classes_bott : '';
				$id_top  		= esc_attr( apply_filters( 'juiz_odpm_container_id_top', '' ) );
				$id_top	 		= $id_top !== '' ? ' id="'.$id_top.'"' : '';
				$id_bott 		= esc_attr( apply_filters( 'juiz_odpm_container_id_bottom', '' ) );
				$id_bott 		= $id_bott !== '' ? ' id="'.$id_bott.'"' : '';
				$before 		= apply_filters( 'juiz_odpm_before_message', '' );
				$after 			= apply_filters( 'juiz_odpm_after_message', '' );


				$message 	 = preg_replace( "%##%", human_time_diff( $date_article ), $message );
				$message_sc  = '<' . $div . ' class="juiz-outdated-message' . esc_attr( $classes ) . '">' . $before . $message . $after . '</' . $div . '>';
				$message_top = '<' . $div . ' class="juiz-outdated-message' . esc_attr( $classes . $classes_top ) . '"' . $id_top . '>' . $before . $message . $after . '</' . $div . '>';
				$message_bott= '<' . $div . ' class="juiz-outdated-message' . esc_attr( $classes . $classes_bott ) . '"' . $id_bott . '>' . $before . $message . $after . '</' . $div . '>';

				return array(
					'message_top'		=> $message_top,
					'message_bottom'	=> $message_bott,
					'message_sc'		=> $message_sc,
					'where'				=> $where
				);
			}
			else return null;

		}
		// if hide option is activated, return nothing
		else return null;
	}
}

// add outdated message
if ( ! function_exists( 'juiz_odpm_add_outdated_message' ) ) {
	add_action( 'the_content', 'juiz_odpm_add_outdated_message' );

	function juiz_odpm_add_outdated_message( $content ) {

		// return `null` if :
		// * hide option for the current post is activated
		// * is not outdated thanks to criteria
		// * is not the right type of post
		$outdated_message = get_odpm_outdated_message();

		if ( null === $outdated_message) {
			return $content;
		}
		else {
			$message_top = $outdated_message['message_top'];
			$message_bott= $outdated_message['message_bottom'];
			$where 		 = $outdated_message['where'];
			$newcontent  = $content;

			if ( $where === 'top' || $where === 'both' ) {
				$newcontent = $message_top . $newcontent;
			}
			if ( $where === 'bottom' || $where === 'both' ) {
				$newcontent = $newcontent . $message_bott;	
			}

			return $newcontent;
		}
	}
}


// create new shortcode
if ( ! function_exists( 'juiz_odpm_shortcode' ) ) {
	function juiz_odpm_shortcode( $atts ){
		$message = get_odpm_outdated_message( true );
		return apply_filters( 'juiz_odpm_shortcode', $message['message_sc'] );
	}
	add_shortcode( 'outdated', 'juiz_odpm_shortcode' );
}

// template function
if ( ! function_exists( 'get_juiz_odpm_message' ) ) {
	function get_juiz_odpm_message(){
		return do_shortcode('[outdated]');
	}
}
if ( ! function_exists( 'juiz_odpm_message' ) ) {
	function juiz_odpm_message(){
		echo get_juiz_odpm_message();
	}
}
