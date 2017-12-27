<?php

namespace Timber;

class MenuItemCollection extends \ArrayObject {
	
	public $MenuItemClass = 'Timber\MenuItem';

	function __construct( $menu_id ) {
		$this->init($menu_id)
	}

	protected function init( $menu_id ) {
		$menu = wp_get_nav_menu_items($menu_id);
		if ( $menu ) {
			_wp_menu_item_classes_by_context($menu);
			if ( is_array($menu) ) {
				$menu = self::order_children($menu);
				$menu = self::strip_to_depth_limit($menu);
			}

		}
	}

	public function depth(){
		
	}

}