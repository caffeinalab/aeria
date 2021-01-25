<?php

namespace Aeria\Query;

class Query
{
    protected $queries;

    /**
     * Deletes a meta from a post.
     *
     * @param int    $post_id required post ID
     * @param string $key     custom meta's key
     *
     * @since  Method available since Release 3.0.0
     */
    public function deleteMeta($post_id, $key)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->postmeta
                WHERE post_id = %d AND meta_key LIKE %s", $post_id, $key.'%'
            )
        );
    }

    /**
     * Deletes an option from WP settings.
     *
     * @param string $key custom meta's key
     *
     * @since  Method available since Release 3.0.0
     */
    public function deleteOption($key)
    {
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $wpdb->options
                WHERE option_name LIKE %s", $key.'%'
            )
        );
    }

    /**
     * Gets the saved post types.
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the post types
     *
     * @since  Method available since Release 3.0.0
     */
    public function getPostTypes($parameters)
    {
        $searchField = (isset($parameters['s'])) ? $parameters['s'] : '';
        $public = (isset($parameters['public'])) ? $parameters['public'] : true;
        $sender = (isset($parameters['sender'])) ? $parameters['sender'] : null;

        $types = array_reduce(get_post_types(['public' => $public], 'object'), function ($carry, $type) {
            if ((empty($searchField) || preg_match('/'.$searchField.'/', $type->name))) {
                $carry[] = json_decode(json_encode($type), true);
            }

            return $carry;
        }, []);

        $types = aeria_objects_filter($types, $parameters, 'name');

        switch ($sender) {
            case 'SelectOptions':
                return array_map(function ($type) {
                    return array(
                        'value' => $type['name'],
                        'label' => $type['labels']['name'],
                    );
                }, $types);
                break;
            default:
                return $types;
                break;
        }

        return $response;
    }

    /**
     * Gets the saved taxonomies.
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the taxonomies
     *
     * @since  Method available since Release 3.0.0
     */
    public function getTaxonomies($parameters)
    {
        $searchField = (isset($parameters['s'])) ? $parameters['s'] : '';
        $sender = (isset($parameters['sender'])) ? $parameters['sender'] : null;
        $post_type = (isset($parameters['post_type'])) ? $parameters['post_type'] : '';
        $taxonomies = [];

        foreach (get_taxonomies([], 'objects') as $index => $taxonomy) {
            if ((empty($searchField) || preg_match('/'.$searchField.'/', $taxonomy->name)) && (!empty($post_type) && in_array($post_type, $taxonomy->object_type))) {
                $taxonomies[] = json_decode(json_encode($taxonomy), true);
            }
        }

        $taxonomies = aeria_objects_filter($taxonomies, $parameters, 'name');

        switch ($sender) {
            case 'SelectOptions':
                $taxonomies = array_map(function ($taxonomy) {
                    return [
                        'value' => $taxonomy['name'],
                        'label' => $taxonomy['labels']['name'],
                    ];
                }, $taxonomies);
                break;
            case 'Names':
                $taxonomies = array_map(function ($taxonomy) {
                    return $taxonomy['name'];
                }, $taxonomies);
                break;
            default:
                break;
        }

        return $taxonomies;
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
        $post_type = (isset($parameters['post_type'])) ? $parameters['post_type'] : 'post';
        $default_taxonomies = $this->getTaxonomies(['post_type' => $post_type, 'sender' => 'Names']);

        if (!isset($parameters['taxonomy']) && empty($default_taxonomies)) {
            return [];
        }

        $taxonomies = (isset($parameters['taxonomy'])) ? $parameters['taxonomy'] : $default_taxonomies;
        $hide_empty = (isset($parameters['hide_empty'])) ? filter_var($parameters['hide_empty'], FILTER_VALIDATE_BOOLEAN) : true;

        $terms = get_terms([
            'search' => $searchField,
            'taxonomy' => $taxonomies,
            'hide_empty' => $hide_empty,
        ]);

        if (is_wp_error($terms)) {
            return [];
        }

        $terms = array_map(function ($term) {
            return json_decode(json_encode($term), true);
        }, $terms);

        $terms = aeria_objects_filter($terms, $parameters, 'name');
        $terms = aeria_objects_filter($terms, $parameters, 'slug');

        switch ($sender) {
            case 'SelectOptions':
                $terms = array_map(function ($term) {
                    return [
                        'value' => $term['term_id'],
                        'label' => $term['name'],
                    ];
                }, $terms);
                break;
            default:
                break;
        }

        return $terms;
    }

    /**
     * Gets the requested posts.
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the requested posts
     *
     * @since  Method available since Release 3.0.0
     */
    public function getPosts($parameters)
    {
        $searchField = (isset($parameters['s'])) ? $parameters['s'] : null;
        $sender = (isset($parameters['sender'])) ? $parameters['sender'] : null;
        $postType = (isset($parameters['post_type'])) ? $parameters['post_type'] : null;
        $parentID = (isset($parameters['parent_id'])) ? $parameters['parent_id'] : null;
        $taxonomy = (isset($parameters['taxonomy'])) ? $parameters['taxonomy'] : null;
        $taxonomyTerms = (isset($parameters['taxonomy_terms'])) ? $parameters['taxonomy_terms'] : null;
        $taxonomyField = (isset($parameters['taxonomy_field'])) ? $parameters['taxonomy_field'] : 'term_id';
        $orderBy = (isset($parameters['orderby'])) ? $parameters['orderby'] : null;
        $order = (isset($parameters['order'])) ? $parameters['order'] : null;
        $numberPosts = (isset($parameters['numberposts'])) ? $parameters['numberposts'] : -1;
        $suppress_filters = (isset($parameters['suppress_filters']) && $parameters['suppress_filters'] == "false") ? false : true;

        switch ($sender) {
        case 'SelectOptions':
            $postParams = ['ID', 'post_title'];
            break;
        default:
            $postParams = [
                'ID', 'post_author', 'post_name', 'post_type', 'post_title',
                'post_date', 'post_content', 'post_excerpt', 'post_modified',
            ];
            break;
        }

        $responseParams = ['ID' => 'value', 'post_author' => 'post_author', 'post_name' => 'post_name',
        'post_type' => 'post_type', 'post_title' => 'label', 'post_date' => 'post_date', 'post_content' => 'post_content',
        'post_excerpt' => 'post_excerpt', 'post_modified' => 'post_modified', ];

        $args = [
            's' => $searchField,
            'post_type' => $postType,
            'post_parent' => $parentID,
            'orderby' => $orderBy,
            'order' => $order,
            'numberposts' => $numberPosts,
            'suppress_filters' => $suppress_filters,
        ];
        
        if (isset($taxonomy) || isset($taxonomyTerms)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => $taxonomy,
                    'slug' => $taxonomyField,
                    'terms' => $taxonomyTerms,
                ],
            ];
        }
        
        $posts = get_posts($args);
        $response = [];
        foreach ($posts as $index => $post) {
            $postArray = $post->to_array();
            foreach ($postParams as $thePostParam) {
                $response[$index][$responseParams[$thePostParam]] = $postArray[$thePostParam];
            }
        }

        return $response;
    }

    /**
     * Registers a new query.
     *
     * @param array $parameters the additional query parameters - check
     *                          WP codex to know more
     *
     * @return array of the requested posts
     *
     * @since  Method available since Release 3.0.0
     */
    public function register($name, $querier)
    {
        $this->queries[$name] = $querier;
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
     * @since  Method available since Release 3.0.0
     */
    public function __call($name, $args)
    {
        global $wpdb;
        $this->queries[$name]($wpdb, $args);
    }
}
