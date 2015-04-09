<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaColumns {

    public static function register($type,$_columns){
        if(empty($_columns['add'])) $_columns['add'] = [];
        if(empty($_columns['remove'])) $_columns['remove'] = [];
        if(empty($_columns['add']) && empty($_columns['remove'])) return;

        // Headers
		add_filter('manage_edit-'.$type.'_columns',function( $columns ) use ($type,$_columns) {
			$old_columns = $columns;
			$center_columns = [];
			$first = [];
			$last = [];

			$first['cb'] = $old_columns['cb'];
			unset($old_columns['cb']);

	        foreach($_columns['add'] as $column => $desc){
				if(isset($desc['position'])){
					if($desc['position']=='first'){
						$first[$column] = $desc['title'];
					} elseif($desc['position']=='last') {
						$last[$column] = $desc['title'];
					} else {
						$center_columns[$column] = $desc['title'];
					}
				} else {
					$center_columns[$column] = $desc['title'];
				}
	        }

	        foreach($_columns['remove'] as $column){
                if(isset($old_columns[$column])) unset($old_columns[$column]);
	        }

	        $results = [];


	        foreach ($first as $key => $col) {
				$results[$key] = $col;
	        }

	        foreach ($old_columns as $key => $col) {
				$results[$key] = $col;
	        }

	        foreach ($center_columns as $key => $col) {
				$results[$key] = $col;
	        }
	        foreach ($last as $key => $col) {
				$results[$key] = $col;
	        }

			return $results;
		});

        // Display
        add_action('manage_'.$type.'_posts_custom_column', function($column,$post_id) use ($type,$_columns) {
            if(isset($_columns['add'][$column])) {
				$call = $_columns['add'][$column]['render'];
				$row_post = new AeriaPost($post_id,$type);
				if(is_callable($call)){
					$call($row_post);
				} else {
					$value = $row_post->fields[$call];
					foreach (AeriaType::$types[$type]['metabox']['fields'] as $this_field) {
						if($this_field['id']===$call){
							switch ($this_field['type']) {
								case 'media':
									if($value){
										echo '<img src="http://static.appcaffeina.com/assets/i/150x150/',$value,'" style="box-shadow: 1px 1px 5px rgba(0,0,0,.6);">';
									}
									break;
								/*
								case 'select':
									echo '<select class="select2" multiple="multiple" disabled="disabled">';
									foreach (explode(',',$value) as $opt) {
										echo '<option selected>',$opt,'</option>';
									}
									echo '</select>';
									break;
								*/
								default:
									echo $value;
									break;
							};
							return;
						}
					}
				}
			}
        },10,2);

        // Widths
        add_action('admin_head',function() use($_columns){
		    echo '<style>';
	        foreach($_columns['add'] as $column => $desc){
				echo '.column-',$column,'{text-align:left;overflow:hidden;vertical-align:middle;';
				if(isset($desc['width'])) echo 'width:',$desc['width'],' !important;';
				echo '}';
	        }
		    echo '</style>';
		});


        // Sortable columns
        $sortable_columns = [];
        foreach($_columns['add'] as $column => $desc){
            if(false===empty($desc['sortable'])){
                $sortable_columns[] = $column;
            }
        }
        add_action('manage_edit-'.$type.'_sortable_columns',function($columns) use ($sortable_columns) {
            foreach($sortable_columns as $key) $columns[$key] = $key;
            return $columns;
        });

    }
}