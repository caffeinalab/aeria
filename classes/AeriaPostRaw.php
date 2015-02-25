<?php

if( false === defined('AERIA') ) exit;

class AeriaPostRaw {
    public function __construct($id,$type=null){
        if($id){
            if(is_a($id,'WP_Post')){
                $t_post         = $id;
            } else {
                $fld = is_numeric($id)?'id':'name';
                $q = $type?array($fld=>$id,'post_type'=>$type):array($fld=>$id);
                $t_post         = is_numeric($id)?get_post($id):current(get_posts($q));
            }
            $this->id           = $t_post->ID;
            $this->title        = $t_post->post_title;
            $this->raw_content  = $t_post->post_content;
            $this->content      = $t_post->post_content;
            $this->excerpt      = $t_post->post_excerpt;
            $this->date         = $t_post->post_date;
            $this->slug         = $t_post->post_name;
            $this->order        = $t_post->menu_order?:0;
            $this->permalink    = AERIA_HOME_URL.$t_post->post_type.'/'.$t_post->post_name;
            $this->type         = $type?:$t_post->post_type;
            $this->parent       = ($t_post->post_parent)?new self($t_post->post_parent):null;
        }
    }
    
    public function loadAsPage($id){
        if($id){
            if(is_a($id,'WP_Post')){
                $t_post         = $id;
            } else {
                $t_post         = is_numeric($id)?get_page($id):get_page_by_path($id);
            }
            
            $this->id           = $t_post->ID;
            $this->title        = $t_post->post_title;
            $this->content      = $t_post->post_content;
            $this->excerpt      = $t_post->post_excerpt;
            $this->date         = $t_post->post_date;
            $this->slug         = $t_post->post_name;
            $this->order        = $t_post->menu_order;
            $this->permalink    = AERIA_HOME_URL.$t_post->post_name;
            $this->type         = 'page';
        }
        return $this;
    }

}
