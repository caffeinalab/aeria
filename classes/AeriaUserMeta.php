<?php 
if( false === defined('AERIA') ) exit;

class AeriaUserMeta {
	
	public static 	$saveHook 		= array( 'personal_options_update', 'edit_user_profile_update', 'user_register' ),
					$showHook 		= array( 'show_user_profile', 'edit_user_profile', 'user_new_form' ),
					$type 			= [ 'select', 'text', 'textarea' ],
					$showedTitle 	= false; 


	public static function register( $options = [] ) {
		if(empty($options['id'])) die('AeriaUserMeta: You must define a user meta id.');
		if(empty($options['type'])) die('AeriaUserMeta: You must define a user meta type.');
		if( !in_array( $options['type'], static::$type ) ) die('AeriaUserMeta: The ' . $options['type'].' type isn\'t supported.');
		
		$options = array_merge_replace( array(
			'label'		=> '',
			'title'   	=> 'Campi aggiuntivi'
      	), $options );

		$saveMeta = function( $user_id ) use ( $options ) {
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

			update_user_meta( $user_id, $options['id'], $_POST[ $options['id'] ] );
		};

		$metaFields = function( $user ) use ( $options ) {
    		if(!current_user_can('read'))
        		return false;

        	if(!self::$showedTitle){
        		echo '<h3>',$options['title'],'</h3>';
        		self::$showedTitle = true;
        	}

        	$form = new AeriaForm([
	    		'action' 	=> 'hack',
	    		'type' 		=> 'wordpress',
	    		'startForm'	=> '',
	    		'endForm'   => ''
	    	]);	
       		$form->setFields([
    				'id'    => $options['id'],
					'name'  => $options['id'],
					'label' => $options['label'],
					'type'  => $options['type'],
					'other' => 'class="regular-text"',
					'value' => esc_attr( get_the_author_meta( $options['id'], $user->ID ) )
    			]);
    		$form->getForm();
        };

		foreach (static::$saveHook as $value) {
			add_action( $value, $saveMeta, 1 );
		}
		foreach (static::$showHook as $value) {
			add_action( $value, $metaFields, 1 );
		}
	}
}