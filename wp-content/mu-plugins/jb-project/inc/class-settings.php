<?php
//--------------------------------------------------------------------------
// Namespace
//--------------------------------------------------------------------------
namespace JB\Project;
use JB\Framework\Settings as Framework_Settings;

//--------------------------------------------------------------------------
// Kill Script if direct file access
//--------------------------------------------------------------------------
if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) {
	header( 'Location: /' );
	exit;
}

//--------------------------------------------------------------------------
// Settings Class
//--------------------------------------------------------------------------
abstract class Settings extends Framework_Settings {

	function __construct() {
		parent::__construct();
	}

	//--------------------------------------------------------------------------
	// Settings API
	//--------------------------------------------------------------------------
	public function settings( $settings_fields = array() ) {

		$project_settings_fields = array(
			array(
				'id' 					=> 'jb_multilingual_link',
				'title'					=> __( 'Multilingual Association', 'jb' ), 
				'section'				=> 'jb_multilingual',
				'callback'				=> array( $this, 'select_multilingual_render' ),
				'args'					=> array( 'id' => 'jb_multilingual_link' ),
			),
		);

		parent::settings( array_merge( $project_settings_fields, $settings_fields ) );

		add_settings_section( 'jb_multilingual', __( 'Multilingual settings', 'jb-project' ), '', 'jb_settings' );
	}


	public function select_multilingual_render( $args ) {
		global $wpdb;
		$current_blog = get_current_blog_id();

		echo '<select name="'.$args['id'].'">';

		$blogs = $wpdb->get_results(
			"SELECT blog_id
			FROM {$wpdb->blogs}
			WHERE site_id = '{$wpdb->siteid}'
			AND spam = '0'
			AND deleted = '0'
			AND archived = '0'
			AND blog_id != {$current_blog}"
		);

		$sites = array();
		foreach ( $blogs as $blog ) {
			$sites[$blog->blog_id] = get_blog_option( $blog->blog_id, 'blogname' );
		}

		$sites = array( 'none' => 'None' ) + $sites;
		foreach ( $sites as $blog_id => $blog_title ) {
			$selected = ( get_option( $args['id'] ) == $blog_id ) ? 'selected' : '';
			echo '<option value="' . $blog_id . '" ' . $selected . '>' . $blog_title . '</option>';
		}
		echo '</select>';
	}

}