<?php

if( false === defined('AERIA') ) exit;

class AeriaMetaBox {

	protected static $groups = [],
					 $defs   = [];

	public static function register($metabox){
		if(false===is_array($metabox)) exit;

		if(empty($metabox['id'])){
			foreach($metabox as $meta_id => $meta){
				static::$defs[$meta_id]		= $meta;
				static::$groups[$meta_id] 	= new Meta_Box($meta);
			}
		} else {
			static::$defs[$metabox['id']]	= $metabox;
			static::$groups[$metabox['id']] = new Meta_Box($metabox);
		}
	}

	public static function infoForField($post_type,$field_name){
		foreach (static::defsForType($post_type) as $def){
			foreach ($def['fields'] as $field) {
				if($field['id']==$field_name){
					return $field;
				}
			}
		}
	}

	public static function boxesForType($post_type){
		$res = [];
		foreach (static::$groups as $id => $box) {
			if(in_array($post_type, (array)$box->_meta_box['pages'])) $res[] = $box;
		}
		return $res;
	}

	public static function defsForType($post_type){
		$res = [];
		foreach (static::$defs as $id => $def) {
			if(in_array($post_type, (array)$def['pages'])) $res[] = $def;
		}
		return $res;
	}


	public static function add_script_date(){
		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_style('rw-datetime', AERIA_RESOURCE_URL.'css/bootstrap-datetimepicker.css');
			wp_enqueue_script('moment', AERIA_RESOURCE_URL.'js/moment.min.js');
			wp_enqueue_script('rw-datetime', AERIA_RESOURCE_URL.'js/bootstrap-datetimepicker.js');
		});
	}

	public static function add_script_select(){
		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_style('select2', AERIA_RESOURCE_URL.'css/select2.css');
			wp_enqueue_style('select2-bootstrap', AERIA_RESOURCE_URL.'css/select2-bootstrap.css');

			wp_enqueue_script('select2', AERIA_RESOURCE_URL.'js/select2.min.js');
			wp_enqueue_script('select2-aeria', AERIA_RESOURCE_URL.'js/select2-aeria.js');
		});
	}

	public static function add_bootstrap_script() {
		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_style('bootstrap', AERIA_RESOURCE_URL.'css/bootstrap.css');
			wp_enqueue_script('bootstrap', AERIA_RESOURCE_URL.'js/bootstrap.min.js');
		});
	}

	public static function add_script_uploads(){
		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_script('uploads', AERIA_RESOURCE_URL.'js/uploads.js');
		});
	}

	public static function add_script_sortable(){
		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_script('jquery-ui-sortable', AERIA_RESOURCE_URL.'js/jquery-ui-sortable.js');
		});
	}

	public static function get_attachment_id_from_src($image_src) {
		global $wpdb;
		// Strip domain
    $image_src = preg_replace('{^https?://[^/]+}', '', $image_src);

    $query = "SELECT ID FROM {$wpdb->posts} WHERE guid LIKE '%$image_src%'";
		$id = $wpdb->get_var($query);
		return $id;

	}


}

add_action('wp_ajax_mbox_delete_file', function() {
	if (!isset($_POST['data'])) return;
	list($post_id, $key, $attach_id, $src, $nonce) = explode('!', $_POST['data']);
	if (!wp_verify_nonce($nonce, 'mbox_ajax_delete_file')) {
		_e('You don\'t have permission to delete this file.');
	}
	wp_delete_attachment($attach_id);
	delete_post_meta($post_id, $key, $src);
	_e('File has been successfully deleted.');
	die();
});

add_action('admin_enqueue_scripts', function(){
	wp_enqueue_style('main', AERIA_RESOURCE_URL.'css/main.css');
});

class Meta_Box {

	public $_meta_box;
	public $_fields;

	// Create meta box based on given data
	function __construct($meta_box) {
		if (!is_admin()) return;

		// assign meta box values to local variables and add it's missed values
		$this->_meta_box = $meta_box;
		$this->_fields = & $this->_meta_box['fields'];
		$this->add_missed_values();

		add_action('admin_menu', array(&$this, 'add'));	// add meta box
		add_action('save_post', array(&$this, 'save'));	// save meta box's data

		add_action('admin_head', function(){
			static $done = false;
			if(!$done) {
				echo '<script type="text/javascript">jQuery(function(){
				window["aeria_init_select2"]?window["aeria_init_select2"]():false;
				window["aeria_setup_media_gallery_fields"]?window["aeria_setup_media_gallery_fields"]():false;
				window["aeria_setup_media_upload_fields"]?window["aeria_setup_media_upload_fields"]():false;
				});</script>';

				$done = true;
			}
		});

		AeriaMetabox::add_script_uploads();
		AeriaMetabox::add_bootstrap_script();

		// check for some special fields and add needed actions for them
		$this->check_field_upload();
		$this->check_field_datetime();
		$this->check_field_daterange();

	}

	/******************** BEGIN UPLOAD **********************/


	/**
	 * Check field upload/file and add needed actions
	 * @return [type] [description]
	 */
	function check_field_upload() {
		if ($this->has_field('image') || $this->has_field('file')) {
			add_action('post_edit_form_tag', array(&$this, 'add_enctype')); // add data encoding type for file uploading
			add_action('admin_head-post.php', array(&$this, 'add_script_upload')); // add scripts for handling add/delete images
			add_action('admin_head-post-new.php', array(&$this, 'add_script_upload'));
			add_action('delete_post', array(&$this, 'delete_attachments')); // delete all attachments when delete post
		}
	}

	/**
	 * Add data encoding type for file uploading
	 */
	function add_enctype() {
		echo ' enctype="multipart/form-data"';
	}

	/**
	 * Add scripts for handling add/delete images
	 */
	function add_script_upload() {
		echo '
		<script type="text/javascript">
		jQuery(document).ready(function($) {
		    jQuery(".dropdown-menu").addClass("twbootstrap");

			$(".rw-add-file").click(function(){
					var $first = $(this).parent().find(".file-input:first");
					$first.clone().insertAfter($first).show();
					return false;
				});
			$(".rw-delete-file").click(function(){
				var $parent = $(this).parent(),
				data = $(this).attr("rel");
				$.post(ajaxurl, {action: \'mbox_delete_file\', data: data}, function(response){
					$parent.fadeOut("slow");
					alert(response);
				});
				return false;
			});
		});
		</script>';
	}

	/**
	 * Delete all attachments when delete post
	 * @param  int $post_id The number of the post
	 */
	function delete_attachments($post_id) {
		$attachments = get_posts(array(
			'numberposts' => -1,
			'post_type' => 'attachment',
			'post_parent' => $post_id
		));
		if (!empty($attachments)) {
			foreach ($attachments as $att) {
				wp_delete_attachment($att->ID);
			}
		}
	}

	/******************** END UPLOAD **********************/

	/******************** DATETIME **********************/

	function check_field_datetime() {
		if ($this->has_field('datetime') && $this->is_edit_page()) {

			AeriaMetaBox::add_script_date();
			add_action('admin_head', function(){
				$elements = array();
				echo '<script type="text/javascript">jQuery(document).ready(function($){';
				foreach ($this->_fields as $field) {
					if ('datetime' == $field['type'] || 'date' == $field['type'] || 'time' == $field['type']) {
						$id = $field['id'];

						$date = ($field['type']=='datetime' || $field['type']=='date')?'true':'false';
						$time = ($field['type']=='datetime' || $field['type']=='time')?'true':'false';

						$start = isset($field['start'])?$field['start']:'-Infinity';
						$end = isset($field['end'])?$field['end']:'Infinity';

						echo "if($('#{$id}_field').length){";
						echo "$('#{$id}_field').datetimepicker({
						    language: \"it\",
						    maskInput: true,
			  	          	pickDate: $date,
			  	          	pickTime: $time,
			  	          	startDate: $start,
			  	          	endDate: $end,
			  	          	pick12HourFormat: false
						}).data('DateTimePicker').widget.wrap('<div class=\"aeria-container\"></div>');";
						echo "}";
					}
				}
				echo '});</script>';
			});
		}
	}

	/******************** END DATETIME **********************/

	/******************** DATERANGE **********************/

	function check_field_daterange() {
		if ($this->has_field('daterange') && $this->is_edit_page()) {

			AeriaMetaBox::add_script_date();
			add_action('admin_head', function(){
				$elements = array();

				$pickTime = 'false';

				if (isset($this->_fields['time']) && $this->_fields['time']===true) {
					$pickTime = 'true';
				}

				echo '<script type="text/javascript">jQuery(document).ready(function($){';
				foreach ($this->_fields as $field) {
					if ('daterange' == $field['type']) {
						$id = $field['id'];
						echo "if($('#{$id}_field_start').length){";
						echo "$('#{$id}_field_start').datetimepicker({
						    language: \"it\",
						    maskInput: true,
			  	          	pickDate: true,
			  	          	pickTime: ".$pickTime.",
			  	          	pick12HourFormat: false
						}).data('DateTimePicker').widget.wrap('<div class=\"aeria-container\"></div>');";

						echo "$('#{$id}_field_end').datetimepicker({
						    language: \"it\",
						    maskInput: true,
			  	          	pickDate: true,
			  	          	pickTime: ".$pickTime.",
			  	          	pick12HourFormat: false
						}).data('DateTimePicker').widget.wrap('<div class=\"aeria-container\"></div>');";
						echo "}";
					}
				}
				echo '});</script>';
			});
		}
	}

	/******************** END DATERANGE **********************/

	/******************** BEGIN META BOX PAGE **********************/

	// Add meta box for multiple post types
	function add() {
		foreach ($this->_meta_box['pages'] as $page) {
			add_meta_box($this->_meta_box['id'], $this->_meta_box['title'], array(&$this, 'show'), $page, $this->_meta_box['context'], $this->_meta_box['priority']);
		}
	}

	// Callback function to show fields in meta box
	function show() {
		global $post;
		if (isset($this->_meta_box['slug']) && $this->_meta_box['slug'] !== $post->post_name) {
			echo '<style>.postbox#'.$this->_meta_box['id'].'{display:none;}</style>';
			return;
		}
		wp_nonce_field(basename(__FILE__), 'mbox_meta_box_nonce');
		echo '<table class="form-table ">';
		foreach ($this->_fields as &$field) {
			//if( $field['type'] == 'gallery' ) $field['multiple'] = true;
			$meta = get_post_meta($post->ID, $field['id'], !$field['multiple']);
			$meta = isset($meta) ? $meta : $field['std'];

			echo '<tr>';
				// call separated methods for displaying each type of field
				call_user_func(array(&$this, 'show_field_' . $field['type']), $field, $meta);
			echo '</tr>';
		}
		echo '</table>';
	}

	/******************** END META BOX PAGE **********************/

	/******************** BEGIN META BOX FIELDS **********************/

	function show_field_begin($field, $meta) {
		echo "<div class='aeria-container'>
				<div class='container'>
				<div class='row'>
					<div class='col-md-4'><label for='{$field['id']}'>{$field['name']}</label></div>
					<div class='col-md-8'>";
	}

	function show_field_end($field, $meta) {
		echo "</div></div></div></div>";
	}

	function show_field_text($field, $meta) {
		$this->show_field_begin($field, $meta);
		$input_type = ($_=&$field['input_type']?:'text');
		echo "<input type='{$input_type}' name='{$field['id']}' id='{$field['id']}' value='$meta'/>";
		$this->show_field_end($field, $meta);
	}

	function show_field_date($field, $meta) {
		$this->show_field_datetime($field, $meta);
	}

	function show_field_time($field, $meta) {
		$this->show_field_datetime($field, $meta);
	}

	function show_field_datetime($field, $meta) {
		$this->show_field_begin($field, $meta);
		$icon = ($field['type']=='datetime'?'calendar':($field['type']=='time'?'time':'calendar'));
		echo '
		<span class="twbootstrap">
		<div class="form-group" style="margin:0;">
			<div class="input-group date" id="'.$field['id'].'_field">
				<input type="text" class="form-control" id="'.$field['id'].'" name="'.$field['id'].'" value="'.$meta.'" />
				<span class="input-group-addon"><span class="glyphicon glyphicon-'.$icon.'"></span></span>
			</div>
		</div>
		<span>
		';
		$this->show_field_end($field, $meta);
	}

	function show_field_daterange($field, $meta) {
		$this->show_field_begin($field, $meta);

		$meta 	= explode('|',$meta);
		$start 	= current($meta);
		$end 	= end($meta);

		echo '
		<span class="twbootstrap">
		<div class="form-group" style="margin:0;">
			<div class="pull-left input-group date" id="'.$field['id'].'_field_start" style="width:200px">
				<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
				<input type="text" class="form-control" id="'.$field['id'].'_start" name="'.$field['id'].'[]" value="'.$start.'" />
			</div>
			<div class="pull-left" style="width:50px;height:1px;text-align:center;line-height:34px;">A</div>
			<div class="pull-left input-group date" id="'.$field['id'].'_field_end" style="width:200px">
				<input type="text" class="form-control" id="'.$field['id'].'_end" name="'.$field['id'].'[]" value="'.$end.'" />
				<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
			</div>
		</div>
		</span>
		';
		$this->show_field_end($field, $meta);
	}

	function show_field_media($field, $meta) {
		$this->show_field_gallery($field, $meta, $single=true);
	}

	function show_field_gallery($field, $metas, $single=false) {
		wp_enqueue_media();
		if(empty($metas)) $metas = [''];

		$idx = 0;
		$this->show_field_begin($field, $metas);
		$layout = (isset($field['layout'])&&$field['layout'])!=''?$field['layout']:'preview';

		if($layout=='list') {
				foreach ((array)$metas as $meta) {
				echo "<table class=\"media aeria-media-gallery-".$field['id']." item_".$idx."\" width=\"100%\"><tr><td><img id='".$field['id'].'_'.$idx."_image' src='".$meta."'/>";
				echo "</td><td><input type='text' name='".$field['id']."[]' id='".$field['id'].'_'.$idx."' value='".$meta."'/>";
				echo "<a class='button button-primary data-type='list' aeria_upload_media_gallery_button' id='".$field['id']."_button' data-target='#".$field['id'].'_'.$idx."'>Select</a>";
				echo "<a class='button button-secondary' onclick=\"jQuery('.aeria-media-gallery-".$field['id'].".item_".$idx."').remove();\">Remove</a>";
				echo "</td></tr></table>";
				$idx++;
			}
			echo "<a class='button button-primary button-add' data-target='aeria-media-gallery-".$field['id']."' onclick=\"var x=jQuery('.aeria-media-gallery-".$field['id']." tr:eq(0)'),v=x.clone();v.find('input').val('');v.find('img').attr('src','');x.before(v);window.aeria_setup_media_gallery_fields();\"><b>+&nbsp;&nbsp;Add Item</b></a>";
			echo "<script>jQuery(function(){window.aeria_setup_media_gallery_fields();})</script>";
		}
		if($layout=='preview') {

			if(!$single) AeriaMetaBox::add_script_sortable();
			if($single) {
				$num_class='single';
			}else{
				$num_class='multi';
			}

			echo '<div class="aeria-media-gallery-'.$field['id'].'">';

			foreach ((array)$metas as $meta) {
				$media_id = AeriaMetaBox::get_attachment_id_from_src($meta);

				if($media_id){
					$url_edit = 'post.php?post='.$media_id.'&action=edit';
					$meta_type = get_post_mime_type($media_id);
					$meta_title = get_the_title($media_id);
				}else{
					$url_edit = '';
					$meta_type = '';
					$meta_title = '';
				}

        		$background = '';
        		$class_background = 'file';

				switch ($meta_type) {
					case 'image/jpeg':
				  case 'image/png':
				  case 'image/gif':
				     	$background = $meta;
				     	$class_background = 'image';
				      break;
				}

				$hidden_class = $meta==''?'display:none;':'';
				echo '<div class="box-image item_'.$idx.'" style="'.$hidden_class.'">';
				echo "<div class='image ".$num_class." ".$class_background."' style='background:url(".$background.");'>";
				if($class_background=='file'){
					echo "<h4>".(wp_trim_words($meta_title,8)?:'<i>Untitled</i>')."</h4>";
				}
				echo "</div>";
				echo "<div class='box-controls'>
						<a class='button button-remove'><i class='glyphicon glyphicon-trash'></i></a>
						<a class='button button-edit' target='_blank' href='".$url_edit."'><i class='glyphicon glyphicon-pencil'></i></a>
					</div>";
				echo "<input type='hidden' name='".$field['id']."[]' value='".$meta."'/>";
				echo '</div>';
				$idx++;
			}
			echo "<a class='box-image add aeria_upload_media_gallery_button' data-meta_type='".$class_background."' data-num='".$num_class."' data-type='preview' data-target='aeria-media-gallery-".$field['id']."'>";
				if(!$single) {
					echo "<i class='glyphicon glyphicon-plus-sign'></i>";
				}else{
					echo "<i class='glyphicon glyphicon-retweet'></i>";
				}
				echo "</a>";
			echo "<script>jQuery(function(){window.aeria_setup_media_gallery_fields();})</script>";
			echo '</div>';
		?>
			<?php if(!$single): ?>
				<script type="text/javascript">
					jQuery(document).ready(function($){
						$('.aeria-media-gallery-<?= $field['id'] ?>').sortable();
					});
				</script>
			<?php endif; ?>
		<?php
		}

		$this->show_field_end($field, $metas);
	}

	function show_field_textarea($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<textarea name='{$field['id']}' rows='15'>$meta</textarea>";
		$this->show_field_end($field, $meta);
	}

	function show_field_html($field, $meta) {
		echo '<td colspan="2">';
		echo is_callable($field['content'])?call_user_func($field['content']):$field['content'];
		echo "</td>";
	}

	function show_field_select($field, $meta) {
		if (!is_array($meta)) $meta = (array) $meta;
		$this->show_field_begin($field, $meta);

		echo "<select class=\"select2\" data-minimum='".(isset($field['minimum'])?$field['minimum']:'0' )."' name='{$field['id']}" . ($field['multiple'] ? "[]' multiple='multiple' style='height:auto'" : "'") . ">";

		if(is_callable($field['options'])) $field['options'] = call_user_func($field['options']);

		foreach ($field['options'] as $key => $value) {
			if(is_array($value)){
				$img = isset($value['image'])?' data-image="'.$value['image'].'" ':'';
				$html = isset($value['html'])?' data-html="'.$value['html'].'" ':'';
				$text = isset($value['text'])?$value['text']:(isset($value['html'])?$value['html']:'');
			} else {
				$img = $html = '';
				$text=$value;
			}
			echo "<option value='",$key,"'",$img,$html,selected(in_array($key,$meta),true,false),">",$text,"</option>";
		}

		echo "</select>";
		$this->show_field_end($field, $meta);
	}

	function show_field_select_ajax($field, $meta) {
		if (!is_array($meta)) $meta = (array) $meta;
		$meta_value = empty($meta)?'':$meta[0];
		$this->show_field_begin($field, $meta);
		echo "<input type='hidden' value='{$meta_value}' name='{$field['id']}'  class='input-xlarge select2_ajax' data-relation='{$field['relation']}' data-placeholder='Select an Option..'". ($field['multiple'] ? " data-multiple='true' style='height:auto'" : "data-multiple='false'") ." /> ";
		$this->show_field_end($field, $meta);
	}

	function show_field_radio($field, $meta) {
		$this->show_field_begin($field, $meta);
		foreach ($field['options'] as $key => $value) {
			echo "<div class='box-radio'><input type='radio' name='{$field['id']}' value='$key'" . checked($meta, $key, false) . " /><label>".$value."</label></div>";
		}
		$this->show_field_end($field, $meta);
	}

	function show_field_checkbox($field, $meta) {
		$this->show_field_begin($field, $meta);
		echo "<input type='checkbox' name='{$field['id']}'" . checked(!empty($meta), true, false) . " />{$field['desc']}</td>";
	}

	function show_field_wysiwyg($field, $meta) {
		$this->show_field_begin($field, $meta);
		wp_editor( $meta, $field['id'] );
		$this->show_field_end($field, $meta);
	}

	function show_field_posts($field, $meta) {
		global $post;
		$this->show_field_begin($field, $meta);

		$conditions = empty($field['conditions'])?array():$field['conditions'];
		foreach($conditions as $condition => &$value){
			switch ($condition) {
				case 'only_childs':
					$condition = 'post_parent';
					if($value && $post){
						$value = $post->ID;
					} else {
						$value = false;
					}
					unset($conditions['only_childs']);
					break;
			}
		}

		$data = get_posts(array_merge_replace(array(
			'numberposts' => -1,
			'post_status' => 'publish',
			'post_type' => $field['postTypes'],
		),$conditions));


		echo "<select class='select2' data-minimum='".(isset($field['minimum'])?$field['minimum']:'0' )."' name='{$field['id']}' ".(isset($field['single'])&&$field['single'] ? '' :"multiple style='height:auto'") . ">";

		if(is_callable($field['filter'])) $data = array_filter($data,$field['filter']);

		$ids = is_array($meta)?$meta:explode(',',$meta);

		foreach ($data as $thePost) {
			$key = $thePost->ID;
			$value = $thePost->post_title;
			echo "<option value='$key'" . selected(in_array($key, $ids), true, false) . ">".$value."</option>";
		}
		echo "</select>";


		$this->show_field_end($field, $meta);
	}


	function show_field_checkbox_list($field, $meta) {
		if (!is_array($meta)) $meta = (array) $meta;
		$this->show_field_begin($field, $meta);
		$html = array();
		foreach ($field['options'] as $key => $value) {
			$html[] = "<input type='checkbox' name='{$field['id']}[]' value='$key'" . checked(in_array($key, $meta), true, false) . " /> $value";
		}
		echo implode('<br />', $html);
		$this->show_field_end($field, $meta);
	}


	/******************** END META BOX FIELDS **********************/

	/******************** BEGIN META BOX SAVE **********************/

	// Save data from meta box
	function save($post_id) {
		if(empty($_POST['post_type']) || empty($_POST['post_ID'])) return;

		$post_type_object = get_post_type_object($_POST['post_type']);

		if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)                     						// check autosave
		|| (!isset($_POST['post_ID']) || $post_id != $_POST['post_ID'])       			// check revision
		|| (!in_array($_POST['post_type'], $this->_meta_box['pages']))        			// check if current post type is supported
		|| (!check_admin_referer(basename(__FILE__), 'mbox_meta_box_nonce'))  	// verify nonce
		|| (!current_user_can($post_type_object->cap->edit_post, $post_id))) {	// check permission
		                                                                      	return $post_id;
		}

		foreach ($this->_fields as $field) {
			$name = $field['id'];
			$type = $field['type'];

			if($type == 'html') continue;

			$old = get_post_meta($post_id, $name, !$field['multiple']);
			$new = isset($_POST[$name]) ? $_POST[$name] : ($field['multiple'] ? array() : '');

			// validate meta value
			if (class_exists('Meta_Box_Validate') && method_exists('Meta_Box_Validate', $field['validate_func'])) {
				$new = call_user_func(array('Meta_Box_Validate', $field['validate_func']), $new);
			}

			// call defined method to save meta value, if there's no methods, call common one
			$save_func = 'save_field_' . $type;
			if (method_exists($this, $save_func)) {
				call_user_func(array(&$this, 'save_field_' . $type), $post_id, $field, $old, $new);
			} else {
				$this->save_field($post_id, $field, $old, $new);
			}
		}
	}

	// Common functions for saving field
	function save_field($post_id, $field, $old, $new) {
		$name = $field['id'];

		// single value
		if (!$field['multiple']) {
			if ('' != $new && $new != $old) {
				update_post_meta($post_id, $name, $new);
			} elseif ('' == $new) {
				delete_post_meta($post_id, $name, $old);
			}
			return;
		}

		// multiple values
		// get new values that need to add and get old values that need to delete
		$add = array_diff((array)$new, (array)$old);
		$delete = array_diff((array)$old, (array)$new);
		foreach ($add as $add_new) {
			add_post_meta($post_id, $name, $add_new, false);
		}
		foreach ($delete as $delete_old) {
			delete_post_meta($post_id, $name, $delete_old);
		}
	}

	function save_field_textarea($post_id, $field, $old, $new) {
		$new = htmlspecialchars($new);
		$this->save_field($post_id, $field, $old, $new);
	}

	function save_field_daterange($post_id, $field, $old, $new) {
		$this->save_field($post_id, $field, $old, implode('|',$new));
	}

	function save_field_media($post_id, $field, $old, $new) {
		$this->save_field($post_id, $field, $old, $new);
	}

	function save_field_gallery($post_id, $field, $old, $new) {
		$this->save_field($post_id, $field, $old, $new);
	}

	function save_field_posts($post_id, $field, $old, $new) {
		$this->save_field($post_id, $field, $old, $new );
	}

	function save_field_wysiwyg($post_id, $field, $old, $new) {
		$new = wpautop($new);
		$this->save_field($post_id, $field, $old, $new);
	}

	function save_field_file($post_id, $field, $old, $new) {
		$name = $field['id'];
		if (empty($_FILES[$name])) return;

		$this->fix_file_array($_FILES[$name]);

		foreach ($_FILES[$name] as $position => $fileitem) {
			$file = wp_handle_upload($fileitem, array('test_form' => false));

			if (empty($file['file'])) continue;
			$filename = $file['file'];

			$attachment = array(
                    'post_mime_type' => $file['type'],
                    'guid' => $file['url'],
                    'post_parent' => $post_id,
                    'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                    'post_content' => ''
            );
			$id = wp_insert_attachment($attachment, $filename, $post_id);
			if (!is_wp_error($id)) {
				wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $filename));
				add_post_meta($post_id, $name, $file['url'], false);	// save file's url in meta fields
			}
		}
	}

	// Save images, call save_field_file, cause they use the same mechanism
	function save_field_image($post_id, $field, $old, $new) {
		$this->save_field_file($post_id, $field, $old, $new);
	}

	/******************** END META BOX SAVE **********************/

	/******************** BEGIN HELPER FUNCTIONS **********************/

	// Add missed values for meta box
	function add_missed_values() {
		// default values for meta box
		$this->_meta_box = array_merge(array(
           'context' => 'normal',
           'priority' => 'high',
           'pages' => array('post')
        ), $this->_meta_box);

		// default values for fields
		foreach ($this->_fields as $key => $field) {
			$multiple = in_array($field['type'], array('checkbox_list', 'file', 'image')) ? true : false;
			$std = $multiple ? array() : '';
			$format = 'date' == $field['type'] ? 'yy-mm-dd' : ('time' == $field['type'] ? 'hh:mm' : '');
			$this->_fields[$key] = array_merge(array(
	           'multiple' => $multiple,
	           'std' => $std,
	           'desc' => '',
	           'format' => $format,
	           'validate_func' => ''
	           ), $field);
		}
	}

	// Check if field with $type exists
	function has_field($type) {
		foreach ($this->_fields as $field) {
			if ($type == $field['type']) return true;
		}
		return false;
	}

	// Check if current page is edit page
	function is_edit_page() {
		global $pagenow;
		if (in_array($pagenow, array('post.php', 'post-new.php'))) return true;
		return false;
	}

	/**
	 * Fixes the odd indexing of multiple file uploads from the format:
	 *     $_FILES['field']['key']['index']
	 * To the more standard and appropriate:
	 *     $_FILES['field']['index']['key']
	 */
	function fix_file_array(&$files) {
		$output = array();
		foreach ($files as $key => $list) {
			foreach ($list as $index => $value) {
				$output[$index][$key] = $value;
			}
		}
		$files = $output;
	}

	/******************** END HELPER FUNCTIONS **********************/
}