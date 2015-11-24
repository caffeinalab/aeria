<?php 
/**
 * @Author: Gabriele Diener <gabriele.diener@caffeina.it>
 **/

if( false === defined('AERIA') ) exit;

Class AeriaForm {
	
	protected			$startForm      	= '',
						$fields         	= [],
						$endForm        	= '</form>',
						$beforeForm			= '',
						$afterForm			= '',
						$beforeField		= '',
						$afterField			= '',
						$enableHTML5    	= true,
						$fieldsType     	= [ "text", "file". "radio", "checkbox", "button", "image", "hidden", "password", "reset", "submit", "select", "textarea", "title", "custom" ],
						$fieldsTypeHTML5  	= [ "color", "date", "datetime", "datetime-local", "email", "week", "month", "number", "range", "search", "tel", "time", "url" ],
						$ErrorPrefix		= '<strong>[AeriaForm: ',
						$ErrorSuffix		= '!]</strong>',
						$tabCharacter		= "\t";
	
	public				$tab       			= "",
						$paragraph      	= "\n";

	/**
	* Usage
	*
	* @param Array $options
	*
	* $name, $action, $id = NULL, $enctype = 'text/plain', $method = 'POST', $target = '_blank', $other = ''
	* $options = [
	* 	'name' 			=> 'FormName',
	* 	'action'  		=> 'FormId',
	*	'id'			=> NULL,
	*	'enctype'		=> 'text/plain',
	*	'method'		=> 'POST',
	*	'target'		=> '_blank',
	*	'type'			=> 'none',
	*	'other'     	=> '',
	*	'indentation'	=> 0
	* ]
	*/
	public function __construct( array $options ){
		
		$this->validateArrayKey( $options, 'action', true, 'You must define a action');

		$options = array_merge_replace(array(
			'name'			=> '',
			'id'			=> '',
			'enctype'		=> 'text/plain',
			'method'		=> 'POST',
			'target'		=> '_blank',
			'other'     	=> '',
			'before'		=> '',
			'after'			=> '',
			'type'			=> 'none',
			'indentation'	=> 0
      	), $options);

		if ( $options['indentation'] > 0 )
			$this->addTab($options['indentation']);

		$this->startForm = '<form'.( ( $this->validateArrayKey( $options, 'id', false ) )? ' id="'.$options['id'].'"' : '' ).( ( $this->validateArrayKey( $options, 'name', false ) )?' name="'.$options['name'].'"':'').' action="'.$options['action'].'" method="'.$options['method'].'" enctype="'.$options['enctype'].'"'.( ( $this->validateArrayKey( $options, 'other', false) )? ' '.$options['other'] : '' ).'>'.$this->paragraph;
		
		if( $options['type'] !== 'none' ){
			$this->renderFormType( $options['type'] );
		}else{
			$this->beforeForm 	= $options['before'];
			$this->afterForm 	= $options['after'];
		}
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
	* @param Array [<description>]
	*/
	public function & setFields(){
		foreach (func_get_args() as $field){
			if(is_array($field)){
				if ($this->isAssoc($field)){
					$this->fields[] = $this->renderField($field);
				}else{
					foreach ($field as $subfield){
						$this->fields[] = $this->renderField($subfield);
					}
				}
			}else{
				$this->error('setFields() parameter isn\'t an array');
			}
		}
		return $this;
	}

	/**
	*
	* Renderizza l'html del campo input configurato
	*
	* @param $fieldArray = [
	*	'id'    => 'prova',
	*	'name'    => 'nome',
	*	'type'    => 'text'
	*	'value'   => 'adasd',
	*	'label'	  => 'Nome:',
	*	'option'  => [
	*			[
	*				'value' => 'Option1',
	*				'label' => 'Name',
	*				'other' => 'parametro'
	*			],
	*		],
	*	'other'	  => 'disabled', 
	*	];
	* 
	* @return string         		Html del campo input renderizzato
	*/ 
	protected function renderField(array $fieldArray){

		$this->checkfields($fieldArray);
		$this->addTab();
		switch ($fieldArray['type']) {
			case 'custom':
				$html = $this->tab . $fieldArray['render'] . $this->paragraph;
				break;

			case 'comment':
				$html = $this->tab.'<!-- ['. $fieldArray['value'] .'] -->'.$this->paragraph;
				break;

			case 'title':
				$head = ( ( $this->validateArrayKey( $fieldArray, 'head', false ) )? $fieldArray['head'] : '3' );
				$html = $this->tab.'<h'.$head.'>'. $fieldArray['value'] .'</h'.$head.'>'.$this->paragraph;
				break;

			case 'select':
				if($this->beforeField){
					$html = $this->tab.$this->beforeField.$this->paragraph;
					$this->addTab();
				}else{
					$html = '';
				}

				$html .= ( ( $this->validateArrayKey( $fieldArray, 'label', false ) )? $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$fieldArray['label'].'</label>'.$this->paragraph : '' );
				$html .= $this->tab.'<select'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'">'.$this->paragraph;
				$this->addTab();

				foreach ($fieldArray['options'] as $option) {
					$this->validateArrayKey( $option, 'value', true, 'You must define a value for select option');
					$this->validateArrayKey( $option, 'label', true, 'You must define a label for select option');
					$html .= $this->tab.'<option value="'.$option['value'].'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$option['other'] : '' ).'>'.$option['label'].'</option>'.$this->paragraph;
				}
				
				$this->removeTab();
				$html .= $this->tab.'</select>'.$this->paragraph;

				if($this->afterField){
					$this->removeTab();
					$html .= ($this->afterField)?$this->tab.$this->afterField.$this->paragraph:'';
				}
				break;

			case 'textarea':
				if($this->beforeField){
					$html = $this->tab.$this->beforeField.$this->paragraph;
					$this->addTab();
				}else{
					$html = '';
				}

				$html .= ( ( $this->validateArrayKey( $fieldArray, 'label', false ) )? $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$fieldArray['label'].'</label>'.$this->paragraph : '' );
				$html .= $this->tab.'<textarea'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'" rows="'.( ( $this->validateArrayKey( $fieldArray, 'rows', false ) )? $fieldArray['rows'] : '5' ).'" cols="'.( ( $this->validateArrayKey( $fieldArray, 'cols', false ) )? $fieldArray['cols'] : '40' ).'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$fieldArray['other'] : '' ).'>'.( ( $this->validateArrayKey( $fieldArray, 'value', false ) )? $fieldArray['value'] : '' ).'</textarea>'.$this->paragraph;
				
				if($this->afterField){
					$this->removeTab();
					$html .= ($this->afterField)?$this->tab.$this->afterField.$this->paragraph:'';
				}
				break;

			case 'radio':
			case 'checkbox':
				if($this->beforeField){
					$html = $this->tab.$this->beforeField.$this->paragraph;
					$this->addTab();
				}else{
					$html = '';
				}
				
				if ($this->validateArrayKey( $fieldArray, 'label', false)) {
					$html .= $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$this->paragraph;
					$this->addTab();
				}
				
				$html .= $this->tab.'<input'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'" type="'.$fieldArray['type'].'" value="'.$fieldArray['value'].'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$fieldArray['other'] : '' ).'>'.( ( $this->validateArrayKey( $fieldArray, 'label', false) )? ' '.$fieldArray['label'] : '' ).$this->paragraph;
				
				if ($this->validateArrayKey( $fieldArray, 'label', false)) {
					$this->removeTab();
					$html .= $this->tab.'</label>'.$this->paragraph;
				}
				
				if($this->afterField){
					$this->removeTab();
					$html .= ($this->afterField)?$this->tab.$this->afterField.$this->paragraph:'';
				}
				break;

			default:
				if($this->beforeField){
					$html = $this->tab.$this->beforeField.$this->paragraph;
					$this->addTab();
				}else{
					$html = '';
				}

				$html .= ( ( $this->validateArrayKey( $fieldArray, 'label', false ) )? $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$fieldArray['label'].'</label>'.$this->paragraph : '' );
				$html .= $this->tab.'<input'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'" type="'.$fieldArray['type'].'" value="'.$fieldArray['value'].'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$fieldArray['other'] : '' ).'>'.$this->paragraph;
				
				if($this->afterField){
					$this->removeTab();
					$html .= ( $this->afterField )? $this->tab.$this->afterField.$this->paragraph : '';
				}
				break;
		}
		$this->removeTab();
		return $html;
	}

	protected function checkfields(array $fieldArray){

		$this->validateArrayKey( $fieldArray, 'type', true, 'You must define a type');

		if ( ( $fieldArray['type'] === 'comment' ) || ( $fieldArray['type'] === 'title' ) ) {
			
			$this->validateArrayKey( $fieldArray, 'value', true, 'You must define a value');
		
		} else if($fieldArray['type'] === 'custom') {
			
			$this->validateArrayKey( $fieldArray, 'render', true, 'You must define a render for custom type');

		} else {

			$this->validateArrayKey( $fieldArray, 'name', true, 'You must define a name');
		
			if ( !in_array( $fieldArray['type'], $this->getAllowedType() ) ) $this->error('This type isn\'t supported');
			
			if ( $fieldArray['type'] !== 'select' ) {

				$this->validateArrayKey( $fieldArray, 'value', true, 'You must define a value');
			
			}else if ( ( !is_array( $fieldArray['options'] ) ) || ( $this->isAssoc($fieldArray['options']) ) ){
				$this->error('You must define a valid option for select type');

			}

		}
	}

	protected function renderFormType( $formType ){
		switch ($formType) {
			case 'bootstrap':
				$this->beforeField 	= '<div class="form-group">';
				$this->afterField 	= '</div>';
				break;
						
			case 'wordpress':
				break;

			default:
				$this->error('This form type isn\'t supported');
				break;
		}
	}

	public function getForm( $echo = true ){
		if($this->beforeForm){
			$html = $this->tab.$this->beforeForm.$this->paragraph;
			$this->addTab();
		}else{
			$html = '';
		}

		$html .= $this->tab.$this->startForm.implode('', $this->fields).$this->tab.$this->endForm;
		
		if($this->afterForm){
			$this->removeTab();
			$html .= $this->paragraph.$this->tab.$this->afterForm;
		}

		if ($echo){
			echo $html;
			return true;
		}else{
			return $html;
		}
	}

	public function & resetFields(){
		$this->fields = [];
		return $this;
	}

	public function & toogleHTML5( $bool ){
		$this->enableHTML5 = !!$bool;
		return $this;
	}

	public function & addType( $type ){
		$this->fieldsType[] = $type;
		return $this;
	} 

	public function getAllowedType(){
		if($this->enableHTML5){
			return array_merge($this->fieldsType, $this->fieldsTypeHTML5);
		}else{
			return $this->fieldsType;
		}
	}

	public function & addTab( $num = 1 ){
		for ($i=0; $i < $num; $i++) $this->tab .= $this->tabCharacter;	
		return $this;
	} 	

	public function & removeTab( $num = 1 ){
		for ($i=0; $i < $num; $i++) $this->tab = substr( $this->tab, 1 );
		return $this;
	} 

	public function & beforeForm( $html = '' ){
		$this->beforeForm = $html;
		return $this;
	} 

	public function & afterForm( $html = '' ){
		$this->afterForm = $html;
		return $this;
	} 

	public function & before( $html = '' ){
		$this->beforeField = $html;
		return $this;
	} 

	public function & after( $html = '' ){
		$this->afterForm = $html;
		return $this;
	} 

	public function & removeType( $type ){
		$this->fieldsType = array_diff( $this->fieldsType, (array) $type );
		return $this;
	} 

	/**
	 * @param  array   $array        
	 * @param  integer $key          
	 * @param  boolean $triggerError 
	 * @param  string  $errorMessage 
	 * @return bool/error()            
	 */
	protected function validateArrayKey( array $array, $key = 0, $triggerError = true, $errorMessage = NULL ){
		if ( ( !isset( $array[$key] ) ) || empty( trim( $array[$key] ) ) ) { 
			if( $triggerError ) { 
				$this->error($errorMessage);
			}else{ 
				return false;
			}
		}else{ 
			return true;
		}
	}

	protected function error( $errorMessage = 'Unknown Error!' ){
		die( $this->ErrorPrefix.$errorMessage.$this->ErrorSuffix );
	}

	// check if array is associative
	protected function isAssoc(array $arr, $reusingKeys = false ) {
		$range = range(0, count($arr) - 1);
		return $reusingKeys? $arr !== $range : array_keys($arr) !== $range;
	}
}
