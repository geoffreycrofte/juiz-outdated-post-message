<?php
// Prevent direct access to the plugin
if ( ! defined('ABSPATH') ) {
    exit;
}

// uninstall hook
register_uninstall_hook( JUIZ_ODPM_FILE, 'juiz_odpm_uninstaller' );
function juiz_odpm_uninstaller() {
	delete_option( JUIZ_ODPM_SETTING_NAME );
}

// activation hook
register_activation_hook( JUIZ_ODPM_FILE, 'juiz_odpm_activation' );
function juiz_odpm_activation() {
	$juiz_sma_options = get_option ( JUIZ_ODPM_SETTING_NAME );
	if ( !is_array($juiz_sma_options) ) {
		
		$default_array = array(
			'delay_before_outdated'	=> 360,
			'post_type_concerned' 	=> array('post'),
			'where_to_display' 		=> 'top',
			'hide_metabox'	 		=> 'n',
			'outdated_message' 		=> esc_html__( 'This content has ##. Please, read this page keeping its age in your mind.', 'juiz-outdated-post-message' )
		);
		
		update_option( JUIZ_ODPM_SETTING_NAME , $default_array );
	}
}

// description setting page
if ( ! function_exists( 'juiz_odpm_plugin_action_links' ) ) {
	add_filter( 'plugin_action_links_' . plugin_basename( JUIZ_ODPM_FILE ), 'juiz_odpm_plugin_action_links',  10, 2 );
	function juiz_odpm_plugin_action_links( $links, $file ) {
		$links[] = '<a href="' . admin_url( 'options-general.php?page=' . JUIZ_ODPM_SLUG ) . '">' . esc_html__('Settings') . '</a>';
		return $links;
	}
}

/*
 * Options page
 */
// Settings page in admin menu
if ( ! function_exists( 'add_juiz_odpm_settings_page' ) ) {

	add_action( 'admin_menu', 'add_juiz_odpm_settings_page' );

	function add_juiz_odpm_settings_page() {
		add_submenu_page( 
			'options-general.php', 
			esc_html__( 'Outdated Post Message', 'juiz-outdated-post-message' ),
			esc_html__( 'Outdated Post Message', 'juiz-outdated-post-message' ),
			'administrator',
			JUIZ_ODPM_SLUG,
			'juiz_odpm_settings_page' 
		);
	}
}

// Some styles for settings page in admin
if ( ! function_exists( 'juiz_odpm_custom_admin_header' ) ) {
	add_action( 'admin_head-settings_page_' . JUIZ_ODPM_SLUG, 'juiz_odpm_custom_admin_header' );
	function juiz_odpm_custom_admin_header() {
		include_once( 'jodpm-admin-styles-scripts.php' );
	}
}

/*
 *****
 ***** Section for Metabox
 *****
 */
if ( ! function_exists( 'juiz_odpm_metaboxes' ) ) {
	
	$options = get_option( JUIZ_ODPM_SETTING_NAME );

	if ( $options['hide_metabox'] === 'n' ) {
		add_action( 'add_meta_boxes', 'juiz_odpm_metaboxes' );
	}
	function juiz_odpm_metaboxes() {

		$options = get_option( JUIZ_ODPM_SETTING_NAME );
		$pts	 = get_post_types( array( 'public'=> true, 'show_ui' => true, '_builtin' => true ) );
		$cpts	 = get_post_types( array( 'public'=> true, 'show_ui' => true, '_builtin' => false ) );
		$all_pts = array_merge($pts, $cpts);

		if ( is_array( $options['post_type_concerned'] ) ) {
			foreach ( $all_pts as $pt ) {
				if ( in_array( $pt, $options['post_type_concerned'] ) ) {
					add_meta_box( 'jodpm_options', esc_html__( 'Outdated message Options', 'juiz-outdated-post-message' ), 'jodpm_options', $pt, 'side', 'default' );
				}
			}
		}
	}
}
// build the metabox
if ( ! function_exists( 'jodpm_options' ) ) {
	function jodpm_options( $post ) {
		$options 	= get_option( JUIZ_ODPM_SETTING_NAME );
		$post_meta 	= get_post_meta( $post->ID,'_jodpm_metabox_options', true );
		$checked 	= is_array( $post_meta ) && $post_meta['hide'] == 'on' ? ' checked="checked"' : '';
		$custom_d 	= is_array( $post_meta ) ? intval( $post_meta['custom_delay'] ) : 0;

		echo '<p><input id="jodpm_metabox_hide_message" type="checkbox"' . $checked . ' name="jodpm_metabox_hide_message" /> <label for="jodpm_metabox_hide_message">' . esc_html__( 'Never show the outdated message for this post', 'juiz-outdated-post-message' ) . '</label></p>';
		echo '<p><label for="jodpm_metabox_custom_delay" style="font-weight:bold;">' . esc_html__( 'This post delay:', 'juiz-outdated-post-message' ) . '</label> <input id="jodpm_metabox_custom_delay" style="width:65px" type="number" name="jodpm_metabox_custom_delay" step="1" min="0" value="' . $custom_d . '" />' . esc_html__( 'days', 'juiz-outdated-post-message' ) . '</p>
			<p class="howto">' . sprintf( esc_html__( 'Keep 0 to use global value (%s)', 'juiz-outdated-post-message' ), $options['delay_before_outdated'] . '&nbsp;' . esc_html__( 'days', 'juiz-outdated-post-message' ) ) . '</p>';
	}
}
// save datas
if ( ! function_exists( 'jodpm_save_metabox' ) ) {
	add_action( 'save_post', 'jodpm_save_metabox' );
	function jodpm_save_metabox( $post_ID ) {
		$datas['hide'] = isset( $_POST['jodpm_metabox_hide_message'] ) ? 'on' : 'off';
		$datas['custom_delay'] = isset( $_POST['jodpm_metabox_custom_delay'] ) && intval( $_POST['jodpm_metabox_custom_delay'] ) >= 0 ? intval( $_POST['jodpm_metabox_custom_delay'] ) : 0;

		update_post_meta( $post_ID, '_jodpm_metabox_options', $datas );
	}
}



/*
 *****
 ***** Sections and fields for settings
 *****
 */

function add_juiz_odpm_plugin_options() {

	// all options in single registration as array
	register_setting( JUIZ_ODPM_SETTING_NAME, JUIZ_ODPM_SETTING_NAME, 'juiz_odpm_sanitize' );


	add_settings_section('juiz_odpm_plugin_basics', esc_html__( 'General Settings', 'juiz-outdated-post-message' ), 'juiz_odpm_section_text_basics', JUIZ_ODPM_SLUG );
	add_settings_field('juiz_odpm_delay', '<label for="juiz_odpm_delay">' . esc_html__('Delay before outdated:', 'juiz-outdated-post-message').'</label>', 'juiz_odpm_setting_delay', JUIZ_ODPM_SLUG, 'juiz_odpm_plugin_basics');
	add_settings_field('juiz_odpm_message', '<label for="juiz_odpm_message">' . esc_html__('Your message:', 'juiz-outdated-post-message').'</label>', 'juiz_odpm_setting_message', JUIZ_ODPM_SLUG, 'juiz_odpm_plugin_basics');
	add_settings_field('juiz_odpm_temp_submit_1', get_submit_button( esc_html__('Save Changes'), 'secondary'), function() {return "";}, JUIZ_ODPM_SLUG, 'juiz_odpm_plugin_basics');


	add_settings_section('juiz_odpm_plugin_display_in', esc_html__('Display settings','juiz-outdated-post-message'), 'juiz_odpm_section_text_display', JUIZ_ODPM_SLUG);
	add_settings_field('juiz_odpm_display_in_types', esc_html__('Display message in:','juiz-outdated-post-message'), 'juiz_odpm_setting_checkbox_content_type', JUIZ_ODPM_SLUG, 'juiz_odpm_plugin_display_in');
	add_settings_field('juiz_odpm_display_where', esc_html__('Display message to the:','juiz-outdated-post-message'), 'juiz_odpm_setting_radio_where', JUIZ_ODPM_SLUG, 'juiz_odpm_plugin_display_in');
	add_settings_field('juiz_odpm_temp_submit_2', get_submit_button( esc_html__('Save Changes'), 'secondary'), function() {return "";}, JUIZ_ODPM_SLUG, 'juiz_odpm_plugin_display_in');


	add_settings_section('juiz_odpm_plugin_metaboxes', esc_html__('Metabox Settings','juiz-outdated-post-message'), 'juiz_odpm_section_text_metaboxes', JUIZ_ODPM_SLUG);
	add_settings_field('juiz_odpm_hide_metabox', esc_html__('Hide the metabox?','juiz-outdated-post-message'), 'juiz_odpm_setting_hide_metabox', JUIZ_ODPM_SLUG, 'juiz_odpm_plugin_metaboxes');

}
add_filter('admin_init','add_juiz_odpm_plugin_options');


/*
 * Basics section text
 */
if ( ! function_exists( 'juiz_odpm_section_text_basics' ) ) {
function juiz_odpm_section_text_basics() {
	echo '<p class="juiz_odpm_section_intro">' . esc_html__( 'Set general options for your outdated message.', 'juiz-outdated-post-message' ) .'</p>';
}
}

if ( ! function_exists( 'juiz_odpm_setting_delay' ) ) {
function juiz_odpm_setting_delay() {

	$options = get_option( JUIZ_ODPM_SETTING_NAME );

	echo '<p><input type="number" name="' . JUIZ_ODPM_SETTING_NAME . '[delay_before_outdated]" id="juiz_odpm_delay" value="' . $options['delay_before_outdated'] . '" class="juiz_short_input" step="1" min="1">' . esc_html__( 'days', 'juiz-outdated-post-message' ) . '</p>';
}
}

if ( ! function_exists( 'juiz_odpm_setting_message' ) ) {
function juiz_odpm_setting_message() {
	$options = get_option( JUIZ_ODPM_SETTING_NAME );
	echo '<p><textarea class="juiz_long_input" name="' . JUIZ_ODPM_SETTING_NAME . '[outdated_message]" id="juiz_odpm_message">' . $options['outdated_message'] . '</textarea><br><em>' . sprintf( esc_html__( 'You can use the %s##%s variable to insert the time passed since the publication date.', 'juiz-outdated-post-message' ), '<strong>', '</strong>' ) . '</em></p>';
}
}


/*
 * Advanced section text
 */
if ( ! function_exists( 'juiz_odpm_section_text_display' ) ) {
function juiz_odpm_section_text_display() {
	echo '<p class="juiz_odpm_section_intro">' . esc_html__( 'Choose where you want to display the outdated message on your posts', 'juiz-outdated-post-message' ) . '</p>';
}
}
// checkbox for type of content
if ( ! function_exists('juiz_odpm_setting_checkbox_content_type' ) ) {
function juiz_odpm_setting_checkbox_content_type() {

	$pts	= get_post_types( array('public'=> true, 'show_ui' => true, '_builtin' => true) );
	$cpts	= get_post_types( array('public'=> true, 'show_ui' => true, '_builtin' => false) );

	$options = get_option( JUIZ_ODPM_SETTING_NAME );

	$all_lists_icon = '<span class="dashicons-before dashicons-editor-ul"></span>';
	$all_lists_selected = '';
	if ( is_array( $options['post_type_concerned'] ) ) {
		$all_lists_selected = in_array( 'all_lists', $options['post_type_concerned'] ) ? 'checked="checked"': '';
	}

	if ( is_array( $options ) && isset( $options['post_type_concerned'] ) && is_array( $options['post_type_concerned'] ) ) {
		
		global $wp_post_types;
		$no_icon = '<span class="jodpm_no_icon">&#160;</span>';

		// classical post type listing
		foreach ( $pts as $pt ) {

			$selected = in_array( $pt, $options['post_type_concerned'] ) ? 'checked="checked"' : '';
			
			$icon = '';

			switch( $wp_post_types[ $pt ]->name ) {
				case 'post' :
					$icon = 'dashicons-before dashicons-admin-post';
					break;
				case 'page' :
					$icon = 'dashicons-before dashicons-admin-page';
					break;
				case 'attachment' :
					$icon = 'dashicons-before dashicons-admin-media';
					break;
			}

			echo '<p><input type="checkbox" name="' . JUIZ_ODPM_SETTING_NAME . '[post_type_concerned][]" id="' . $pt . '" value="' . $pt . '" ' . $selected . '> <label for="' . $pt . '"><span class="' . $icon . '"></span>&nbsp;' . $wp_post_types[ $pt ]->label . '</label></p>';
		}

		// custom post types listing
		if ( is_array( $cpts ) && ! empty( $cpts ) ) {
			foreach ( $cpts as $cpt ) {

				$selected = in_array( $cpt, $options['post_type_concerned'] ) ? 'checked="checked"' : '';
				$icon = $no_icon;
				
				// image & dashicons supports
				if ( isset( $wp_post_types[ $cpt ]->menu_icon ) && $wp_post_types[ $cpt ]->menu_icon ) {
					$icon = preg_match('#dashicons#', $wp_post_types[ $cpt ]->menu_icon) ?
							'<span class="dashicons ' . $wp_post_types[ $cpt ]->menu_icon . '"></span>'
							:
							'<img alt="&#8226;" src="' . esc_url( $wp_post_types[ $cpt ]->menu_icon ) . '" />';
				}

				echo '<p><input type="checkbox" name="' . JUIZ_ODPM_SETTING_NAME . '[post_type_concerned][]" id="' . $cpt . '" value="' . $cpt . '" ' . $selected . '> <label for="' . $cpt . '">' . $icon . ' ' . $wp_post_types[ $cpt ]->label . '</label></p>';
			}
		}
	}
}
}

// where display buttons
// radio fields styles choice
if ( ! function_exists( 'juiz_odpm_setting_radio_where' ) ) {
	function juiz_odpm_setting_radio_where() {

		$options = get_option( JUIZ_ODPM_SETTING_NAME );

		$w_bottom = $w_top = $w_both = $w_nowhere = "";
		if ( is_array($options) && isset($options['where_to_display']) )
			${'w_'.$options['where_to_display']} = " checked='checked'";
		
		echo '	<input id="jodpm_w_b" value="bottom" name="' . JUIZ_ODPM_SETTING_NAME . '[where_to_display]" type="radio" ' . $w_bottom . ' />
				<label for="jodpm_w_b">' . esc_html__( 'Content bottom', 'juiz-outdated-post-message' ) . '</label>
				
				<input id="jodpm_w_t" value="top" name="'.JUIZ_ODPM_SETTING_NAME.'[where_to_display]" type="radio" '.$w_top.' />
				<label for="jodpm_w_t">'. esc_html__( 'Content top', 'juiz-outdated-post-message' ) . '</label>
				
				<input id="jodpm_w_2" value="both" name="' . JUIZ_ODPM_SETTING_NAME . '[where_to_display]" type="radio" ' . $w_both . ' />
				<label for="jodpm_w_2">' . esc_html__( 'Both', 'juiz-outdated-post-message' ) . '</label>

				<input id="jodpm_w_0" value="nowhere" name="'.JUIZ_ODPM_SETTING_NAME.'[where_to_display]" type="radio" '.$w_nowhere.' />
				<label for="jodpm_w_0">' . esc_html__( 'I\'m a ninja, I want to use the shortcode only!', 'juiz-outdated-post-message' ) . '</label>';
	}
}


/*
 * Metaboxes section text
 */
if ( ! function_exists( 'juiz_odpm_section_text_metaboxes' ) ) {
function juiz_odpm_section_text_metaboxes() {
	echo '<p class="juiz_odpm_section_intro">' . esc_html__('You can hide metabox added by this plugin in your posts\' edition pages', 'juiz-outdated-post-message' ) . '</p>';
}
}
// radio fields styles choice
if ( ! function_exists( 'juiz_odpm_setting_hide_metabox' ) ) {
	function juiz_odpm_setting_hide_metabox() {

		$options = get_option( JUIZ_ODPM_SETTING_NAME );

		$this_y = $this_n = "";
		if ( is_array( $options ) && isset( $options['hide_metabox'] ) )
			${'this_' . $options['hide_metabox']} = " checked='checked'";
		
		echo '	<input id="jodpm_hide_y" value="y" name="' . JUIZ_ODPM_SETTING_NAME . '[hide_metabox]" type="radio" ' . $this_y . ' />
				<label for="jodpm_hide_y">' . esc_html__( 'Yes', 'juiz-outdated-post-message' ) . '</label>
				
				<input id="jodpm_hide_n" value="n" name="' . JUIZ_ODPM_SETTING_NAME . '[hide_metabox]" type="radio" ' . $this_n . ' />
				<label for="jodpm_hide_n">' . esc_html__( 'No', 'juiz-outdated-post-message' ) . '</label>';
	}
}




/*
 * sanitize posted data
 */
function juiz_odpm_sanitize( $options ) {

	$newoptions['outdated_message']		= $options['outdated_message'];
	$newoptions['hide_metabox']			= in_array( $options['hide_metabox'], array( 'y', 'n' ) ) ? $options['hide_metabox'] : 'n';
	$newoptions['delay_before_outdated']= intval( $options['delay_before_outdated'] );
	$newoptions['where_to_display']		= in_array( $options['where_to_display'], array( 'bottom', 'top', 'both', 'nowhere' ) ) ? $options['where_to_display'] : 'top';

	if ( is_array( $options['post_type_concerned'] ) && count( $options['post_type_concerned'] ) > 0 ) {
		$newoptions['post_type_concerned'] = $options['post_type_concerned'];
	}
	else {
		wp_redirect( admin_url( 'options-general.php?page=' . JUIZ_ODPM_SLUG . '&message=1337' ) );
		exit;
	}
	
	return $newoptions;
}






/*
 *****
 ***** Settings page
 *****
 */
if ( ! function_exists( 'juiz_odpm_settings_page' ) ) {
	function juiz_odpm_settings_page() {
		?>
		<div id="juiz-odpm" class="wrap">
			<div id="icon-options-general" class="icon32">&nbsp;</div>
			<h2><?php esc_html_e( 'Manage Juiz Outdated Post Message', 'juiz-outdated-post-message' ) ?> <small>v. <?php echo JUIZ_ODPM_VERSION; ?></small></h2>

			<?php if ( isset( $_GET['message'] ) && $_GET['message'] == '1337' ) { ?>
			<div class="error settings-error">
				<p>
					<strong><?php echo sprintf( esc_html__( 'You must chose at least one %stype of content%s.', 'juiz-outdated-post-message' ), '<a href="#post">', '</a>' ); ?></strong><br>
					<em><?php esc_html_e( 'If you want to deactivate the outdated message, be a ninja!','juiz-outdated-post-message' ); ?></em></p>
			</div>
			<?php } ?>
			<p class="jodpm_info">
				<?php echo sprintf( esc_html__( 'You can use %s[outdated]%s shortcode to display the outdated message.','juiz-outdated-post-message' ), '<code>','</code>'); ?>
				<?php esc_html_e('Be careful, this shortcode use the global settings below.', 'juiz-outdated-post-message'); ?>
			</p>
			<form method="post" action="options.php">
				<?php
					settings_fields( JUIZ_ODPM_SETTING_NAME );
					do_settings_sections( JUIZ_ODPM_SLUG );
					submit_button();
				?>
			</form>

			<p class="juiz_bottom_links">
				<em><?php esc_html_e('Like it? Support this plugin! Thank you.', 'juiz-outdated-post-message') ?></em>
				<a class="juiz_paypal" target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&amp;business=P39NJPCWVXGDY&amp;lc=FR&amp;item_name=Juiz%20Outdated%20Post%20Message%20%2d%20WP%20Plugin&amp;item_number=%23wp%2djodpm&amp;currency_code=EUR&amp;bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted"><?php esc_html_e('Donate', 'juiz-outdated-post-message') ?></a>

				<a class="juiz_twitter" target="_blank" href="https://twitter.com/intent/tweet?source=webclient&amp;hastags=WordPress,Plugin&amp;text=Juiz%20Outdated%20Post%20Message%20lets%20know%20your%20visitors%20when%20a%20post%20is%20outdated!%20Try%20it!&amp;url=https://wordpress.org/plugins/juiz-outdated-post-message/&amp;related=geoffrey_crofte&amp;via=geoffrey_crofte"><?php esc_html_e('Tweet it', 'juiz-outdated-post-message') ?></a>

				<a class="juiz_rate" target="_blank" href="https://wordpress.org/support/view/plugin-reviews/juiz-outdated-post-message/"><?php esc_html_e('Rate it', 'juiz-outdated-post-message') ?></a>
				<a href="https://flattr.com/submit/auto?user_id=CreativeJuiz&amp;url=https://wordpress.org/plugins/juiz-outdated-post-message/&amp;title=Juiz%20Outdated%20Post%20Message%20-%20WordPress%20Plugin&amp;description=Awesome%20WordPress%20Plugin%20helping%20your%20visitor%20to%20know%20when%20a%20post%20is%20outdated.%20Control%20the%20time%20very%20easily&amp;tags=WordPress,Outdated,Post,Message,Visitors&amp;category=software" lang="en" hreflang="en"><img src="https://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this!" width="93" height="20" style="vertical-align:-6px;"></a>
			</p>
		</div>
		<?php
	}
}