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
				$row_post = new AeriaPostRaw($post_id,$type);
				if(is_callable($call)){
					$call($row_post);
				} else {
					foreach (array_merge([['id'=>'featured','type'=>'featured']],(array)AeriaType::$types[$type]['metabox']['fields']) as $this_field) {
						if($this_field['id']===$call){
							switch ($this_field['type']) {
								case 'featured':
									if($value=$row_post->featuredURL()){
										echo '<div style="width:150px;height:150px;display:block;margin:0 auto;background:url('.$value.') center center no-repeat; background-size: cover;box-shadow: 1px 1px 5px rgba(0,0,0,.6);">';
									}
									break;
								case 'media':
									if($value = current((array)$row_post->fields->$call)){
										echo '<div style="width:150px;height:150px;display:block;margin:0 auto;background:url('.$value.') center center no-repeat; background-size: cover;box-shadow: 1px 1px 5px rgba(0,0,0,.6);">';
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
									echo current((array)$row_post->fields->$call);
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
