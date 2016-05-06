<?php 
/**
 * TODO:
 * - Aggiungere filtri sui singoli elementi form, input e label
 * - Aggiungere opzione in setFormType() per agire sui filtri dei singoli elementi
 * - Rimuovere else inutili seguiti da throw new Exception
 **/

if( false === defined('AERIA') ) exit;

Class AeriaForm {

	public static		$customType 	= [],
						// Type Array
						$type				= [
							'standard'  	=> [ "text", "file", "radio", "checkbox", "button", "image", "hidden", "password", "reset", "submit", "select", "textarea", "title", "custom" ],
							'Html5' 		=> [ "color", "date", "datetime", "datetime-local", "email", "week", "month", "number", "range", "search", "tel", "time", "url" ],
							'enableHtml5' 	=> true
						];
	
						// Form Parts Array
	public				$form 				= [
							'start' 	=> '',
							'fields'    => [],
							'end'       => '',
						],
						// Utils Array
						$utils 				= [
							'paragraph' 	=> "\n",
							'tab' 			=> "\t",
							'currentTab'    => "",
						],
						// Before/After Vars
						$before 			= [
							'form'		=> '',
							'innerForm'	=> '',
							'field'		=> '',
							'label'		=> '',
							'input'		=> '',
						],
						$after 				= [
							'form'		=> '',
							'innerForm'	=> '',
							'field'		=> '',
							'label'		=> '',
							'input'		=> '',
						];						

	/**
	* Usage
	*
	* @param Array $options
	*
	* $name, $action, $id = NULL, $enctype = 'text/plain', $method = 'POST', $target = '_blank', $other = ''
	* $options = [
	* 	'name' 			=> 'FormName',
	* 	'action'  		=> 'FormId',
	*	'id'			=> 'FormId',
	*	'enctype'		=> 'text/plain',
	*	'method'		=> 'POST',
	*	'target'		=> '_blank',
	*	'type'			=> 'none',
	*	'other'     	=> '',
	*	'indentation'	=> 0,
	*	'before'		=> [],
	*	'after'			=> [],
	* ]
	*/
	public function __construct( array $options ){
		
		self::validateArrayKey( $options, 'action', true, 'You must define a action.' );

		$options = array_merge_replace( array(
			'name'			=> '',
			'id'			=> '',
			'enctype'		=> 'text/plain',
			'method'		=> 'POST',
			'target'		=> '_blank',
			'other'     	=> '',
			'before'		=> [],
			'after'			=> [],
			'type'			=> 'none',
			'indentation'	=> 0,
			'startForm'     => false,
			'endForm'		=> '</form>',
      	), $options );

		if ( $options['indentation'] > 0 )
			$this->addTab( $options['indentation'] );

		if( $options['startForm'] !== false ){
			$this->form['start'] = $options['startForm'];
		}else{
			$this->form['start'] = '<form' . ( ( self::validateArrayKey( $options, 'id', false ) )? ' id="' . $options['id'] . '"' : '' ) . ( ( self::validateArrayKey( $options, 'name', false ) )?' name="' . $options['name'] . '"' : '' ) . ' action="' . $options['action'] . '" method="' . $options['method'] . '" enctype="' . $options['enctype'] . '"' . ( ( self::validateArrayKey( $options, 'other', false ) )? ' ' . $options['other'] : '' ) . '>' . $this->utils['paragraph'];
		}

		$this->form['end'] = $options['endForm'];
		
		if( ( $options['type'] !== 'none' ) && ( $options['type'] !== '' ) )
			$this->renderFormType( $options['type'] );

		$this->before 	= array_merge_replace( $this->before, $options['before'] );
		$this->after 	= array_merge_replace( $this->after, $options['after'] );
		
		if( $this->before['form'] ){
			$html = $this->getBefore( 'form', NULL, $options );
			$this->addTab();
		}else{
			$html = '';
		}	
		
		$html .= $this->utils['currentTab'].$this->form['start'].$this->getBefore( 'innerForm', true, $options );
		$this->form['start'] = $html;

	}

	public static function create( array $options ){
		return new AeriaForm( $options );
	}

	/**
	 * 
	* Usage
	* AeriaForm::create('Name','Action')
	*    ->setFields($field1,$field2,$field3,$field4)
	*    ->setFields($fieldsArray,$fieldsArray,$field5)
	*    ->getForm();
	*
	* @param array() Fields option array
	*/
	public function & setFields(){	
		foreach ( func_get_args() as $field ){
			if ( !is_array( $field ) ) throw new Exception( "AeriaForm: setFields() parameter isn't an array.", 1 );
			if ( is_array_associative( $field ) ){
				$this->form['fields'][] = $this->renderField( $field );
			}else{
				foreach ( $field as $subfield ){
					$this->form['fields'][] = $this->renderField( $subfield );
				}
			}
		}
		return $this;
	}

	/**
	*
	* Renderizza l'html del campo input configurato
	*
	* @param array $fieldArray = [
	*	'id'    	=> 'prova',
	*	'name'    	=> 'nome',
	*	'type'    	=> 'text'
	*	'value'   	=> 'adasd',
	*	'label'	  	=> 'Nome:',
	*	'option'  	=> [
	*			[
	*				'value' => 'Option1',
	*				'label' => 'Name',
	*				'other' => 'parametro'
	*			],
	*		],
	*	'other'	  	=> 'disabled', 
	*	];
	* 
	* @return string Html del campo input renderizzato
	*/ 
	private function renderField($fieldArray){

		$this->checkfields($fieldArray);

		switch ($fieldArray['type']) {
			case 'hidden':
				$this->addTab();
				$html = $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', '<input' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ) . ' name="'.$fieldArray['name'] . '" type="hidden" value="' . $fieldArray['value'] . '"' . ( ( self::validateArrayKey( $fieldArray, 'other', false ) )? ' ' . $fieldArray['other'] : '' ) . '>', $fieldArray  ) . $this->utils['paragraph'];
				$this->removeTab();
				break;

			case 'custom':
				$html = $this->getBefore( 'field', true, $fieldArray );
				$this->addTab();
				if (is_callable($fieldArray['render'])){
					$html .= $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', call_user_func( $fieldArray['render'], $this->utils ), $fieldArray ) . $this->utils['paragraph'];
				}else{
					$html .= $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', $fieldArray['render'], $fieldArray ) . $this->utils['paragraph'];
				}
				$this->removeTab();
				$html .= $this->getAfter( 'field', false, $fieldArray );
				break;

			case 'comment':
				$this->addTab();
				$html = $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', '<!-- ['. $fieldArray['value'] .'] -->', $fieldArray ) . $this->utils['paragraph'];
				$this->removeTab();
				break;

			case 'title':
				$head = ( ( self::validateArrayKey( $fieldArray, 'head', false ) )? $fieldArray['head'] : '3' );
				$html = $this->getBefore( 'field', true, $fieldArray );
				$this->addTab();
				$html = $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', '<h' . $head . '>'. $fieldArray['value'] .'</h' . $head . '>', $fieldArray ) . $this->utils['paragraph'];
				$this->removeTab();
				$html = $this->getAfter( 'field', false, $fieldArray );
				break;

			// Input apply_filters mancante
			case 'select':
				$html = $this->getBefore( 'field', true, $fieldArray );
				
				if ( self::validateArrayKey( $fieldArray, 'label', false ) ){
					$html .= $this->getBefore( 'label', true, $fieldArray );
					$this->addTab();
					$html .= $this->utils['currentTab'] . '<label' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="' . $fieldArray['id'] . '"' ) : '' ) . '>' . $fieldArray['label'] . '</label>' . $this->utils['paragraph'];
					$this->removeTab();
					$html .= $this->getAfter( 'label', false, $fieldArray );
				}

				$html .= $this->getBefore( 'input', true, $fieldArray );
				$this->addTab();
				$html .= $this->utils['currentTab'] . '<select' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="' . $fieldArray['id'] . '"' ) : '' ) . ' name="' . $fieldArray['name'] . '">' . $this->utils['paragraph'];
				$this->addTab();

				foreach ( $fieldArray['options'] as $option ) {
					self::validateArrayKey( $option, 'value', true, 'You must define a value for select option.' );
					self::validateArrayKey( $option, 'label', true, 'You must define a label for select option.' );
					$html .= $this->utils['currentTab'] . '<option value="' . $option['value'] . '"' . ( ( self::validateArrayKey( $fieldArray, 'other', false ) )? ' ' . $option['other'] : '' ) . '>'.$option['label'] . '</option>' . $this->utils['paragraph'];
				}
				
				$this->removeTab();
				$html .= $this->utils['currentTab'] . '</select>' . $this->utils['paragraph'];
				$this->removeTab();
				$html .= $this->getAfter( 'input', false, $fieldArray );
				$html .= $this->getAfter( 'field', false, $fieldArray );
				break;

			case 'textarea':
				$html = $this->getBefore( 'field', true, $fieldArray );
				
				if ( self::validateArrayKey( $fieldArray, 'label', false ) ){

					$html .= $this->getBefore( 'label', true, $fieldArray );
					$this->addTab();
					$html .= $this->utils['currentTab'] . '<label' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="' . $fieldArray['id'] . '"' ) : '' ) . '>' . $fieldArray['label'] . '</label>' . $this->utils['paragraph'];
					$this->removeTab();
					$html .= $this->getAfter( 'label', false, $fieldArray );

				}

				$html .= $this->getBefore( 'input', true, $fieldArray );
				$this->addTab();
				$html .= $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', '<textarea' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'] . '"' ) : '' ) . ' name="' . $fieldArray['name'] . '" rows="' . ( ( self::validateArrayKey( $fieldArray, 'rows', false ) )? $fieldArray['rows'] : '5' ) . '" cols="' . ( ( self::validateArrayKey( $fieldArray, 'cols', false ) )? $fieldArray['cols'] : '40' ) . '"' . ( ( self::validateArrayKey( $fieldArray, 'other', false ) )? ' ' . $fieldArray['other'] : '' ) . '>' . ( ( self::validateArrayKey( $fieldArray, 'value', false ) )? $fieldArray['value'] : '' ) . '</textarea>', $fieldArray ) . $this->utils['paragraph'];
				$this->removeTab();
				$html .= $this->getAfter( 'input', false, $fieldArray );
				$html .= $this->getAfter( 'field', false, $fieldArray );
				break;

			case 'radio':
			case 'checkbox':
				$html = $this->getBefore( 'field', true, $fieldArray );
				
				if ( self::validateArrayKey( $fieldArray, 'heading', false ) ) {

					$html .= $this->getBefore( 'label', true, $fieldArray );
					$html .= $this->utils['currentTab'] . $fieldArray['heading'] . $this->utils['paragraph'];
					$html .= $this->getAfter( 'label', false, $fieldArray );
				}

				$html .= $this->getBefore( 'input', true, $fieldArray );
				
				if (  self::validateArrayKey( $fieldArray, 'label', false ) ) {
					$this->addTab();
					$html .= $this->utils['currentTab'] . '<label' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="' . $fieldArray['id'].'"' ) : '' ) . '>' . $this->utils['paragraph'];
				}

				$this->addTab();
				$html .= $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', '<input' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="' . $fieldArray['id'] . '"' ) : '' ) . ' name="' . $fieldArray['name'] . '" type="' . $fieldArray['type'] . '" value="' . $fieldArray['value'] . '"' . ( ( self::validateArrayKey( $fieldArray, 'other', false ) )? ' ' . $fieldArray['other'] : '' ) . '>', $fieldArray ) . ( ( self::validateArrayKey( $fieldArray, 'label', false) )? ' ' . $fieldArray['label'] : '' ) . $this->utils['paragraph'];
				$this->removeTab();

				if ( self::validateArrayKey( $fieldArray, 'label', false ) ) {
					$html .= $this->utils['currentTab'] . '</label>' . $this->utils['paragraph'];
					$this->removeTab();
				}

				$html .= $this->getAfter( 'input', false, $fieldArray );
				$html .= $this->getAfter( 'field', false, $fieldArray );
				break;

			default:
				$html = $this->getBefore( 'field', true, $fieldArray );

				if ( self::validateArrayKey( $fieldArray, 'label', false ) ) {
					$html .= $this->getBefore('label', true, $fieldArray );
					$this->addTab();
					$html .= $this->utils['currentTab'] . '<label' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="' . $fieldArray['id'] . '"' ) : '' ) . '>' . $fieldArray['label'] . '</label>' . $this->utils['paragraph'];
					$this->removeTab();
					$html .= $this->getAfter( 'label', false, $fieldArray );
				}

				$html .= $this->getBefore( 'input', true, $fieldArray );
				$this->addTab();
				$html .= $this->utils['currentTab'] . apply_filters( 'AeriaFormInput', '<input' . ( ( self::validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="' . $fieldArray['id'] . '"' ) : '' ) . ' name="'.$fieldArray['name'] . '" type="' . $fieldArray['type'].'"' . ( ( self::validateArrayKey( $fieldArray, 'value', false ) )? ' value="' . $fieldArray['value'] . '"' : '' ) . ( ( self::validateArrayKey( $fieldArray, 'other', false ) )? ' ' . $fieldArray['other'] : '' ) . '>', $fieldArray ) . $this->utils['paragraph'];
				$this->removeTab();
				$html .= $this->getAfter( 'input', false, $fieldArray );
				$html .= $this->getAfter( 'field', false, $fieldArray );
				break;
		}
		return $html;

	}

	private function checkfields(array $fieldArray){

		self::validateArrayKey( $fieldArray, 'type', true, 'You must define a type.' );

		if ( ( $fieldArray['type'] === 'comment' ) || ( $fieldArray['type'] === 'title' ) ) {
			
			self::validateArrayKey( $fieldArray, 'value', true, 'You must define a value.' );
		
		} else if( $fieldArray['type'] === 'custom' ) {
			
			self::validateArrayKey( $fieldArray, 'render', true, 'You must define a render for custom type.' );

		} else if( $fieldArray['type'] !== 'submit' ){

			self::validateArrayKey( $fieldArray, 'name', true, 'You must define a name.' );
		
			if ( !in_array( $fieldArray['type'], static::getAllowedType() ) ) throw new Exception( "AeriaForm: The " . $fieldArray['type']." type isn't supported.", 1 );

			if ( ( $fieldArray['type'] === 'checkbox' ) || ( $fieldArray['type'] === 'radio' ) ) {

				self::validateArrayKey( $fieldArray, 'value', true, 'You must define a value.' );
			
			}else if ( 
				( $fieldArray['type'] === 'select' ) 
				&&
				( ( !is_array( $fieldArray['options'] ) ) || ( is_array_associative( $fieldArray['options'] ) ) ) 
			){
				throw new Exception( 'AeriaForm: You must define a valid option for select type.', 1 );
			}
		}

	}

	public static function setFormType( $typeArray ){
		if ( !is_array_associative($typeArray) ) throw new Exception( "AeriaForm: $typeArray parameter isn't an associative array.", 1 );
		foreach ($typeArray as $name => $type) {
			if ( !is_array_associative($type) ) throw new Exception( "AeriaForm: $type parameter isn't an associative array.", 1 );
			if( 
				( self::validateArrayKey( $type, 'before', false ) && is_array($type['before']) && is_array_associative($type['before']) ) 
				||
				( self::validateArrayKey( $type, 'after', false ) && is_array($type['after']) && is_array_associative($type['after']) ) 
			){
				static::$customType[$name] = $type;
			}else{
				throw new Exception( "AeriaForm: $type doesn't have an array associative with key before or after.", 1 );
			}
		}
	}

	private function renderFormType( $formType ){
		if( self::validateArrayKey( static::$customType, $formType, false ) ){
			if( self::validateArrayKey( static::$customType[$formType], 'before', false ) ){
				$this->before = array_merge_replace( $this->before, static::$customType[$formType]['before'] );
			}
			if( self::validateArrayKey( static::$customType[$formType], 'after', false ) ){
				$this->after = array_merge_replace( $this->after, static::$customType[$formType]['after'] );
			}
		}else{
			throw new Exception( "AeriaForm: Form type " . $formType . " isn't supported.", 1 );
		}
	}

	public function getForm( $echo = true ){
		
		$html = $this->form['start'];
		$html .= implode( '', $this->form['fields'] );
		$html .= $this->getAfter( 'innerForm', false, $this->form['fields'] );
		$html .= $this->utils['currentTab'] . $this->form['end'] . $this->utils['paragraph'];
		
		if( $this->after['form'] ){
			$this->removeTab();
			$html .= $this->getAfter( 'form', NULL, $this->form['fields'] );
		}

		if ($echo){
			echo $html;
			return true;
		}else{
			return $html;
		}

	}

	public function & resetFields(){
		$this->form['fields'] = [];
		return $this;
	}

	/**
	 * Input Type Utils
	 */

	public static function toogleHTML5( $bool ){
		return static::$type['enableHtml5'] = !!$bool;
	}

	public static function addType( $type ){
		static::$type['standard'][] = $type;
		return static::getAllowedType();
	} 

	public static function removeType( $type ){
		static::$type['standard'] = array_diff( static::$type['standard'], (array) $type );
		return static::getAllowedType();
	}

	public static function getAllowedType(){
		if( static::$type['enableHtml5'] ){
			return array_merge( static::$type['standard'], static::$type['Html5'] );
		}else{
			return static::$type['standard'];
		}
	}

	/**
	 * Tab Type Utils
	 */
	public function & addTab( $num = 1 ){
		for ( $i=0; $i < $num; $i++ ) $this->utils['currentTab'] .= $this->utils['tab'];	
		return $this;
	} 	

	public function & removeTab( $num = 1 ){
		for ( $i=0; $i < $num; $i++ ) $this->utils['currentTab'] = substr( $this->utils['currentTab'], 1 );
		return $this;
	} 

	/**
	 * Before/After Utils
	 */
	private function setElement( $point = 'form', $html = '', $arrayName = 'before' ){
			$this->{$arrayName}[$point] = $html;
	}

	private function getElement( $point, $addTab = NULL, $arrayName = 'before', $param = [] ){
		if( $this->{$arrayName}[$point] !== '' ){
			$element = ( is_callable( $this->{$arrayName}[$point] ) )? call_user_func( $this->{$arrayName}[$point], $param ) : $this->{$arrayName}[$point] ;
			if( !is_null( $addTab ) ){
				if( !!$addTab ){
					$this->addTab();
					$html = $this->utils['currentTab'] . $element . $this->utils['paragraph'];
				}else{
					$html = $this->utils['currentTab'] . $element . $this->utils['paragraph'];
					$this->removeTab();
				}
				return $html;
			}
			return $this->utils['currentTab'] . $element . $this->utils['paragraph'];
		}else{
			return;
		}
	}

	public function & setBefore( $point, $html = '' ){
		$this->setElement( $point, $html, 'before' );
		return $this;
	}

	public function & setAfter( $point, $html = '' ){
		$this->setElement( $point, $html, 'after' );
		return $this;
	}

	public function getBefore( $point, $addTab = NULL, $param = [] ){
		return $this->getElement( $point, $addTab, 'before', $param );
	}

	public function getAfter( $point, $addTab = NULL, $param = [] ){
		return $this->getElement( $point, $addTab, 'after', $param );
	}

	/**
	 * Utils
	 */

	/**
	 * @param  array   $array        
	 * @param  integer $key          
	 * @param  boolean $triggerError 
	 * @param  string  $errorMessage 
	 * @return bool/error()            
	 */
	private static function validateArrayKey( $array, $key = 0, $triggerError = true, $errorMessage = NULL ){
		if ( ( !isset( $array[$key] ) ) ||  empty( $array[$key] ) ) { 
			if( $triggerError ) throw new Exception( 'AeriaForm: ' . $errorMessage, 1 );
			return false;
		}else{ 
			return true;
		}
	}
}

AeriaForm::setFormType([
	'wordpress'	=> [
		'before' 	=> [
			'form'		=> '',
			'innerForm'	=> '<table class="form-table">',
			'field'		=> '<tr>',
			'label'		=> '<th scope="row">',
			'input'		=> '<td>'
		],
		'after' 	=> [
			'form'		=> '',
			'innerForm'	=> '</table>',
			'field'		=> '</tr>',
			'label'		=> '</th>',
			'input'		=> '</td>'
		]
	],
	'bootstrap'	=> [
		'before' 	=> [
			'form'		=> '<div class="row">',
			'innerForm'	=> '',
			'field'		=> '<div class="form-group">',
			'label'		=> '',
			'input'		=> ''
		],
		'after' 	=> [
			'form'		=> '</div>',
			'innerForm'	=> '',
			'field'		=> '</div>',
			'label'		=> '',
			'input'		=> ''
		]
	]
]);