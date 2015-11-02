<?php

if( false === defined('AERIA') ) exit;

class AeriaSection {

	public static function register($args){

		if(empty($args['type'])) die("AeriaSection: You must define a post_type id");

		add_action('add_meta_boxes', function() use ($args){
			add_meta_box(
				'aeria_section',
				'Sections',
				function($post) use ($args){
					AeriaSection::render_controls();
					AeriaSection::render_sections($post->ID,$args);
				},
				$args['type']
			);
		});


		add_action('save_post', function($post_id){

			if (!isset($_POST['section_metabox_nonce'])){
				return;
			}

			if (!wp_verify_nonce($_POST['section_metabox_nonce'],'section_metabox')){
				return;
			}

			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ){
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
			while ( isset($_POST['post_section_columns_'.$s])) {

				// check columns
				$columns = $_POST['post_section_columns_'.$s];

				$content = [];
				for ($i=1; $i <= $columns ; $i++) {
					$content['column_'.$i] = wpautop($_POST['post_section_'.$s.'_'.$i]);
				}

				$sections['section_'.$s] = [
					'columns' =>  $columns,
					'title' => sanitize_text_field($_POST['post_section_title_'.$s]),
					'background' => $_POST['post_section_background_'.$s],
					'content' => $content
				];
				$s++;
			}

			update_post_meta( $post_id, 'post_sections', addslashes(serialize($sections)) );

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

		add_action( 'wp_ajax_add_section', function(){
			AeriaSection::render_section([],$_POST['section'],$_POST['ncol']); exit;
		});

		add_action( 'wp_ajax_sort_section', function(){
			AeriaSection::sort_section($_POST['order'], $_POST['post_id']); exit;
		});

	}

	public static function sort_section($order, $post_id) {
		$sections = unserialize(get_post_meta( $post_id, 'post_sections', true ));

		$new_sections = [];
		$s = 0;

		foreach ($order as $key) {
			$new_sections['section_'.$s] = $sections[$key];
			$s++;
		}

		update_post_meta( $post_id, 'post_sections', addslashes(serialize($new_sections)) );

		die(json_encode([
			'success' => 1
		]));
	}

	public static function render_controls(){
		?>
		<div class="box-controls">
			<select id="ncol">
				<option value="1">1 Col</option>
				<option value="2">2 Cols</option>
				<option value="3">3 Cols</option>
				<option value="4">4 Cols</option>
			</select>
			<button class="button button-primary button-large" type="button" data-section-add ><span class="dashicons dashicons-welcome-add-page"></span> Add Section</button>
			<button class="button button-large" type="button" data-section-expand-all ><span class="dashicons dashicons-editor-expand"></span> Expand All Sections</button>
			<button class="button button-large" type="button" data-section-sort ><span class="dashicons dashicons-randomize"></span> Reorder/Remove</button>
			<div style="clear:both"></div>
		</div>
		<?php
	}

	public static function render_sections($post_id, $args){
		$sections = unserialize(get_post_meta( $post_id, 'post_sections', true ));
		wp_nonce_field( 'section_metabox', 'section_metabox_nonce' );

		?>
		<div class="box-reorder">
			<?php
				if($sections){
					$s = 0;
					echo '<ul data-section-sortable data-post-id="'.$post_id.'">';
					foreach ($sections as $key => $section) {
						$title = !empty($section['title'])?stripslashes($section['title']):'Section '.$s;
						echo '<li data-section-id="section_'.$s.'">'.$title.' <button class="button button-small" type="button" data-section-remove ><span class="dashicons dashicons-trash"></span></button></li>';
						$s++;
					}
					echo '</ul>';
					echo '<button class="button button-large button-primary" type="button" data-section-sortable-save ><span class="dashicons dashicons-yes"></span> Save</button>';

				}
			?>
		</div>
		<div class="box-sections">
			<?php

				if(isset($args['description']) && !empty($args['description'])) echo '<p>'.$args['description'].'</p>';

				if($sections){
					$s = 0;
					foreach ($sections as $key => $section) {
						AeriaSection::render_section($section,$s);
						$s++;
					}
				}else{
					// Default Section (First)
					AeriaSection::render_section([],0,1,$args);
				}
			?>
		</div>
		<?php
	}

	public static function render_section($section = [], $key = 0, $ncol = 1, $args = []){
		if(empty($section)) {
			$section = [
				'columns' => $ncol,
				'title' => '',
				'background' => '',
				'content' => [
					'column_1' => ''
				]
			];

			if(!empty($args['fields'])){
				$section['fields'] = $args['fields'];
			}

		}

	?>
		<div class="box-section" data-section-num=<?= $key ?>>
			<input type="hidden" data-section-columns name="post_section_columns_<?= $key ?>" value="<?= $section['columns'] ?>">
			<div class="header-section" >
				<div class="background" data-section-background style="background-image:url(<?= stripslashes($section['background']) ?>);">
					<input type="hidden" name="post_section_background_<?= $key ?>" id="post_section_background_<?= $key ?>" value="<?= $section['background'] ?>" />
				</div>
				<div class="title">
					<input type="text" placeholder="Here the title" class="post_section_title" name="post_section_title_<?= $key ?>" id="post_section_title_<?= $key ?>" value="<?= stripslashes($section['title']) ?>" >
				</div>
				<div class="controls">
					<button class="button button-small" data-section-expand ><span class="dashicons dashicons-welcome-write-blog"></span></button>
				</div>
				<div style="clear:both;"></div>
			</div>
			<div class="body-section">
				<?php

					if(isset($section['fields']) && !empty($section['fields'])){
						foreach ($section['fields'] as $field) {
							var_dump($field);
						}
					}

					for ($i=1; $i <= $section['columns']; $i++) {
						if($section['columns'] > 1) echo '<h2>Column '.$i.'</h2>';
					 	wp_editor( stripslashes($section['content']['column_'.$i]) , 'post_section_'.$key.'_'.$i );
					 }
				?>
			</div>
		</div>
	<?php
	}

}
