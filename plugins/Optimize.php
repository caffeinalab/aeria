<?php
/**
 * @Author: Graziano Vincini <graziano.vincini@caffeinalab.com>
 * @Date:   2015-02-04 16:18:04
 * @Last Modified by:   Graziano Vincini
 * @Last Modified time: 2015-02-04 16:23:59
 */

// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

if(false===defined('WP_POST_REVISIONS')) define('WP_POST_REVISIONS', false );

// Remove unneeded widgets that have undesirable query overhead
add_action( 'widgets_init', function() {
    unregister_widget('WP_Widget_Pages');
    unregister_widget('WP_Widget_Calendar');
    unregister_widget('WP_Widget_Tag_Cloud');
    unregister_widget('WP_Nav_Menu_Widget');
});

add_action('init',function(){

    add_filter('index_rel_link',            '__return_false' );
    add_filter('parent_post_rel_link',      '__return_false' );
    add_filter('start_post_rel_link',       '__return_false' );
    add_filter('previous_post_rel_link',    '__return_false' );
    add_filter('next_post_rel_link',        '__return_false' );

    // remove junk from head
    remove_action('wp_head', 'rel_canonical');
    remove_action('wp_head', 'feed_links_extra', 3 ); // Display the links to the extra feeds such as category feeds
    remove_action('wp_head', 'feed_links', 2 ); // Display the links to the general feeds: Post and Comment Feed
    remove_action('wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
    remove_action('wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
    remove_action('wp_head', 'index_rel_link' ); // index link
    remove_action('wp_head', 'parent_post_rel_link', 10, 0 ); // prev link
    remove_action('wp_head', 'start_post_rel_link', 10, 0 ); // start link
    remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0 ); // Display relational links for the posts adjacent to the current post.
    remove_action('wp_head', 'wp_generator' ); // Display the XHTML generator that is generated on the wp_head hook, WP version
    remove_action('wp_head', 'wp_shortlink_wp_head' );

    // remove_all_admin_bar_items
    remove_action( 'admin_bar_menu', 'wp_admin_bar_my_account_menu', 10 );
    remove_action( 'admin_bar_menu', 'wp_admin_bar_my_sites_menu', 20 );
    remove_action( 'admin_bar_menu', 'wp_admin_bar_edit_menu', 30 );
    remove_action( 'admin_bar_menu', 'wp_admin_bar_shortlink_menu', 80 );
    remove_action( 'admin_bar_menu', 'wp_admin_bar_updates_menu', 70 );
    remove_action( 'admin_bar_menu', 'wp_admin_bar_new_content_menu', 40 );
    remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 50 );
    remove_action( 'admin_bar_menu', 'wp_admin_bar_appearance_menu', 60 );

    // Disable RSS feeds
    function __disable_feed(){static $url = null; if(null===$url) $url = get_bloginfo('url'); wp_redirect($url);}
    add_action('do_feed',               '__disable_feed', 1);
    add_action('do_feed_rdf',           '__disable_feed', 1);
    add_action('do_feed_rss',           '__disable_feed', 1);
    add_action('do_feed_rss2',          '__disable_feed', 1);
    add_action('do_feed_atom',          '__disable_feed', 1);
    add_action('do_feed_rss2_comments', '__disable_feed', 1);
    add_action('do_feed_atom_comments', '__disable_feed', 1);

    // optimize_rewrites
	add_filter('rewrite_rules_array', function($rules){
	    foreach ($rules as $rule => $rewrite) {
	        if ( preg_match('(feed|rss2|atom|comment|attachment|trackback)i',$rule) ) {
	            unset($rules[$rule]);
	        }
	    }
	    return $rules;
	});

    // Disable Emojis
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );    
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    add_filter( 'tiny_mce_plugins', function($plugins){
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        } else {
            return array();
        }
    });


});
