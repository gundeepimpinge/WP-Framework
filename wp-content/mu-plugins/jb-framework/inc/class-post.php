<?php
//--------------------------------------------------------------------------
// Namespace
//--------------------------------------------------------------------------
namespace JB\Framework;

//--------------------------------------------------------------------------
// Kill Script if direct file access
//--------------------------------------------------------------------------
if ( $_SERVER['SCRIPT_FILENAME'] == __FILE__ ) {
	header( 'Location: /' );
	exit;
}

//--------------------------------------------------------------------------
// Helpers to manipulate post
//--------------------------------------------------------------------------
abstract class Post {

	function __construct(){
		//Nothing here ... mostly helpers function
	}

	// Checks if a Category parent archive page is being displayed
	public static function is_parent_category( $cat_slug_or_ID ) {
		if (!is_category()) // make sure it's a category archive
			return false;
		$current = get_the_category();
		$parent  = get_category( $current[0]->category_parent );
		if ( $parent->slug == $cat_slug_or_ID || $parent->cat_ID == $cat_slug_or_ID )
			return true;
		else
			return false;
	}

	// Checks if the current post has any of the given parent categories
	public static function has_parent_category( $cat_slug_or_ID ) {
		if ( is_single() ) // make sure it's a post single
			return false;
		$current = get_the_category();
		$parent  = get_category( $current[0]->category_parent );
		if ( $parent->slug == $cat_slug_or_ID || $parent->cat_ID == $cat_slug_or_ID )
			return true;
		else
			return false;
	}

	
	// Check if the post in the loop is the last, note that you need to pass the function an int counter
	public static function is_last_post( $i ) {
		global $wp_query;
		if ( $i == $wp_query->post_count || $i == get_option( 'posts_per_page' ) )
			return true;
		return false;
	}


	//--------------------------------------------------------------------------
	// Post-Types & Archives
	//--------------------------------------------------------------------------
	// By default, WordPress does not let you to have a WYSIWYG page for a post-type archive.
	// Therefore, we create a page, put it in the menu, then load that page title and content in the post-type archive.

	// Grab the post-type archive title, according to a page
	public static function get_archive_title( $item = '' ) {
		if ( empty( $item ) ){
			global $post;
			$type = get_post_type( $post );
			$item = get_page_by_path( get_option( 'jb_base_' . $type ) );
		}
			
		if ( !empty( $item->post_title ) )
			return get_the_title( $item );
		else
			return false;
	}

	public static function archive_title( $item = '' ) {
		echo self::get_archive_title( $item ); //xss ok
	}

	// Grab the post-type archive description, according to a page
	public static function get_archive_description( $item = '' ) {
		if ( empty( $item ) ):
			global $post;
			$type = get_post_type( $post );
			$item = get_page_by_path( get_option( 'jb_base_' . $type ) );
		endif;
			
		if ( !empty( $item->post_content ) )
			return apply_filters( 'the_content', $item->post_content );
		else
			return false;
	}

	public static function archive_description( $item = '' ) {
		echo self::get_archive_description( $item ); //xss ok
	}

	// Grab the author description
	public static function get_author_description( $meta = 'description', $userID = '' ) {
		if ( empty( $user ) ):
			global $post;
			$userID = $post->post_author;
		endif;
		
		$description = get_the_author_meta( $meta, $userID );
		
		if ( !empty( $description ) )
			return apply_filters( 'the_content', $description );
		else
			return false;
	}

	public static function author_description( $meta = 'description', $userID = '' ) {
		echo self::get_author_description( $meta, $userID ); //xss ok
	}

	// Grab the taxonomy description
	public static function get_term_description( $type = 'post_tag', $therm_id = '' ) { 
		if ( $type == 'tag' )
			$type = 'post_tag'; // people tend to use tag, but the actual taxonomy title is post_tag
			
		$description = term_description( $therm_id, $type );
		
		if ( !empty( $description ) )
			return apply_filters( 'the_content', $description );
		else
			return false;
	}

	public static function term_description( $meta = 'description', $userID = '' ) {
		echo self::get_term_description( $meta, $userID ); //xss ok
	}

	public static function get_post_parent_id( $post_type = 'page', $post_parent = 0 ){
		//We make a query to get all top level parents (hub page)
		$args = array( 'post_type' => $post_type, 'posts_per_page' => -1, 'post_parent' => $post_parent );
		$parents = wp_list_pluck( get_posts( $args ), 'ID' );

		return $parents;
	}

	public static function get_post_children_id( $post_type = 'page', $post_id = 0, $order = 'DESC', $orderby = 'post_date' ){
		//We make a query to get all top level parents (hub page)
		$args = array( 'post_type' => $post_type, 'post_parent' => $post_id, 'orderby' => $orderby, 'order' => $order );
		$children = wp_list_pluck( get_children( $args ), 'ID' );
		wp_reset_postdata();
		return $children;
	}

	public static function get_page_link_by_slug( $page_slug, $post_type = 'page' ) {
		$page = get_page_by_path( $page_slug, OBJECT, $post_type );
		if ( $page )
			return get_permalink( $page->ID );
		else
			return '#';
	}

	public static function get_page_id_by_slug( $page_slug, $post_type = 'page' ) {
		$page = get_page_by_path( $page_slug, OBJECT, $post_type );
		if ( $page )
			return $page->ID;
		else
			return false;
	}

	public static function get_category_link_by_slug( $cat_slug ) {
		$cat = get_category_by_slug( $cat_slug );
		if ($cat)
			return get_category_link( $cat->term_id );
		else
			return '#';
	}

	//--------------------------------------------------------------------------
	// Metadata helpers
	//--------------------------------------------------------------------------
	public static function get_meta( $key, $page_id = null, $single = true, $filter = 'none' ) {
		global $post;

		if ( !isset( $page_id ) )
			$page_id = $post->ID;

		$meta = get_post_meta( $page_id, $key, $single );

		if ( $filter == 'wysiwyg' && ! is_array( $meta ) && ! is_object( $meta ) )
			return apply_filters( 'the_content', $meta );

		return $meta;
	}

	public static function the_meta( $key, $page_id = null, $single = true, $filter = 'none' ) {
		$meta = self::get_meta( $key, $page_id, $single, $filter );
		if ( !is_array( $meta ) && ! is_object( $meta ) )
			echo $meta; //xss ok
	}


	//--------------------------------------------------------------------------
	// Taxonomies links override
	//--------------------------------------------------------------------------
	public static function custom_taxonomies_terms_links() {
		global $post, $post_id;
		// get post by post id
		$post = &get_post( $post->ID );
		// get post type by post
		$post_type = $post->post_type;
		// get post type taxonomies
		$taxonomies = get_object_taxonomies( $post_type );
		$return     = '';
		foreach ( $taxonomies as $taxonomy ) {
			// get the terms related to post
			$terms = get_the_terms( $post->ID, $taxonomy );
			if ( !empty( $terms ) ) {
				$out = '<ul class="categories-list"><li class="categories-title">'.__( 'categories_slug','jb-project' ).'</li>';
				foreach ( $terms as $term )
					$out  .= '<li class="cat-item cat-'.$term->slug.'"><a href="'.get_term_link( $term->slug, $taxonomy ).'">'.$term->name.'</a></li>';
				$return = $out.'</ul>';
			}
		}
		return $return;
	}

}