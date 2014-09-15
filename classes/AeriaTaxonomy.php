<?php

if( false === defined('AERIA') ) exit;

class AeriaTaxonomy {
	static public $taxonomies = array();

	public static function exists($tax_name){
		return isset(static::$taxonomies[$tax_name]) || taxonomy_exists($tax_name);
	}

	public static function bind($tax_name,$post_type){
		if(false===static::exists($tax_name)) trigger_error('AeriaTaxonomy: '.strip_tags($tax_name).' doesn\'t exists.',E_USER_ERROR);
		foreach((array)$post_type as $type) register_taxonomy_for_object_type($tax_name,$type);
	}

	public static function register($tax){
		if(empty($tax['id'])) trigger_error('AeriaTaxonomy: You must define an id.',E_USER_ERROR);
		if(empty($tax['types'])) trigger_error('AeriaTaxonomy: You must define a target post_type.',E_USER_ERROR);
		$tax['options'] = empty($tax['options'])?array():$tax['options'];
		$taxonomy_id = $tax['id']; unset($tax['id']);
		$types = (array)$tax['types']; unset($tax['types']);

		$taxonomy_name = isset($tax['title'])?$tax['title']:ucfirst($taxonomy_id);
		static::$taxonomies[$taxonomy_id] = $tax;

		add_action( 'init', function() use ($tax,$taxonomy_id,$taxonomy_name,$types) {
			$options = array_merge_replace(array(
				'query_var' 				=> true,
				'hierarchical'              => true,
				'public'                    => true,
				'show_ui'                   => true,
				'show_admin_column'         => true,
				'show_in_nav_menus'         => true,
				'show_tagcloud'             => true,
				'rewrite' 					=> array( 'slug' => $taxonomy_id ),
				),(array)$tax['options']);

			unset($tax['options']);

			$options['labels'] = array_merge_replace(array(
				'name' => $taxonomy_name,
				'singular_name' => $taxonomy_name,
				'add_new' => 'Add new',
				'add_new_item' => 'Add new item',
				'edit_item' => 'Edit',
				'new_item' => 'New',
				'all_items' => 'Show all',
				'view_item' => 'Show item',
				'search_items' => 'Search',
				'not_found' =>  'Not found',
				'not_found_in_trash' => 'Not found in trash',
				'choose_from_most_used' => 'Choose from most used',
				'parent_item_colon' => null,
				'parent_item' => null,
				'menu_name' => $taxonomy_name,
				'menu_icon' => null,
				),(array)$tax);

			register_taxonomy( $taxonomy_id, $types, $options );
		},0);
	}
}