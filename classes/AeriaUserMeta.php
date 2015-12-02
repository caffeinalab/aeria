<?php 
if( false === defined('AERIA') ) exit;

class AeriaUserMeta {
	
	public static 	$saveHook 	= array( 'personal_options_update', 'edit_user_profile_update', 'user_register' ),
					$showHook 	= array( 'show_user_profile', 'edit_user_profile', 'user_new_form' ),
					$type 		= [ 'select', 'text', 'textarea' ];


	public static function register( $option = [] ) {
		if(empty($option['id'])) die('AeriaUserMeta: You must define a user meta id.');
		if(empty($option['type'])) die('AeriaUserMeta: You must define a user meta type.');
		if( !in_array( $option['type'], static::$type ) ) die('AeriaUserMeta: The ' . $option['type'].' type isn\'t supported.');
		
		$options = array_merge_replace( array(
			'label'			=> '',
      	), $options );

		$saveMeta = function( $user_id ) use ( $option ) {
			global $pagenow;
			if ( 
				( $pagenow !== 'profile.php' ) 
				&&
				(
					( ( !current_user_can( 'edit_user', $user_id ) ) && ( ( $pagenow === 'user-edit.php' ) ) )
					OR
					( ( !current_user_can( 'create_users', $user_id ) ) && ( $pagenow === 'user-new.php' ) )
				)	
			)
			    return false;

			update_user_meta( $user_id, $option['id'], $_POST[ $option['id'] ] );
		};

		$metaFields = function( $user ) use ( $option ) {
    		if(!current_user_can('read'))
        		return false;

        	$form = new AeriaForm([
	    		'action' 	=> 'hack',
	    		'type' 		=> 'wordpress',
	    		'startForm'	=> '',
	    		'endForm'   => ''
	    	]);	
       		$form->setFields([
    				'id'    => $option['id'],
					'name'  => $option['id'],
					'label' => $option['label'],
					'type'  => $option['type'],
					'other' => 'class="regular-text"',
					'value' => esc_attr( get_the_author_meta( $option['id'], $user->ID ) )
    			]);
    		$form->getForm();
        };

		foreach (static::$saveHook as $value) {
			add_action( $value, $saveMeta );
		}
		foreach (static::$showHook as $value) {
			add_action( $value, $metaFields );
		}
	}
}