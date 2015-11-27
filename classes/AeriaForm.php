<?php 
/**
 * @Author: Gabriele Diener <gabriele.diener@caffeina.it>
 **/

if( false === defined('AERIA') ) exit;

Class AeriaForm {
	
	private				$startForm      	= '',
						$fields         	= [],
						$endForm        	= '</form>',
						/**
						 * Before/After Vars
						 */
						$beforeForm			= '',
						$afterForm			= '',
						$beforeInnerForm	= '',
						$afterInnerForm		= '',
						$beforeField		= '',
						$afterField			= '',
						$beforeLabel		= '',
						$afterLabel			= '',
						$beforeInput		= '',
						$afterInput			= '',
						$enableHTML5    	= true,
						$fieldsType     	= [ "text", "file", "radio", "checkbox", "button", "image", "hidden", "password", "reset", "submit", "select", "textarea", "title", "custom" ],
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
	*	'indentation'	=> 0,
	*	'before'		=> '',
	*	'after'			=> '',
	*	'beforeInner'	=> '',
	*	'afterInner'	=> '',
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
			'beforeInner'	=> '',
			'afterInner'	=> '',
			'type'			=> 'none',
			'indentation'	=> 0
      	), $options);

		if ( $options['indentation'] > 0 )
			$this->addTab($options['indentation']);

		$this->startForm = '<form'.( ( $this->validateArrayKey( $options, 'id', false ) )? ' id="'.$options['id'].'"' : '' ).( ( $this->validateArrayKey( $options, 'name', false ) )?' name="'.$options['name'].'"':'').' action="'.$options['action'].'" method="'.$options['method'].'" enctype="'.$options['enctype'].'"'.( ( $this->validateArrayKey( $options, 'other', false) )? ' '.$options['other'] : '' ).'>'.$this->paragraph;
		
		if( $options['type'] !== 'none' )
			$this->renderFormType( $options['type'] );

		$this->beforeForm 		= ($this->validateArrayKey( $options, 'before', false ) )? $options['before'] : $this->beforeForm ;
		$this->afterForm 		= ($this->validateArrayKey( $options, 'after', false ) )? $options['after'] : $this->afterForm ;
		$this->beforeInnerForm 	= ($this->validateArrayKey( $options, 'beforeInner', false ) )? $options['beforeInner'] : $this->beforeInnerForm ;
		$this->afterInnerForm 	= ($this->validateArrayKey( $options, 'afterInner', false ) )? $options['afterInner'] : $this->afterInnerForm ;
		
		if($this->beforeForm){
			$html = $this->getElement($this->beforeForm);
			$this->addTab();
		}else{
			$html = '';
		}	
		
		$html .= $this->tab.$this->startForm;

		$html .= $this->getElement($this->beforeInnerForm, true);

		$this->startForm = $html;
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
					$html = $this->tab.'<input'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'" type="hidden" value="'.$fieldArray['value'].'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$fieldArray['other'] : '' ).'>'.$this->paragraph;
					$this->removeTab();
				break;

			case 'custom':
				if (is_callable($fieldArray['render'])){
					$html = $this->getElement($this->beforeField, true);
					$this->addTab();
					$html .= $this->tab.call_user_func($fieldArray['render']).$this->paragraph;
					$this->removeTab();
					$html .= $this->getElement($this->afterField, false);
				}else{
					$html = $this->getElement($this->beforeField, true);
					$this->addTab();
					$html .= $this->tab . $fieldArray['render'] . $this->paragraph;
					$this->removeTab();
					$html .= $this->getElement($this->afterField, false);
				}
				break;

			case 'comment':
				$this->addTab();
				$html = $this->tab.'<!-- ['. $fieldArray['value'] .'] -->'.$this->paragraph;
				$this->removeTab();
				break;

			case 'title':
				$head = ( ( $this->validateArrayKey( $fieldArray, 'head', false ) )? $fieldArray['head'] : '3' );
				$html = $this->getElement($this->beforeField, true);
				$this->addTab();
				$html = $this->tab.'<h'.$head.'>'. $fieldArray['value'] .'</h'.$head.'>'.$this->paragraph;
				$this->removeTab();
				$html = $this->getElement($this->afterField, false);
				break;

			case 'select':
				$html = $this->getElement($this->beforeField, true);
				
				if ( $this->validateArrayKey( $fieldArray, 'label', false ) ){
					$html .= $this->getElement($this->beforeLabel, true);
					$this->addTab();
					$html .= $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$fieldArray['label'].'</label>'.$this->paragraph;
					$this->removeTab();
					$html .= $this->getElement($this->afterLabel, false);
				}

				$html .= $this->getElement($this->beforeInput, true);
				$this->addTab();
				$html .= $this->tab.'<select'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'">'.$this->paragraph;
				
				$this->addTab();

				foreach ($fieldArray['options'] as $option) {
					$this->validateArrayKey( $option, 'value', true, 'You must define a value for select option');
					$this->validateArrayKey( $option, 'label', true, 'You must define a label for select option');
					$html .= $this->tab.'<option value="'.$option['value'].'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$option['other'] : '' ).'>'.$option['label'].'</option>'.$this->paragraph;
				}
				
				$this->removeTab();

				$html .= $this->tab.'</select>'.$this->paragraph;
				$this->removeTab();
				$html .= $this->getElement($this->afterInput, false);
				
				$html .= $this->getElement($this->afterField, false);

				break;

			case 'textarea':

				$html = $this->getElement($this->beforeField, true);
				if ( $this->validateArrayKey( $fieldArray, 'label', false ) ){

					$html .= $this->getElement($this->beforeLabel, true);
					$this->addTab();
					$html .= $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$fieldArray['label'].'</label>'.$this->paragraph;
					$this->removeTab();
					$html .= $this->getElement($this->afterLabel, false);

				}

				$html .= $this->getElement($this->beforeInput, true);
				$this->addTab();
				$html .= $this->tab.'<textarea'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'" rows="'.( ( $this->validateArrayKey( $fieldArray, 'rows', false ) )? $fieldArray['rows'] : '5' ).'" cols="'.( ( $this->validateArrayKey( $fieldArray, 'cols', false ) )? $fieldArray['cols'] : '40' ).'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$fieldArray['other'] : '' ).'>'.( ( $this->validateArrayKey( $fieldArray, 'value', false ) )? $fieldArray['value'] : '' ).'</textarea>'.$this->paragraph;
				$this->removeTab();
				$html .= $this->getElement($this->afterInput, false);
				
				$html .= $this->getElement($this->afterField, false);

				break;

			case 'radio':
			case 'checkbox':
				$html = $this->getElement($this->beforeField, true);
				
				if ($this->validateArrayKey( $fieldArray, 'heading', false)) {

					$html .= $this->getElement($this->beforeLabel, true);

					$html .= $this->tab.$fieldArray['heading'].$this->paragraph;

					$html .= $this->getElement($this->afterLabel, false);
				}

				$html .= $this->getElement($this->beforeInput, true);
				
				if ($this->validateArrayKey( $fieldArray, 'label', false)) {
					$this->addTab();
					$html .= $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$this->paragraph;
				}
				$this->addTab();
				$html .= $this->tab.'<input'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'" type="'.$fieldArray['type'].'" value="'.$fieldArray['value'].'"'.( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$fieldArray['other'] : '' ).'>'.( ( $this->validateArrayKey( $fieldArray, 'label', false) )? ' '.$fieldArray['label'] : '' ).$this->paragraph;
				$this->removeTab();
				if ($this->validateArrayKey( $fieldArray, 'label', false)) {
					$html .= $this->tab.'</label>'.$this->paragraph;
					$this->removeTab();
				}

				$html .= $this->getElement($this->afterInput, false);

				$html .= $this->getElement($this->afterField, false);

				break;

			default:

				$html = $this->getElement($this->beforeField, true);

				if ( $this->validateArrayKey( $fieldArray, 'label', false ) ) {
					$html .= $this->getElement($this->beforeLabel, true);
					$this->addTab();
					$html .= $this->tab.'<label'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' for="'.$fieldArray['id'].'"' ) : '' ).'>'.$fieldArray['label'].'</label>'.$this->paragraph;
					$this->removeTab();
					$html .= $this->getElement($this->afterLabel, false);
				}

				$html .= $this->getElement($this->beforeInput, true);
				$this->addTab();
				$html .= $this->tab.'<input'.( ( $this->validateArrayKey( $fieldArray, 'id', false ) )? ( ' id="'.$fieldArray['id'].'"' ) : '' ).' name="'.$fieldArray['name'].'" type="'.$fieldArray['type'].'"'.( ( $this->validateArrayKey( $fieldArray, 'value', false ) )? ' value="'.$fieldArray['value'].'"' : '' ).( ( $this->validateArrayKey( $fieldArray, 'other', false ) )? ' '.$fieldArray['other'] : '' ).'>'.$this->paragraph;
				$this->removeTab();
				$html .= $this->getElement($this->afterInput, false);

				$html .= $this->getElement($this->afterField, false);


				break;
		}
		return $html;
	}

	private function checkfields(array $fieldArray){

		$this->validateArrayKey( $fieldArray, 'type', true, 'You must define a type');

		if ( ( $fieldArray['type'] === 'comment' ) || ( $fieldArray['type'] === 'title' ) ) {
			
			$this->validateArrayKey( $fieldArray, 'value', true, 'You must define a value');
		
		} else if($fieldArray['type'] === 'custom') {
			
			$this->validateArrayKey( $fieldArray, 'render', true, 'You must define a render for custom type');

		} else {

			$this->validateArrayKey( $fieldArray, 'name', true, 'You must define a name');
		
			if ( !in_array( $fieldArray['type'], $this->getAllowedType() ) ) $this->error('The '.$fieldArray['type'].' type isn\'t supported');

			if ( ( $fieldArray['type'] === 'checkbox' ) || ( $fieldArray['type'] === 'radio' ) ) {

				$this->validateArrayKey( $fieldArray, 'value', true, 'You must define a value');
			
			}else if ( 
				( $fieldArray['type'] === 'select' ) 
				&&
				( ( !is_array( $fieldArray['options'] ) ) || ( $this->isAssoc($fieldArray['options']) ) ) 
			){
				$this->error('You must define a valid option for select type');

			}

		}
	}

	private function renderFormType( $formType ){
		switch ($formType) {
			case 'bootstrap':
				$this->beforeForm		= '<div class="row">';
				$this->afterForm		= '</div>';
				$this->beforeInnerForm	= '';
				$this->afterInnerForm	= '';
				$this->beforeField 		= '<div class="form-group">';
				$this->afterField 		= '</div>';
				$this->beforeLabel		= '';
				$this->afterLabel		= '';
				$this->beforeInput		= '';
				$this->afterInput		= '';
				break;
						
			case 'wordpress':
				$this->beforeForm		= '';
				$this->afterForm		= '';
				$this->beforeInnerForm	= '<table class="form-table">';
				$this->afterInnerForm	= '</table>';
				$this->beforeField		= '<tr>';
				$this->afterField		= '</tr>';
				$this->beforeLabel		= '<th scope="row">';
				$this->afterLabel		= '</th>';
				$this->beforeInput		= '<td>';
				$this->afterInput		= '</td>';	
				break;

			default:
				$this->error('This form type isn\'t supported');
				break;
		}
	}

	public function getForm( $echo = true ){
		
		$html = $this->startForm;

		$html .= implode('', $this->fields);

		$html .= $this->getElement($this->afterInnerForm, false);

		$html .= $this->tab.$this->endForm.$this->paragraph;
		
		if($this->afterForm){
			$this->removeTab();
			$html .= $this->getElement($this->afterForm);
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

	/**
	 * Input Type Utils
	 */

	public function & toogleHTML5( $bool ){
		$this->enableHTML5 = !!$bool;
		return $this;
	}

	public function & addType( $type ){
		$this->fieldsType[] = $type;
		return $this;
	} 

	public function & removeType( $type ){
		$this->fieldsType = array_diff( $this->fieldsType, (array) $type );
		return $this;
	}

	public function getAllowedType(){
		if($this->enableHTML5){
			return array_merge($this->fieldsType, $this->fieldsTypeHTML5);
		}else{
			return $this->fieldsType;
		}
	}

	/**
	 * Tab Type Utils
	 */

	public function & addTab( $num = 1 ){
		for ($i=0; $i < $num; $i++) $this->tab .= $this->tabCharacter;	
		return $this;
	} 	

	public function & removeTab( $num = 1 ){
		for ($i=0; $i < $num; $i++) $this->tab = substr( $this->tab, 1 );
		return $this;
	} 

	/**
	 * Before/After Utils
	 */

	public function & beforeForm( $html = '' ){
		$this->beforeForm = $html;
		return $this;
	} 

	public function & afterForm( $html = '' ){
		$this->afterForm = $html;
		return $this;
	} 

	public function & beforeInnerForm( $html = '' ){
		$this->beforeInnerForm = $html;
		return $this;
	} 

	public function & afterInnerForm( $html = '' ){
		$this->afterInnerForm = $html;
		return $this;
	}

	public function & beforeField( $html = '' ){
		$this->beforeField = $html;
		return $this;
	}

	public function & afterField( $html = '' ){
		$this->afterField = $html;
		return $this;
	}

	public function & beforeLabel( $html = '' ){
		$this->beforeLabel = $html;
		return $this;
	}

	public function & afterLabel( $html = '' ){
		$this->afterLabel = $html;
		return $this;
	}

	public function & beforeInput( $html = '' ){
		$this->beforeInput = $html;
		return $this;
	}

	public function & afterInput( $html = '' ){
		$this->afterInput = $html;
		return $this;
	}

	public function getElement( $point, $addTab = NULL){
		if($point !== ''){
			if(!is_null($addTab)){
				if(!!$addTab){
					$this->addTab();
					return $this->tab.$point.$this->paragraph;
				}else{
					$html = $this->tab.$point.$this->paragraph;
					$this->removeTab();
					return $html; 
				}
			}
			return $this->tab.$point.$this->paragraph;
		}else{
			return '';
		}
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
	private function validateArrayKey( array $array, $key = 0, $triggerError = true, $errorMessage = NULL ){
		if ( ( !isset( $array[$key] ) ) ||  empty( $array[$key] ) ) { 
			if( $triggerError ) { 
				$this->error($errorMessage);
			}else{ 
				return false;
			}
		}else{ 
			return true;
		}
	}

	private function error( $errorMessage = 'Unknown Error!' ){
		die( $this->ErrorPrefix.$errorMessage.$this->ErrorSuffix );
	}

	/**
	 * Check if array is associative
	 * @param  array   $arr         Array to be verified
	 * @param  boolean $reusingKeys Array can be with repeated keys
	 * @return boolean              True if success else false
	 */
	private function isAssoc(array $arr, $reusingKeys = false ) {
		$range = range(0, count($arr) - 1);
		return $reusingKeys? $arr !== $range : array_keys($arr) !== $range;
	}
}
