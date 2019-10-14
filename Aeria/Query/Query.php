<?php

namespace Aeria\Query;


class Query
{
    protected $queries;

    /**
     * Deletes a meta from a post
     *
     * @param int    $post_id required post ID
     * @param string $key     custom meta's key
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function deleteMeta($post_id, $key)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta
                WHERE post_id = %d AND meta_key LIKE %s", $post_id, $key."%"
            )
        );

    }

    /**
     * Deletes an option from WP settings
     *
     * @param string $key custom meta's key
     *
     * @return void
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function deleteOption($key)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options
                WHERE option_name LIKE %s", $key."%"
            )
        );
    }

    /**
     * Gets the saved post types
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the post types
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getPostTypes($parameters)
    {
        $searchField = (isset($parameters['s'])) ? $parameters['s'] : null;
        $public = (isset($parameters['public'])) ? $parameters['public'] : false;
        $sender = (isset($parameters["sender"])) ? $parameters["sender"] : null;

        $args=[
            "public" => $public,
            "query_var" => $searchField
        ];
        $types = get_post_types($args, 'object');
        $response=[];
        foreach ($types as $index => $post_type) {
            $response[$index]["label"] = $post_type->labels->name;
            $response[$index]["value"] = $post_type->name;
        }
        return $response;
    }
    /**
     * Gets the saved taxonomies
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the taxonomies
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getTaxonomies($parameters)
    {
        $searchField = (isset($parameters['s'])) ? $parameters['s'] : "";
        $sender = (isset($parameters["sender"])) ? $parameters["sender"] : null;
        $postType = (isset($parameters["post_type"])) ? $parameters["post_type"] : "";
        $taxonomies = get_taxonomies([], 'objects');
        $response = [];
        foreach ($taxonomies as $index => $taxonomy) {
            if ((preg_match('/'.$searchField.'/', $taxonomy->name)&&((in_array($postType, $taxonomy->object_type)) || $postType == "") || $searchField=="")) {
                $response[$index]["label"] = $taxonomy->labels->name;
                $response[$index]["value"] = $taxonomy->name;
            }
        }
        return $response;
    }

    /**
     * Gets the saved terms.
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of terms
     *
     * @since  Method available since Release 3.0.0
     */
    public function getTerms($parameters)
    {
        $searchField = (isset($parameters['s'])) ? $parameters['s'] : '';
        $sender = (isset($parameters['sender'])) ? $parameters['sender'] : null;
        $taxonomy = (isset($parameters['taxonomy'])) ? $parameters['taxonomy'] : 'category';
        $hide_empty = (isset($parameters['hide_empty'])) ? $parameters['hide_empty'] : true;
        $terms = get_terms(array(
          'search' => $searchField,
          'taxonomy' => $taxonomy,
          'hide_empty' => $hide_empty,
        ));

        switch ($sender) {
          case 'SelectOptions':
            return array_map(function ($term) {
              return array(
                'value' => $term->term_id,
                'label' => $term->name,
              );
            }, array_values($terms));
            break;
          default:
            return $terms;
            break;
          }
    }

    /**
     * Gets the requested posts
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the requested posts
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function getPosts($parameters)
    {
        $searchField = (isset($parameters["s"])) ? $parameters["s"] : null;
        $sender = (isset($parameters["sender"])) ? $parameters["sender"] : null;
        $postType = (isset($parameters["post_type"])) ? $parameters["post_type"] : null;
        $parentID = (isset($parameters["parent_id"])) ? $parameters["parent_id"] : null;
        $taxonomy = (isset($parameters["taxonomy"])) ? $parameters["taxonomy"] : null;
        $taxonomyTerms = (isset($parameters["taxonomy_terms"])) ? $parameters["taxonomy_terms"] : null;
        $orderBy = (isset($parameters["orderby"])) ? $parameters["orderby"] : null;
        $order = (isset($parameters["order"])) ? $parameters["order"] : null;
        $numberPosts = (isset($parameters["numberposts"])) ? $parameters["numberposts"] : -1;


        switch ($sender){
        case 'SelectOptions':
            $postParams = ["ID", "post_title"];
            break;
        default:
            $postParams = [
                "ID", "post_author", "post_name", "post_type", "post_title",
                "post_date", "post_content", "post_excerpt", "post_modified"
            ];
            break;
        }

        $responseParams = ["ID" => "value", "post_author" => "post_author", "post_name" => "post_name",
        "post_type" => "post_type", "post_title" => "label", "post_date" => "post_date", "post_content" => "post_content",
        "post_excerpt" => "post_excerpt", "post_modified" => "post_modified"];

        $args = [
            's' => $searchField,
            'post_type' => $postType,
            'post_parent' => $parentID,
            'tax_query' => [
                'taxonomy' => $taxonomy,
                'terms' => $taxonomyTerms
            ],
            'orderby' => $orderBy,
            'order' => $order,
            'numberposts' => $numberPosts
        ];
        $posts = get_posts($args);
        $response=[];
        foreach ($posts as $index => $post) {
            $postArray = $post->to_array();
            foreach ($postParams as $thePostParam) {
                $response[$index][$responseParams[$thePostParam]]=$postArray[$thePostParam];
            }
        }
        return $response;
    }
    /**
     * Registers a new query
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the requested posts
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function register($name, $querier)
    {
        $this->queries[$name]=$querier;
    }
    /**
     * Overrides php __call. If the function is present in the class prototypes, it
     * gets called.
     *
     * @param string $name the function name
     * @param array  $args the arguments to be passed to the function
     *
     * @return mixed the callable return value
     *
     * @access public
     * @since  Method available since Release 3.0.0
     */
    public function __call($name, $args)
    {
        global $wpdb;
        $this->queries[$name]($wpdb, $args);
    }

}
