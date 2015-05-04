<?php

if( false === defined('AERIA') ) exit;

class AeriaType {

  static public $types = array();

  public static function register($type){

    if(empty($type['id'])) trigger_error('AeriaType: You must define a post_type id.',E_USER_ERROR);

    $type['options'] = empty($type['options'])?array():$type['options'];
    $post_type = $type['id'];
    $post_name = isset($type['title'])?$type['title']:ucfirst($type['id']);
    static::$types[$post_type] = $type;


    if(false===empty($type['metabox'])){
      // Support one or multiple metabox definitions
      if (isset($type['metabox']['id'])) $type['metabox'] = [$type['metabox']];
      foreach($type['metabox'] as $mbox){
        if (empty($mbox['pages'])) $mbox['pages'] = [$post_type];
        if (!in_array($post_type, $mbox['pages'])) $mbox['pages'][] = $post_type;
        AeriaMetaBox::register($mbox);
      }
      unset($type['metabox']);
    }

    if(false===empty($type['taxonomy'])){
      // Support one or multiple taxonomy definitions
      if (isset($type['taxonomy']['id'])) $type['taxonomy'] = [$type['taxonomy']];
      foreach($type['taxonomy'] as $tax){
        if (empty($tax['types'])) $tax['types'] = [$post_type];
        if (!in_array($post_type, $tax['types'])) $tax['types'][] = $post_type;
        AeriaTaxonomy::register($tax);
      }
      unset($type['taxonomy']);
    }

    if(false===empty($type['columns'])){
      AeriaColumns::register($type['id'],$type['columns']);
      unset($type['columns']);
    }


    add_action( 'init', function() use ($type,$post_type,$post_name) {

      $options = array_merge_replace(array(
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => ['slug' => $post_type, 'with_front' => false],
        'capability_type'     => 'post',
        'with_front'          => false,
        'has_archive'         => true,
        'hierarchical'        => true,
        'feeds'               => false,
        'menu_position'       => null,
        'reorder'             => false,
        'supports'            => false, // 'title', editor', 'author', 'thumbnail', 'excerpt', 'comments',
      ),$type['options']);

      unset($type['options']);

      $options_supports = $options['supports'];
      if(!$options_supports) $options_supports = 'title,editor';
      $options['supports'] = explode(',',$options_supports);

      if($options['hierarchical']) $options['supports'][] = 'page-attributes';


      $options['labels'] = array_merge_replace(array(
        'name'                => $post_name,
        'singular_name'       => $post_name,
        'add_new'             => 'Add new',
        'add_new_item'        => 'Add new Item',
        'edit_item'           => 'Edit',
        'new_item'            => 'New',
        'all_items'           => 'Show all',
        'view_item'           => 'Show item',
        'search_items'        => 'Search',
        'not_found'           => 'Not found',
        'not_found_in_trash'  => 'Not found in trash',
        'parent_item_colon'   => '',
        'taxonomies'          => [], // 'category', 'post_tag',
        'menu_name'           => $post_name,
        'menu_icon'           => null,
      ), $type);

      register_post_type( $post_type, $options );

      if($options['reorder']){
        new AeriaReorder(array(
          'post_type'     => $post_type,
          'order'         => 'ASC',
          'heading'       => $options['labels']['singular_name'],
          'final'         => '',
          'initial'       => '',
          'menu_label'    => __( 'Reorder', 'reorder' ),
          'icon'          => '',
          'post_status'   => 'publish',
          'show_title'    => isset($options['reorder']['show_title'])?$options['reorder']['show_title']:true,
          'fields'        => $options['reorder']['fields']?:false
        ));
        }

      // Check and register relations

      $relations = isset($type['relations'])?$type['relations']:false;

      if($relations) {

        $meta_fields = [];

        foreach ((array)$relations as $key => $relation) {

          $multiple = isset($relation['multiple'])? $relation['multiple']:false;

          $meta_fields[] = [
            'name'      => $relation['title'],
            'id'        => 'relations_'.$post_type.'_'.$relation['type'],
            'type'      => 'select_ajax',
            'multiple'  => $multiple,
            'relation'  => $relation['type']
          ];
        }

        AeriaMetaBox::register([
          'id'      => 'relations_'.$post_type,
          'title'   => 'Relations '.$options['labels']['name'],
          'pages'   => [$post_type],
          'fields'  => $meta_fields
        ]);
      }

    });

  }

}
