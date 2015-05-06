<?php

if( false === defined('AERIA') ) exit;

class AeriaPost {

    public      $id             = null;
    public      $title          = '';
    public      $raw_content    = '';
    public      $content        = '';
    public      $excerpt        = '';
    public      $data           = '';
    public      $slug           = '';
    public      $permalink      = '';
    public      $order          = '';
    public      $type           = '';
    public      $post_author    = '';
    public      $parent         = null;
    protected   $_fields        = null;
    protected   $_fields_cache  = null;
    protected   $_thumbs        = null;
    protected   $_attachments   = null;
    protected   $_tags          = null;
    protected   $_categories    = null;
    protected   $_tax_cache     = null;
    protected   $_featured      = null;

    public function __construct($id,$type=null){
        if($id){
            if(is_object($id)){
                if (is_a($id,'WP_Post')){
                    $t_post         = $id;
                } else if (is_a($id,'AeriaPost')) {
                    foreach ($id as $key => $value) {
                        $this->$key = $value;
                    }
                    return;
                }
            } else {
                $fld = is_numeric($id)?'id':'name';
                $q = $type?array($fld=>$id,'post_type'=>$type):array($fld=>$id);
                $t_post         = is_numeric($id)?get_post($id):current(get_posts($q));
            }
            $this->id           = $t_post->ID;
            $this->title        = apply_filters('the_title', $t_post->post_title);
            $this->raw_content  = $t_post->post_content;
            $this->post_author  = $t_post->post_author;
            $this->content      = do_shortcode(apply_filters('the_content', $t_post->post_content));
            $this->excerpt      = apply_filters('the_excerpt', $t_post->post_excerpt);
            $this->date         = $t_post->post_date;
            $this->slug         = $t_post->post_name;
            $this->order        = $t_post->menu_order?:0;
            $this->permalink    = AERIA_HOME_URL.$t_post->post_type.'/'.$t_post->post_name;
            $this->type         = $type?:$t_post->post_type;
            $this->parent       = ($t_post->post_parent)?new self($t_post->post_parent):null;
        }
    }

    public static function load($id,$type=null){
       return new static($id,$type);
    }

    public function update($fields=array()){
        $fields['ID'] = $this->id;
        return $this->id == wp_update_post($fields);
    }

    public function loadAsPage($id){
        if($id){
            $t_post             = is_numeric($id)?get_page($id):get_page_by_path($id);
            $this->id           = $t_post->ID;
            $this->title        = apply_filters('the_title', $t_post->post_title);
            $this->content      = apply_filters('the_content', $t_post->post_content);
            $this->excerpt      = apply_filters('the_excerpt', $t_post->post_excerpt);
            $this->date         = $t_post->post_date;
            $this->slug         = $t_post->post_name;
            $this->order        = $t_post->menu_order;
            $this->permalink    = AERIA_HOME_URL.$t_post->post_name;
            $this->type         = 'page';
        }
        return $this;
    }

    public function asJSON(){
        $tmp = json_decode(json_encode($this));
        $tmp->tags = $this->tags;
        $tmp->fields = $this->fields;
        $tmp->categories = $this->categories;
        return json_encode($tmp,JSON_NUMERIC_CHECK);
    }

    protected function get_meta(){
        static $info_cache=[];
        $terms = get_post_meta($this->id);
        $results = array();
        if(!empty($terms)){
            if(!is_wp_error($terms)){
                foreach($terms as $key=>$term){
                    if(count($term)>1){
                        $temp = [];
                        foreach ($term as $value) {
                            $temp[] = is_serialized($value)?unserialize($value):$value;
                        }
                        $results[$key] = $temp;
                    } else {
                        $results[$key] = is_serialized($term[0])?unserialize($term[0]):$term[0];
                    }
                }
            }
        }
        return (object)$results;
    }

    protected function get_thumbs(){
        $results = array();
        if($att = get_post_thumbnail_id($this->id)){
            $imgdata = wp_get_attachment_image_src($att,'full');
            $results['big'] = $imgdata[0];
            $imgdata = wp_get_attachment_image_src($att,'medium');
            $results['medium'] = $imgdata[0];
            $imgdata = wp_get_attachment_image_src($att,'thumbnail');
            $results['small'] = $imgdata[0];
        }
        return (object)$results;
    }

    public function featuredURL($full=false){
        if ($full) {
            $th = $this->get_thumbs();
            return $th->big;
        } else {
            if(null===$this->_featured){
                if (has_post_thumbnail($this->id)) {
                    $this->_featured = wp_get_attachment_thumb_url(get_post_thumbnail_id($this->id));
                }else{
                 $this->_featured = false;
                }
            }
            return $this->_featured;
        }
    }

    public function taxonomy($taxname){
        if(false==empty($this->_tax_cache[$taxname])) return $this->_tax_cache[$taxname];
            $terms = wp_get_object_terms($this->id,$taxname);
            $results = array();
            if(!empty($terms)){
            if(!is_wp_error($terms)){
                foreach($terms as $term){
                  $results[] = array(
                    'id'=>$term->term_id,
                    'name'=>$term->name,
                    'slug'=>$term->slug,
                    'permalink' => get_term_link($term->term_id,$term->name)
                    );
                }
            }
        }
        return $this->_tax_cache[$taxname]=$results;
     }

    /**
    *   Tutti i post che hanno nei loro tags almeno un tag
    *   in comune con i tag del post chiamante
    */

    public function relatedPosts($limit=5,$type=null){

        if($this->tags){
            $type = $type?:$this->type;
            $tags = array(); foreach($this->tags as $tag) $tags[] = $tag['id'];

            $translated = new AeriaPost(real_id($this->id, $type, false, 'it'));
            foreach($translated->tags as $tag) $tags[] = $tag['slug'];

            $posts_not_translated = Aeria::get_posts(array(
                'post_type'         => $type,
                'posts_per_page'    => $limit,
                'post__not_in'      => [$this->id],
                'exclude'           => [$this->id],
                'tax_query'         => array(
                    array(
                        'taxonomy'      => 'post_tag',
                        'terms'         => $tags,
                        'field'         => 'term_id',
                        'operator'      => 'IN',
                        )
                    ),
                'suppress_filters'  => 0
                ),$type.'_related_to_'.$this->id,0);

            $return = array();
            foreach ($posts_not_translated as $key => $value) {
                $tmp = new AeriaPost(real_id($value->id, $type, false, $_GET['lang'] ?: 'it'));
                if ($tmp->id) {
                    $return[] = $tmp;
                }
            }
            return $return;
        } else {
            return array();
        }
    }

    /**
    *   Tutti i post che hanno nei loro tags almeno un tag
    *   uguale alla slug del post chiamante
    */

    public function linkedPosts($limit=5,$type=null){

        $type = $type?:$this->type;
        $tags = array($this->slug);

        $translated = new AeriaPost(real_id($this->id, $type, false, 'it'));
        $tags[] = $translated->slug;

        $posts_not_translated = Aeria::get_posts(array(
            'post_type'         => $type,
            'posts_per_page'    => $limit,
            'post__not_in'      => array($this->id),
            'exclude'           => array($this->id),
            'tax_query'         => array(
                array(
                    'taxonomy'      => 'post_tag',
                    'terms'         => $tags,
                    'field'         => 'slug',
                    'operator'      => 'IN',
                    )
                ),
            'suppress_filters'  => 0
            ),$type.'_linked_to_'.$this->id,0);

        $return = array();
        foreach ($posts_not_translated as $key => $value) {
            $tmp = new AeriaPost(real_id($value->id, $type, false, $_GET['lang'] ?: 'it'));
            if ($tmp->id) {
                $return[] = $tmp;
            }
        }
        return $return;
    }

    /**
    *   Tutti i post che hanno nei loro tags almeno un tag
    *   in comune con i tags del post chiamante o la sua slug
    */

    public function allRelatedPosts($limit=5,$type=null){

        $type = $type?:$this->type;
        $tags = array();

        foreach($this->tags as $tag) $tags[] = $tag['slug'];
        $tags[] = $this->slug;

        $translated = new AeriaPost(real_id($this->id, $type, false, 'it'));
        foreach($translated->tags as $tag) $tags[] = $tag['slug'];

        $posts_not_translated = Aeria::get_posts(array(
            'post_type'         => $type,
            'posts_per_page'    => $limit,
            'post__not_in'      => array($this->id),
            'exclude'           => array($this->id),
            'tax_query'         => array(
                array(
                    'taxonomy'      => 'post_tag',
                    'terms'         => $tags,
                    'field'         => 'slug',
                    'operator'      => 'IN',
                    )
                ),
            'suppress_filters'  => 0
            ),$type.'_all_related_to_'.$this->id,0);

        $return = array();
        foreach ($posts_not_translated as $key => $value) {
            $tmp = new AeriaPost(real_id($value->id, $type, false, $_GET['lang'] ?: 'it'));
            if ($tmp->id) {
                $return[] = $tmp;
            }
        }
        return $return;
    }


    public function __get($name){
        switch($name){
            case 'tags':
            return null!==$this->_tags?
            $this->_tags:$this->_tags=$this->taxonomy('post_tag');
            break;
            case 'categories':
            return null!==$this->_categories?
            $this->_categories:$this->_categories=$this->taxonomy('category');
            break;
            case 'fields':
            return null!==$this->_fields?
            $this->_fields:$this->_fields=$this->get_meta();
            break;
            case 'thumbs':
            return null!==$this->_thumbs?
            $this->_thumbs:$this->_thumbs=$this->get_thumbs();
            break;
            case 'attachments':
            return null!==$this->_attachments?
            $this->_attachments:$this->_attachments=$this->get_attachments();
            break;
            default:
            return null;
            break;
        }
    }

    protected function get_attachments(){
        if($atts = Aeria::get_posts(array(
            'orderby'        => 'menu_order ID',
            'order'          => 'ASC',
            'post_type'      => 'attachment',
            'post_parent'    => $this->id,
            'numberposts'    => -1
            ),'attachments_for_'.$this->id,0)){
            $results = []; foreach($atts as $att){
                $full   = wp_get_attachment_image_src($att->ID,'full');
                $large  = wp_get_attachment_image_src($att->ID,'large');
                $small  = wp_get_attachment_image_src($att->ID,'small');
                $results[] = [
                'id'            =>  $att->ID,
                'title'         =>  $att->post_title,
                'description'   =>  $att->post_excerpt,
                'content'       =>  $att->post_content,
                'slug'          =>  $att->post_name,
                'src'           =>  $full[0],
                'width'         =>  $full[1],
                'height'        =>  $full[2],
                'title'         =>  $att->post_title,
                'title'         =>  $att->post_title,
                'thumb'         =>  [
                'large'     =>  [
                'src'       =>  $large[0],
                'width'     =>  $large[1],
                'height'    =>  $large[2],
                ],
                'small'     =>  [
                'src'       =>  $small[0],
                'width'     =>  $small[1],
                'height'    =>  $small[2],
                ],
                ],
                ];
            }
            return $results;
        } else {
            return [];
        }
    }

    function fieldInfo($field_name){
        return AeriaMetaBox::infoForField($this->type,$field_name);
    }

    function fieldDisplayValue($field_name){
        $info = $this->fieldInfo($field_name);
        $raw  = $this->fields->$field_name;
        if (empty($info['options'])) return $raw;
        $value = is_callable($info['options']) ? call_user_func($info['options']) : $info['options'];
        return is_array($value) && isset($value[$raw]) ? $value[$raw] : $raw;
    }

    /**
     * fieldAsPost function.
     *
     * @access public
     * @param mixed $field_name
     * @return void
     */
    function fieldAsPost($field_name){
        $res = [];
        if(isset($this->fields->$field_name)) foreach(preg_split('/\s*,\s*/',$this->fields->$field_name) as $_id){
            $res[] = new self($_id);
        };
        return empty($res)?false:(count($res)>1?$res:$res[0]);
    }

    /**
     * fieldAsURL function.
     *
     * @access public
     * @param mixed $field_name
     * @return void
     */
    function fieldAsURL($field_name){
        if(isset($this->fields->$field_name)) return $this->fields->$field_name;
    }

    /**
     * Add a new post to the database.
     *
     * @access public
     * @static
     * @param mixed $data
     * @param array $meta (default: array())
     * @return void
     */
    static function insert($data,$meta=array()){
        extract($data);
        $p_data = array(
            'post_status' => 'publish',
            'post_type' => isset($type)?$type:'post',
            'post_author' => 1,
            'menu_order' => isset($order)?$order:0,
            'post_content' => isset($content)?$content:'',
            'post_category' => isset($category)?$category:array(),
            'post_title' => isset($title)?$title:'',
            );

        $post_id = wp_insert_post($p_data);
        foreach((array)$meta as $key => $value){
            add_post_meta($post_id, $key, $value);
        }
        return $post_id;
    }

}

function manual_order_wp($posts){
    usort($posts, function($a,$b){return ($a->menu_order==$b->menu_order)?0:($a->menu_order>$b->menu_order?1:-1); });
    return $posts;
}
