<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaWidgetElement extends WP_Widget {
		protected $info = '';
		protected $options = [];
		protected $data = null;

	    function __construct() {
	    	$this->info = array_merge([
	        	'view'     => function($args,$data){},
	        	'slug'     => '',
	        	'title'    => '',
	        	'options'  => function($instance,$data){},
	        	'params'   => function($args,$data){return $args;},
	        ],AeriaWidget::$widgets[get_called_class()]);

	        $this->data = empty($this->info['data'])?[]:(is_callable($this->info['data'])?call_user_func($this->info['data']):$this->info['data']);

	    	if(empty($this->info['slug'])) $this->info['slug'] = 'aeria_anonim_widget_'.(AeriaWidget::$anonWidgets++);

	    	if(is_callable($this->info['options'])) $this->info['options']($this,$this->data);
	    	if(!is_callable($this->info['params'])) $this->info['params'] = function($args,$data){return $args;};

	        parent::__construct( false, $this->info['title'] );
	    }

	    function widget( $args, $instance ) {
        	$this->info['view'](array_merge($instance,$this->info['params']($args,$this->data)),$this->data);
	    }

	    function getWidget( $args, $instance ) {
			$this->info['view'](array_merge($instance,$this->info['params']($args,$this->data)),$this->data);
	    }

	    function update( $new_instance, $old_instance ) {
	        return $new_instance;
	    }

	    function addOption( $type, $name, $key, $desc = '', $default = null, $default2 = null ) {
	    	$this->options[] = array(
	    		'type' => $type,
	    		'name' => $name,
	    		'key' => $key,
	    		'desc' => $desc,
	    		'defaults' => is_array($default)?$default2:$default,
	    		'values' => is_array($default)?$default:$default2,
	    	);
	    }

	    function addOptionText( $name, $key, $desc, $default ) {
		    $this->addOption( 'text', $name, $key, $desc, $default );
	    }
	    function addOptionCheckbox( $name, $key, $desc, $default ) {
		    $this->addOption( 'check', $name, $key, $desc, $default );
	    }
	    function addOptionSelect( $name, $key, $desc, $values, $default ) {
		    $this->addOption( 'select', $name, $key, $desc, $values, $default );
	    }
	    function addOptionTextarea( $name, $key, $desc, $default ) {
		    $this->addOption( 'textarea', $name, $key, $desc, $default );
	    }

	    function form( $instance ) {
	    	echo '<table style="width:100%;">';
	    	foreach($this->options as $option){
	    		$opt = (object)$option;
	    		$param_value = esc_attr($instance[$opt->key]);
    			if(empty($param_value)) $param_value = $opt->defaults;
    			$id = 'w_'.md5(serialize($instance)).'_'.$this->get_field_id($opt->key);
    			$name = $this->get_field_name($opt->key);
    			$label = $opt->name;
	    		switch($opt->type){
		    		case 'select':
		    		default:
			    		echo '<tr><td valign=middle align=right><label style="font-weight:bold" for="',$id,'">',$label,':</label></td><td>';
			    		echo '<select class="" id="',$id,'" name="',$name,'" style="width:100%;">';
			    		foreach($opt->values as $name=>$value){
			    			echo '<option value="',$value,'"',($value==$param_value?' selected':''),'>',$name,'</option>';
			    		}
			    		echo '</select>';
			    		echo '</td></tr>';
		    		break;
		    		case 'check':
			    		echo '<tr><td valign=middle align=right><label style="font-weight:bold" for="',$id,'">',$label,':</label></td><td>';
			    		echo '<input style="width:100%;" type="checkbox" id="',$id,'" name="',$name,'" value="1" ',($param_value?'checked="checked"':''),'>';
			    		echo '</td></tr>';
		    		break;
		    		case 'textarea':
			    		echo '<tr><td valign=top align=right><label style="font-weight:bold" for="',$id,'">',$label,':</label></td><td>';
			    		echo '<textarea style="width:100%;height:180px;font-size:11px;font-family:monaco;" id="',$id,'" name="',$name,'">',$param_value,'</textarea>';
			    		echo '</td></tr>';
		    		break;
		    		case 'text':
		    		default:
			    		echo '<tr><td valign=middle align=right><label style="font-weight:bold" for="',$id,'">',$label,':</label></td><td>';
			    		echo '<input style="width:100%;" type="text" id="',$id,'" name="',$name,'" value="',$param_value,'">';
			    		echo '</td></tr>';
		    		break;
	    		}
	    	}
	   	echo '</table>';
   }
}


class AeriaWidget {
	static $widgets = [];
	static $anonWidgets = 0;

	public static function getDefinedWidgets(){
		$res = []; foreach (static::$widgets as $wdg) $res[$wdg['title']] = $wdg['class'];
		return $res;
	}

   	public static function add($widget){

   		// Make id an alias of slug, and support anonymous widgets
   		if(empty($widget['slug']))
   			$widget['slug'] = (empty($widget['id'])?'anon_aeria_widget_'.(static::$anonWidgets++):$widget['id']);

		$widget['class'] = $widget_class_name = 'AeriaWidget_'.trim($widget['slug']);
		static::$widgets[$widget_class_name] = $widget;
		add_action( 'widgets_init', function() use ($widget) {
			eval('class '.$widget['class'].' extends AeriaWidgetElement {}');
			register_widget( $widget['class'] );
		});
   	}

}
