<?php
/**
 * Helper for build custom wordpress menu
 *
 * @author Gabriele Diener <gabriele.diener@caffeina.com>
 */

class AeriaMenu {
	
	protected 			$elements 		= [],
			 			$args 			= [],
						$menu_location 	= "primary",
						$setted 		= false;

	/**
	 * Return array with all menu items.
	 * @param  string $menu_location 	Menu location
	 * @param  array $args 				Additional parameters for the elements of the menu 
	 * @see var_dump((new AeriaMenu($location))->items);
	 * 
	 * @return (false|array)         	Array of menu items, otherwise false
	 */
	public function __construct( $menu_location = false, $args = [] ) {
		if ( !empty( $menu_location ) ) $this->menu_location = $menu_location;
		if ( !empty( $args ) ) $this->args = $args;
		
		$menu_items = $this->items();
		
		$this->elements = empty($menu_items) ? false : array_reduce( $this->trasform($menu_items), function( $tree, $e ) {
			$parent_id = $e['parent_id'];
			$tree[ $parent_id ]['children'][] = $e;
			$tree[$e['id']] =& $tree[ $parent_id ]['children'][ count( $tree[ $parent_id ]['children']) - 1 ];
			return $tree;
		}, [ 0 => [ 'children' => [] ] ] )[0]['children'];
    }

	public static function location() {
		return get_nav_menu_locations();
	}

	public function items() {
		return wp_get_nav_menu_items( static::location()[ $this->menu_location ] );
	}

	public function elements() {
		return $this->elements;
	}

    protected function trasform( $menu ) {
    	return array_map( function($item) {
			return [
				'id' 		=> $item->ID,
				'parent_id' => $item->menu_item_parent,
				'title' 	=> $item->title,
				'url' 		=> $item->url,
				'object_id' => $item->object_id,
			] + $this->resultArgs($item) + [
				'children'	=> []
			];
		}, $menu );
    }

    protected function resultArgs($menu) {
    	if ($this->setted) return $this->args;

    	$this->args = array_map( function( $item ) use ($menu) {
    		return $menu->{$item};
    	}, $this->args );

    	$this->setted = true;
    	return $this->args;
    }
}