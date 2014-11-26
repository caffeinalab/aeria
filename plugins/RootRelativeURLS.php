<?php
/*
Plugin Name: Root Relative URLs
*/

// Provide emergency override
function root_urls_active($active=null){ static $ROOT_URLS_ENABLED = true; return null===$active?$ROOT_URLS_ENABLED:$ROOT_URLS_ENABLED=!!$active; }

add_filter( 'wpseo_build_sitemap_post_type', function($type){
    root_urls_active(false);
    return $type;
},0);

//Ideally this function runs before any other plugin
add_action('plugins_loaded', array('MP_WP_Root_Relative_URLS', 'init'), 1);

class MP_WP_Root_Relative_URLS {
    //Wordpress Root Relative URL Hack - this allows for accessing a Wordpress site from multiple domain names and
    //munges absolute urls provided to the wysiwyg editor to be root relative instead of absolute
    //generally displays urls throughout the admin area as root relative instead of absolute

    //Normally we inject root relative urls when possible, especially for content creation
    static $massage = false;

    static function add_actions($tag_arry, $func, $p = 10, $a = 1) {
        //allows for multiple tags to bind to the same funciton call
        //useful when using lambda functions
        foreach ($tag_arry as $v) {
            add_filter($v, $func, $p, $a);
        }
    }

    static function scheme( $url ) {
        //And this here is a prime example of why absolute urls in Wordpress create extra overhead and processing.
        //And in the core, they use four different approaches to acheive this translation!
        //For reference, see: http://core.trac.wordpress.org/ticket/19037
        if (is_ssl()) {
            $url = str_replace('http://', 'https://', $url);
        }
        else {
            $url = str_replace('https://', 'http://', $url);
        }
        return $url;
    }

    static function dynamic_absolute_url($url) {
        //These URL's cannot be reformmated into root-relative urls for various reasons.  Either they are indescriminantly
        //used in export functions or RSS feeds.  Or because they are literally checked against the domain of URLs stored
        //in the database - a needless process for any website.
        $url = @parse_url($url);

        if (!isset($url['path'])) $url['path'] = '';

        $relative = ltrim(@$url['path'], '/') . (isset($url['query']) ? "?" . $url['query'] : '');

        return MP_WP_Root_Relative_URLS::scheme(
            'http://' . @$_SERVER['HTTP_HOST'] .
            (!empty($relative) ? '/' . $relative : '') .
            (isset($url['fragment']) ? '#' . $url['fragment'] : '')
            );
    }

    static function dynamic_rss_absolute_url($info, $type = 'url') {
        //Generates dynamic absolute URI's for RSS Feeds
        //get_bloginfo_rss returns multiple types of info, the_permalink_rss only returns a url.
        //so when type is not passed in, consider it a url, otherwise only parse when type == url
        if ($type == 'url') {
            return MP_WP_Root_Relative_URLS::dynamic_absolute_url($info);
        }
        return $info;
    }

    static function enable_content_massage() {
        //this is only called when an external feed is being called
        //this lets the content filter know that we should convert root relative urls back in to absolute urls since
        //some external sources don't understand the html spec
        self::$massage = true;

        //massage global post object
        global $post;
        $post->post_content = self::massage_external_content($post->post_content);
        $post->post_excerpt= self::massage_external_content($post->post_excerpt);
    }

    static function massage_external_content($a) {
        //Here is where we fix the root relative content urls into absolute urls using the current host name
        //this shouldn't be required but companies like feedburner and feedblitz don't understand the web :(
        if (self::$massage == true) {
            $a = preg_replace_callback('#(<[^>]+(?:href|src)\s*?=\s*?[\'"])(\/)#i', array("MP_WP_Root_Relative_URLS", "do_absolute_massage_cb"), $a);
        }
        return $a;
    }

    static function do_absolute_massage_cb($a) {
        //callback handler that does the physical insertion of absolute domain into root relative urls
        return $a[1] . self::scheme('http://' . @$_SERVER['HTTP_HOST'] . $a[2]);
    }

    static function proper_root_relative_url($url) {
        if(!root_urls_active()) return $url;
        //This method is used for urls that can be acceptably reformatted into root-relative urls without causing issues
        //related to other deficiencies in the wp core.

        if (self::$massage) {
            //massage back to absolute because we're rendering a feed and the platform mixes url procurment methods between the delivery methods
            //despite offering _rss specific filters
            return MP_WP_Root_Relative_URLS::dynamic_absolute_url($url);
        } else {
            $url = @parse_url($url);

            if (!isset($url['path'])) $url['path'] = '';

            return '/' . ltrim(@$url['path'], '/') .
            (isset($url['query']) ? "?" . $url['query'] : '') .
            (isset($url['fragment']) ? '#' . $url['fragment'] : '');
        }
    }

    static function proper_multisite_path_comparison($redirect_bool) {
        //Prevents infinite loop caused by the path matching but not the domain when making network admin requests
        global $current_blog, $current_site;

        //don't worry about domain name mismatch as long as the paths are correct
        if ($redirect_bool &&
            $current_blog->path == $current_site->path) {
            $redirect_bool = false;
    }

    return $redirect_bool;
}

static function root_relative_url($url, $html) {
    if(!root_urls_active()) return $html;

        //If urls already start from root, just return it
    if ($url[0] == "/") return $html;

    $p = parse_url($url);
    $root = $p['scheme'] . "://" . $p['host'];
    $html = str_ireplace($root, '', $html);
    return $html;
}

static function root_relative_image_urls($html, $id, $caption, $title, $align, $url, $size, $alt) {
        //Same as media_send_to_editor but images are handled separately
    return MP_WP_Root_Relative_URLS::root_relative_url($url, $html);
}

static function root_relative_media_urls($html, $id, $att) {
        //Filter out host from embed urls
    return MP_WP_Root_Relative_URLS::root_relative_url($att['url'], $html);
}

static function fix_canonical_redirect($redirect, $requested) {
        //Fixes infinite redirect loop caused by WP Core bug: http://core.trac.wordpress.org/ticket/21824
    if (MP_WP_Root_Relative_URLS::proper_root_relative_url($redirect) ==
        MP_WP_Root_Relative_URLS::proper_root_relative_url($requested)) {
        return false;
}
}

static function fix_upload_paths($o) {
        //Fixes attachment urls when user has customized the base url and/or upload folder in Admin > Settings > Media : Uploading Files
    $o['url'] = MP_WP_Root_Relative_URLS::proper_root_relative_url($o['url']);
    $o['baseurl'] = MP_WP_Root_Relative_URLS::proper_root_relative_url($o['baseurl']);
    return $o;
}

static function init() {

    add_action('admin_init', array( 'MP_WP_Root_Relative_URLS', 'admin_settings_init' ));

        //Here we check the url blacklist to disable proper root relative urls, this helps with certain 3rd party
        //plugins / certain clients for rss feeds
    $cur_url = MP_WP_Root_Relative_URLS::dynamic_absolute_url(@$_SERVER['REQUEST_URI']);
    if (stripos(get_option('emc2_blacklist_urls'), $cur_url) !== false) {
            //for blacklists, create a dynamic but full absolute url instead
        self::$massage = true;
    }

        //Setup all hooks / filters for either dynamically replacing the host part of a URL with the current host
        //or for stripping the scheme + host + port altogether
    MP_WP_Root_Relative_URLS::add_actions(
        array(
            'option_siteurl',
            'blog_option_siteurl',
            'option_home',
            'admin_url',
            'home_url',
            'includes_url',
            'site_url',
            'plugins_url',
            'content_url',
            'site_option_siteurl',
            'network_home_url',
            'network_site_url'
            ),
        array(
            'MP_WP_Root_Relative_URLS',
            'dynamic_absolute_url'
            ),
        1
        );

    MP_WP_Root_Relative_URLS::add_actions(
        array(
            'post_link',
            'page_link',
            'attachment_link',
            'post_type_link',
            'wp_get_attachment_url'
            ),
        array(
            'MP_WP_Root_Relative_URLS',
            'proper_root_relative_url'
            ),
        1
        );

    MP_WP_Root_Relative_URLS::add_actions(
        array(
            'get_bloginfo_rss',
            'the_permalink_rss',
            'get_post_comments_feed_link',
            'get_the_author_url',
            'get_comment_link'
            ),
        array(
            'MP_WP_Root_Relative_URLS',
            'dynamic_rss_absolute_url'
            ),
            1, //high priority
            2  //supply second parameter for type checking
            );

        //Used to indicate that an atom feed is being generated so it's ok to massage the content urls for absolute format
    MP_WP_Root_Relative_URLS::add_actions(
        array(
            'atom_ns',
            'attom_comments_ns',
            'rss2_ns',
            'rss2_comments_ns',
            'rdf_ns',
            'wp_mail'
            ),
        array(
            'MP_WP_Root_Relative_URLS',
            'enable_content_massage'
            )
        );

    MP_WP_Root_Relative_URLS::add_actions(
        array(
            'the_excerpt_rss',
            'the_content_feed',
            ),
        array(
            'MP_WP_Root_Relative_URLS',
            'massage_external_content'
            )
        );

        //HACK: This plugin actually won't work for MU Sites until either of the following conditions are true:
            //1. Wordpress core team publishes this patch - http://core.trac.wordpress.org/attachment/ticket/18910/ms-blogs.php.patch
            //2. You deal with the consequences of patching a core file yourself using the above patch reference
        //Regardless of the above, this plugin only supports path-based MU installations - at this point domain-based MU installations are not supported
    add_filter(
        'redirect_network_admin_request',
        array(
            'MP_WP_Root_Relative_URLS',
            'proper_multisite_path_comparison'
            )
        );

    add_filter(
        'image_send_to_editor',
        array(
            'MP_WP_Root_Relative_URLS',
            'root_relative_image_urls'
            ),
            1, //high priority
            8  //eight params? wow
            );

    add_filter(
        'media_send_to_editor',
        array(
            'MP_WP_Root_Relative_URLS',
            'root_relative_media_urls'
            ),
        1,
        3
        );

    add_filter(
        'redirect_canonical',
        array(
            'MP_WP_Root_Relative_URLS',
            'fix_canonical_redirect'
            ),
        10,
        2
        );

    add_filter(
        'upload_dir',
        array(
            'MP_WP_Root_Relative_URLS',
            'fix_upload_paths'
            ),
        1,
        1
        );

        # fix links to javascript loaded with wp_enqueue_script where the files are located in wp-include (e.g. wp_enqueue_script( 'comment-reply' );
        # @credit: @tfmtfm
        /*
        add_filter(
            'script_loader_src',
            array(
                'MP_WP_Root_Relative_URLS',
                'proper_root_relative_url'
            )
        );
        */

        # support links generated by the WPML plugin (i.e. for the multi-lingual support)
add_filter(
    'WPML_filter_link',
    array(
        'MP_WP_Root_Relative_URLS',
        'proper_root_relative_url'
        )
    );
}

    //The following sets up an admin config page
static function admin_settings_init() {

        //give this setting its own section on the General page
    add_settings_section('emc2_blacklist_urls', 'Root Relative Blacklist', array('MP_WP_Root_Relative_URLS', 'render_setting_section'), 'general');

    add_settings_field('emc2_blacklist_urls', 'Root Relative Blacklist URLs',
            array('MP_WP_Root_Relative_URLS', 'render_setting_input'), //render callback
            'general', //page
            'emc2_blacklist_urls', //section
            array(
                'label_for' => 'emc2_blacklist_urls'
                )
            );

    register_setting('general', 'emc2_blacklist_urls');
}

static function render_setting_section($t) {
        //add description of section to page
    echo "<p>Enter URLs you do not want processed to root relative by the Root Relative URL Plugin. This is particularly useful for fixing 3rd party plugins that depend on absolute URLs.<br/>- Only put one URL per line, you can enter as many urls as you'd like.<br/>- You can use partial URLs, no wildcards needed, but be careful about unintentionally matching a post title.<br/>- The full URL in the browser will be used to check against these entries so you can disable root relative urls for your entire production site simply by putting your production url in this field.<br/>- To disable processing for RSS feeds you would use <span style='background-color: #e8f6fd;'>http://www.mysite.com/?feed</span> to capture all rss, atomic and comment feeds, only you'd replace www.mysite.com with your production URL.</p>";
}

static function render_setting_input($attr) {
        //Display a list of option boxes for specifying the new line insertion behavior
    $options = get_option('emc2_blacklist_urls');
    ?>
    <textarea id="emc2_blacklist_urls" name="emc2_blacklist_urls" style="width: 70%; min-width: 25em;" rows="20"><?php echo esc_attr($options); ?></textarea>
    <?php
}

}