<?php 
/**
 * TODO:
 * - Fix title not only one
 * - Implementare altri campi form
 * - Implementare campi in registrazione utente
 * - Implementare campi condizionali e validazione
 * - Implementera possibilità di eliminare campi esistenti
 * - Creare una classe AeriaUser
 */

if( false === defined('AERIA') ) exit;

class AeriaUserMeta {
	
	private static 	$saveHook 		= array( 'personal_options_update', 'edit_user_profile_update', 'user_register' ),
					$showHook 		= array( 'show_user_profile', 'edit_user_profile', 'user_new_form' ),
					$type 			= [ 'select', 'text', 'textarea' ],
					$showedTitle 	= false; 


	public static function register() {
		foreach ( func_get_args() as $options ){
			if( !is_array( $options ) ) throw new Exception( "AeriaUserMeta: Options parameter isn't an array.", 1 );
			if ( is_array_associative( $options ) ){
				static::setMeta( $options );
			}else{
				foreach ( $options as $option ){
					static::setMeta( $option );
				}
			}
		}
	}

	private static function setMeta( $options ){
		if( empty( $options['id'] ) ) throw new Exception( 'AeriaUserMeta: You must define a user meta id.', 1 );
		if( empty( $options['type'] ) ) throw new Exception( 'AeriaUserMeta: You must define a user meta type.', 1 );
		if( !in_array( $options['type'], static::$type ) ) throw new Exception( "AeriaUserMeta: The " . $options['type'] . " type isn't supported.", 1 );
		
		$options = array_merge_replace( array(
			'label'	=> '',
			'title' => 'Campi aggiuntivi'
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
    		if( !current_user_can( 'read' ) )
        		return false;

        	if( !self::$showedTitle ){
        		echo '<h3>' , $options['title'] , '</h3>';
        		self::$showedTitle = true;
        	}

        	AeriaForm::create([
	    		'action' 	=> 'hack',
	    		'type' 		=> 'wordpress',
	    		'startForm'	=> '',
	    		'endForm'   => ''
	    	])->setFields([
				'id'    => $options['id'],
				'name'  => $options['id'],
				'label' => $options['label'],
				'type'  => $options['type'],
				'other' => 'class="regular-text"',
				'value' => esc_attr( get_the_author_meta( $options['id'], $user->ID ) )
			])->getForm();
        };

		foreach ( static::$saveHook as $value ) {
			add_action( $value, $saveMeta, 1 );
		}
		foreach ( static::$showHook as $value ) {
			add_action( $value, $metaFields, 1 );
		}
	}
}