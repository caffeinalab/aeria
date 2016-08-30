<?php

if( false === defined('AERIA') ) exit;

class AeriaSection {

	public static function register($args){

		if(empty($args['type'])) die("AeriaSection: You must define a post_type id");
		if(empty($args['title'])) $args['title'] = 'Sections';
		$id_section = (empty($args['id']))?'':$args['id'];

		add_action('add_meta_boxes', function() use ($args, $id_section){
			global $post;
			if (!empty($args['post_id']) && !in_array( $post->ID, (array) $args['post_id'] )){
				return;
			}
			$id_metabox = !empty($id_section)?'aeria_section_'.$id_section:'aeria_section';
			foreach ( (array) $args['type'] as $type ) {
				add_meta_box(
					$id_metabox,
					$args['title'],
					function($post) use ($args) {
						AeriaSection::render_controls($args);
						AeriaSection::render_sections($post->ID,$args);
					},
					$type
				);
			}
		});

		foreach ( (array) $args['type'] as $type ) {
			add_filter( 'postbox_classes_' . $type . '_aeria_section', function( $classes ) use ( $args ) {
				array_push( $classes, 'aeria_section_' . $args['title'] );
				return $classes;
			});
		}

		add_action('save_post', function($post_id) use($args) {

			$id_section = (empty($args['id']))?'':$args['id'].'_';

			if (!isset($_POST['section_metabox_nonce'])){
				return;
			}

			if (!wp_verify_nonce($_POST['section_metabox_nonce'],'section_metabox')){
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
				return;
			}

			if ( !in_array( $_POST['post_type'], (array) $args['type'] ) ) {
				return;
			}

			if (!empty($args['post_id']) && !in_array( $post_id, (array) $args['post_id'] )){
				return;
			}

			if (isset($_POST['post_type']) && 'page' == $_POST['post_type']){
				if(!current_user_can('edit_page', $post_id )){
					return;
				}
			}else{
				if(!current_user_can( 'edit_post', $post_id)){
					return;
				}
			}
			$sections = [];
			$s = 0;

			while ( isset($_POST[$id_section.'post_section_columns_'.$s])) {

				// check columns
				$columns = $_POST[$id_section.'post_section_columns_'.$s];

				$content = [];
				for ($i=1; $i <= $columns ; $i++) {
					if(isset($_POST[$id_section.'post_section_'.$s.'_'.$i])) $content['column_'.$i] = wpautop($_POST[$id_section.'post_section_'.$s.'_'.$i]);
				}

				$sections['section_'.$s] = [
					'columns' =>  $columns,
					'title' => sanitize_text_field($_POST[$id_section.'post_section_title_'.$s]),
					'background' => $_POST[$id_section.'post_section_background_'.$s],
					'content' => $content
				];

				//if exist section type -> save
				if ( count( $args['fields'] ) === 1 ){
					$value_type = array_keys( $args['fields'] )[0];
				}else{
					$value_type = ( isset($_POST[$id_section.'section_type_'.$s] ) && !empty( $_POST[$id_section.'section_type_'.$s] ) )? $_POST[$id_section.'section_type_'.$s] : '' ;
				}
				if( $value_type ) $sections['section_'.$s]['section_type'] = $value_type;

				//save classic fields
				if(isset($args['fields']) && !empty($args['fields']) && isset($args['fields'][0]['type'])){
					foreach ($args['fields'] as $field) {
						$sections['section_'.$s]['fields'][$field['id']] = $_POST[$id_section.$field['id'].'_'.$s];
					}
				}elseif(count($args['fields']) && !empty($value_type)) {
					foreach ($args['fields'][$value_type]['fields'] as $field) {
						$sections['section_'.$s]['fields'][$field['id']] = $_POST[$id_section.$field['id'].'_'.$s];
					}
				}

				$s++;
			}
			$id_meta_section = !empty($id_section)?'_'.str_replace('_','',$id_section):'';

			if(!empty($sections)) update_post_meta( $post_id, 'post_sections'.$id_meta_section, wp_slash(json_encode($sections,JSON_UNESCAPED_UNICODE)) );

		});


		add_action('admin_enqueue_scripts', function(){
			wp_enqueue_style('aeria_section', AERIA_RESOURCE_URL.'css/section.css');
			wp_enqueue_script('aeria_section', AERIA_RESOURCE_URL.'js/section.js');
			wp_enqueue_media();
			wp_localize_script( 'aeria_section', 'meta_image',
	        	[
	            	'title' => 'Choose or Upload an Image',
	            	'button' => 'Use this image'
	            ]
	        );
		});

		add_action( 'wp_ajax_add_section', function() {
			AeriaSection::render_section([],$_POST['section'],$_POST['ncol'],['id' => $_POST['id_section']]); exit;
		});

		add_action( 'wp_ajax_sort_section', function(){
			AeriaSection::sort_section($_POST['order'], $_POST['post_id'],$_POST['id_section']); exit;
		});

	}

	public static function sort_section($order, $post_id, $id_section) {
		$id_meta_section = !empty($id_section)?'_'.$id_section:'';
		$sections = json_decode(get_post_meta( $post_id, 'post_sections'.$id_meta_section, true ),true);

		$new_sections = [];
		$s = 0;

		foreach ($order as $key) {
			$new_sections['section_'.$s] = $sections[$key];
			$s++;
		}

		update_post_meta( $post_id, 'post_sections'.$id_meta_section, wp_slash(json_encode($new_sections,JSON_UNESCAPED_UNICODE)) );

		die(json_encode([
			'success' => 1
		]));
	}

	public static function render_controls($args=[]){

		$id_section = (empty($args['id']))?'':$args['id'];
		$id_metabox = !empty($id_section)?'aeria_section_'.$id_section:'aeria_section';

		?>
		<div class="box-controls">
			<?php if(empty($args['supports']) || in_array('columns', $args['supports'])){ ?>
			<select id="ncol">
				<option value="1">1 Col</option>
				<option value="2">2 Cols</option>
				<option value="3">3 Cols</option>
				<option value="4">4 Cols</option>
			</select>
			<?php }else{ ?>
				<input type="hidden" value="1" id="ncol">
			<?php } ?>
			<button class="button button-primary button-large" type="button" data-section-add="<?= $id_section ?>" ><span class="dashicons dashicons-welcome-add-page"></span> <?= __('Add', 'aeria') ?> <?= $args['title'] ?></button>
			<button class="button button-large" type="button" data-section-expand-all="<?= $id_section ?>" ><span class="dashicons dashicons-editor-expand"></span> <?= __('Expand All', 'aeria') ?> <?= $args['title'] ?></button>
			<button class="button button-large" type="button" data-section-sort="<?= $id_section ?>" ><span class="dashicons dashicons-randomize"></span> <?= __('Reorder/Remove', 'aeria') ?></button>
			<div style="clear:both"></div>
		</div>
		<?php
	}

	public static function render_sections($post_id, $args){

		$id_section = (empty($args['id']))?'':$args['id'];
		$id_meta_section = !empty($id_section)?'_'.$id_section:'';
		$id_metabox = !empty($id_section)?'aeria_section_'.$id_section:'aeria_section';

		$sections = json_decode(get_post_meta( $post_id, 'post_sections'.$id_meta_section, true ),true);

		wp_nonce_field( 'section_metabox', 'section_metabox_nonce' );

		?>
		<div class="box-reorder">
			<p>Prima di utilizzare questa funzionalità assicurati di aver <b>salvato</b> tutto il lavoro.</p>
			<?php
				if($sections){
					$s = 0;
					echo '<ul data-section-sortable data-post-id="'.$post_id.'">';
					foreach ($sections as $key => $section) {
						$title = !empty($section['title'])?stripslashes($section['title']):'Section '.$s;
						echo '<li data-section-id="section_'.$s.'">'.$title.' <button class="button button-small" type="button" data-section-remove="'.$id_section.'" ><span class="dashicons dashicons-trash"></span></button></li>';
						$s++;
					}
					echo '</ul>';
					echo '<button class="button button-large button-primary" type="button" data-section-sortable-save="'.$id_section.'" ><span class="dashicons dashicons-yes"></span> Confirm</button>';

				}
			?>
		</div>
		<div class="box-sections">
			<?php

				if(isset($args['description']) && !empty($args['description'])) echo '<p>'.$args['description'].'</p>';

				if($sections){
					$s = 0;
					foreach ($sections as $key => $section) {
						AeriaSection::render_section($section,$s,$section['columns'],$args);
						$s++;
					}
				}
			?>
		</div>
		<?php
	}

	public static function render_field($field=null,$key=0,$val='',$id_section=''){
		if(!$field) return;

		if(!empty($id_section)) $field['id'] = $id_section.'_'.$field['id'];

		if ($field['type'] == 'hidden'){
			echo '<input type="hidden" value="' . $field['value'] . '" id="' . $field['id'] . '_' . $key . '" name="' . $field['id'] . '_' . $key . '"/> ';
			return;
		}
		
		echo '<div class="row-field">';

		echo '<label for="'.$field['id'].'_'.$key.'">'.$field['name'].'</label>';

		switch ($field['type']) {
			case 'select':
				echo '<select class="select2" id="' . $field['id'] . '_' . $key . '" name="' . $field['id'] . '_' . $key . ( $field['multiple'] ? '[]" multiple="multiple" style="height:auto"' : '"' ) . '>';
				foreach ( $field['options'] as $k => $value ) {
					echo '<option value="' . $k . '" ' . selected( in_array( $k, (array) $val ), true, false ) . '>' . $value . '</option>';
				}
				echo '</select>';
				break;

			case 'text':
				echo '<input type="text" id="'.$field['id'].'_'.$key.'" name="'.$field['id'].'_'.$key.'" value="'.stripslashes($val).'">';
				break;

			case 'textarea':
				echo '<div class="wrap-editor">';
				echo '<textarea rows="5" id="'.$field['id'].'_'.$key.'" name="'.$field['id'].'_'.$key.'">'.stripslashes($val).'</textarea>';
				echo '</div>';
				break;

			case 'wysiwyg':
				echo '<div class="wrap-editor">';
				wp_editor( stripslashes($val) , $field['id'].'_'.$key );
				echo '</div>';
				break;

			case 'media':
				$background = stripslashes($val);
				echo '<div class="wrap-media"><div class="remove_background" data-remove-background>x</div><div class="background" data-section-background style="background-image:url('.$background.');">';
				echo '<input type="hidden" name="'.$field['id'].'_'.$key.'" id="'.$field['id'].'_'.$key.'" value="'.$background.'" />';
				echo '</div></div>';
				break;

			case 'select_ajax':
				echo '<input type="hidden" value="' . html_addslashes($val) . '" id="' . $field['id'] . '_' . $key . '" name="' . $field['id'] . '_' . $key . '" class="input-xlarge select2_ajax" data-relation="' . ( $field['relation']? $field['relation'] : 'page' ) . '" data-placeholder="Select an Option.."'. ( $field['multiple'] ? ' data-multiple="true" style="height:auto"' : ' data-multiple="false"' ) .' /> ';
				break;

			default:
				return null;
				break;
		};
		echo '</div>';
	}

	public static function render_relation_fields($fields=null,$key=0,$val='',$preview_path, $id_section=''){
		if(!$fields) return;

		$field_id = (empty($id_section))?'':$id_section.'_';

		$row_class = !empty($preview_path)?'row-half':'row-full';
		$preview_attr = !empty($preview_path)?'data-select-preview="'.$preview_path.'"':'';

		echo '<div class="'.$row_class.'">';

			echo '<select '.$preview_attr.' id="'.$field_id.'section_type_'.$key.'" name="'.$field_id.'section_type_'.$key.'">';
			echo '<option value="">Seleziona una tipologia di sezione</option>';
			foreach ($fields as $k => $value) {
				$selected = ($val == $k)?'selected="selected"':'';
				echo '<option value="'.$k.'" '.$selected.'>'.$value['description'].'</option>';
			}
			echo '</select>';

			//print buttons | 2 buttons 1 function, i know :)
			echo '<button data-generate-section class="button">Genera campi</button>';
			echo ' <button data-generate-section class="button button-primary">Salva</button>';

		echo '</div>';

		if(!empty($preview_path)){
			echo '<div class="'.$row_class.'"><div class="wrap-preview"></div></div>';
		}

	}

	public static function render_section($section_passed = [], $key = 0, $ncol = 1, $args = []){

		//check supports
		$support_columns = (!isset($args['supports']) || empty($args['supports']) || in_array('columns', $args['supports']));
		$support_fields = (!isset($args['supports']) || empty($args['supports']) || in_array('fields', $args['supports']));
		$preview_path = (isset($args['preview_path']) && !empty($args['preview_path']))?$args['preview_path']:false;

		$id_section = (empty($args['id']))?'':$args['id'];
		$id_meta_section = !empty($id_section)?'_'.$id_section:'';
		$id_input = !empty($id_section)?$id_section.'_':'';

		if(empty($section_passed)) {
			$section = [
				'columns' => $ncol,
				'title' => '',
				'background' => '',
				'content' => [
					'column_1' => ''
				]
			];

		}else{
			$section = [
				'columns' => $section_passed['columns'],
				'title' => $section_passed['title'],
				'background' => $section_passed['background'],
			];

			for ($i=1; $i <= $section_passed['columns']; $i++) {
				$section['content']['column_'.$i] = $section_passed['content']['column_'.$i];
			}

			if(!empty($section_passed['fields'])){
				$section['fields'] = $section_passed['fields'];
			}

			if(!empty($section_passed['section_type'])){
				$section['section_type'] = $section_passed['section_type'];
			}
		}

	?>
		<div class="box-section<?= ($section_passed['section_type']) ? ' section_' . $section_passed['section_type'] : '' ?>" data-section-num=<?= $key ?>>
			<input type="hidden" data-section-columns="<?= $id_section ?>" name="<?= $id_input ?>post_section_columns_<?= $key ?>" value="<?= $section['columns'] ?>">
			<div class="header-section" >
				<div class="remove_background" data-remove-background="<?= $id_section ?>">x</div>
				<div class="background" data-section-background="<?= $id_section ?>" style="background-image:url(<?= stripslashes($section['background']) ?>);">
					<input type="hidden" name="<?= $id_input ?>post_section_background_<?= $key ?>" id="<?= $id_input ?>post_section_background_<?= $key ?>" value="<?= $section['background'] ?>" />
				</div>
				<div class="title">
					<input type="text" placeholder="Here the title" class="post_section_title" name="<?= $id_input ?>post_section_title_<?= $key ?>" id="<?= $id_input ?>post_section_title_<?= $key ?>" value="<?= stripslashes($section['title']) ?>" >
				</div>
				<div class="controls">
					<button class="button button-small" data-section-expand="<?= $id_section ?>" ><span class="dashicons dashicons-welcome-write-blog"></span></button>
				</div>
				<div style="clear:both;"></div>
			</div>
			<div class="body-section">
				<?php

					/**
					 * check pre render field
					 * classic or conditional fields?
					 */

					if( isset( $args['fields'] ) && !empty( $args['fields'] ) ){
						if( isset( $args['fields'][0]['type'] ) ){

							/**
							 * Classic list fields
							 */
							echo '<div class="wrap-fields">';
							foreach ( $args['fields'] as $field ) {
								$value = (isset($section['fields'][$field['id']]) && !empty($section['fields'][$field['id']]))?$section['fields'][$field['id']]:'';
								AeriaSection::render_field($field,$key,$value,$id_section);
							}
							echo '</div>';
						}elseif( count( $args['fields'] ) ) {

							/**
							 * Relation list fields
							 */
							if( count( $args['fields'] ) === 1 ){
								$value_type = current(array_keys($args['fields']));
							}else{
								$value_type = isset( $section['section_type'] )?$section['section_type']:'';	// get value from general section settings
								AeriaSection::render_relation_fields( $args['fields'],$key,$value_type,$preview_path,$id_section );
							}

							if(!empty($value_type)){
								echo '<div class="row-full"><h4>'.strtoupper( $args['fields'][$value_type]['description'] ).'</h4></div>';
								echo '<div class="wrap-fields">';
								foreach ($args['fields'][$value_type]['fields'] as $field) {
									$value = ( isset( $section['fields'][$field['id']] ) && !empty( $section['fields'][$field['id']]))?$section['fields'][$field['id']]:'';
									AeriaSection::render_field( $field,$key,$value,$id_section );
								}
								echo '</div>';
							}else{
								echo '<div class="row-full"><p>Nessuna tipologia di sezione attiva</p></div>';
							}


						}
					}

					if($support_columns){
						for ($i=1; $i <= $section['columns']; $i++) {
							if($section['columns'] > 1) echo '<h2>Column '.$i.'</h2>';
						 	wp_editor( stripslashes($section['content']['column_'.$i]) , $id_meta_section.'post_section_'.$key.'_'.$i );
						}
					}
				?>
			</div>
		</div>
	<?php
	}
}