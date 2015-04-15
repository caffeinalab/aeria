<?php

/**
 * USAGE:
 * <?php $imgURL = get_cupp_meta( $user_id, $size ); ?>
 * or
 * <img src="<?php echo get_cupp_meta( $user_id, $size ); ?>">
 *
 */


// Enqueue scripts and styles
add_action( 'admin_enqueue_scripts', 'cupp_enqueue_scripts_styles' );
function cupp_enqueue_scripts_styles() {
    // Register
    wp_register_style( 'cupp_admin_css', AERIA_RESOURCE_URL.'/css/userprofilephoto.css', false, '1.0.0', 'all' );
    wp_register_script( 'cupp_admin_js', AERIA_RESOURCE_URL.'/js/userprofilephoto.js', array('jquery'), '1.0.0', true );

    // Enqueue
    wp_enqueue_style( 'cupp_admin_css' );
    wp_enqueue_script( 'cupp_admin_js' );
}

// Show the new image field in the user profile page.
add_action( 'show_user_profile', 'cupp_profile_img_fields' );
add_action( 'edit_user_profile', 'cupp_profile_img_fields' );

function cupp_profile_img_fields( $user ) {
    if(!current_user_can('upload_files'))
        return false;

    // vars
    $cupp_url = get_the_author_meta( 'cupp_meta', $user->ID );
    $cupp_upload_url = get_the_author_meta( 'cupp_upload_meta', $user->ID );
    $cupp_upload_edit_url = get_the_author_meta( 'cupp_upload_edit_meta', $user->ID );

    if(!$cupp_upload_url){
        $btn_text = 'Upload New Image';
    } else {
        $cupp_upload_edit_url = get_home_url().get_the_author_meta( 'cupp_upload_edit_meta', $user->ID );
        $btn_text = 'Change Current Image';
    }
    ?>

    <div id="cupp_container">
    <h3><?php _e( 'Custom User Profile Photo', 'aeria' ); ?></h3>

    <table class="form-table">

        <tr>
            <th><label for="cupp_meta"><?php _e( 'Profile Photo', 'aeria' ); ?></label></th>
            <td>
                <!-- Outputs the image after save -->
                <div id="current_img">
                    <?php if($cupp_upload_url): ?>
                        <img src="<?php echo esc_url( $cupp_upload_url ); ?>" class="cupp-current-img">
                        <div class="edit_options uploaded">
                            <a class="remove_img"><span>Remove</span></a>
                            <a href="<?php echo $cupp_upload_edit_url; ?>" class="edit_img" target="_blank"><span>Edit</span></a>
                        </div>
                    <?php elseif($cupp_url) : ?>
                        <img src="<?php echo esc_url( $cupp_url ); ?>" class="cupp-current-img">
                        <div class="edit_options single">
                            <a class="remove_img"><span>Remove</span></a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Select an option: Upload to WPMU or External URL -->
                <div id="cupp_options">
                    <input type="radio" id="upload_option" name="img_option" value="upload" class="tog" checked>
                    <label for="upload_option">Upload New Image</label><br>
                    <input type="radio" id="external_option" name="img_option" value="external" class="tog">
                    <label for="external_option">Use External URL</label><br>
                </div>

                <!-- Hold the value here if this is a WPMU image -->
                <div id="cupp_upload">
                    <input type="hidden" name="cupp_upload_meta" id="cupp_upload_meta" value="<?php echo esc_url_raw( $cupp_upload_url ); ?>" class="hidden" />
                    <input type="hidden" name="cupp_upload_edit_meta" id="cupp_upload_edit_meta" value="<?php echo esc_url_raw( $cupp_upload_edit_url ); ?>" class="hidden" />
                    <input type='button' class="cupp_wpmu_button button-primary" value="<?php _e( $btn_text, 'aeria' ); ?>" id="uploadimage"/><br />
                </div>

                <!-- Outputs the text field and displays the URL of the image retrieved by the media uploader -->
                <div id="cupp_external">
                    <input type="text" name="cupp_meta" id="cupp_meta" value="<?php echo esc_url_raw( $cupp_url ); ?>" class="regular-text" />
                </div>

                <!-- Outputs the save button -->
                <span class="description"><?php _e( 'Upload a custom photo for your user profile or use a URL to a pre-existing photo.', 'aeria' ); ?></span>
                <p class="description"><?php _e('Update Profile to save your changes.', 'aeria'); ?></p>
            </td>
        </tr>

    </table><!-- end form-table -->
</div> <!-- end #cupp_container -->

    <?php wp_enqueue_media(); // Enqueue the WordPress MEdia Uploader ?>

<?php }

// Save the new user CUPP url.
add_action( 'personal_options_update', 'cupp_save_img_meta' );
add_action( 'edit_user_profile_update', 'cupp_save_img_meta' );

function cupp_save_img_meta( $user_id ) {

    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    // If the current user can edit Users, allow this.
    update_user_meta( $user_id, 'cupp_meta', $_POST['cupp_meta'] );
    update_user_meta( $user_id, 'cupp_upload_meta', $_POST['cupp_upload_meta'] );
    update_user_meta( $user_id, 'cupp_upload_edit_meta', $_POST['cupp_upload_edit_meta'] );
}

/**
 * Return an ID of an attachment by searching the database with the file URL.
 *
 * First checks to see if the $url is pointing to a file that exists in
 * the wp-content directory. If so, then we search the database for a
 * partial match consisting of the remaining path AFTER the wp-content
 * directory. Finally, if a match is found the attachment ID will be
 * returned.
 *
 * http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
 *
 * @return {int} $attachment
 */
function get_attachment_image_by_url( $url ) {

    // Split the $url into two parts with the wp-content directory as the separator.
    $parse_url  = explode( parse_url( WP_CONTENT_URL, PHP_URL_PATH ), $url );

    // Get the host of the current site and the host of the $url, ignoring www.
    $this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
    $file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

    // Return nothing if there aren't any $url parts or if the current host and $url host do not match.
    if ( !isset( $parse_url[1] ) || empty( $parse_url[1] ) || ( $this_host != $file_host ) ) {
        return;
    }

    // Now we're going to quickly search the DB for any attachment GUID with a partial path match.
    // Example: /uploads/2013/05/test-image.jpg
    global $wpdb;

    $prefix     = $wpdb->prefix;
    $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts WHERE guid RLIKE %s;", $parse_url[1] ) );

    // Returns null if no attachment is found.
    return $attachment[0];
}

/**
 * Retrieve the appropriate image size
 *
 * @param $user_id    Default: $post->post_author. Will accept any valid user ID passed into this parameter.
 * @param $size       Default: 'thumbnail'. Accepts all default WordPress sizes and any custom sizes made by the add_image_size() function.
 * @return {url}      Use this inside the src attribute of an image tag or where you need to call the image url.
 */
function get_cupp_meta( $user_id, $size ) {

    //allow the user to specify the image size
    if (!$size){
        $size = 'thumbnail'; // Default image size if not specified.
    }
    if(!$user_id){
        $user_id = $post->post_author;
    }

    // get the custom uploaded image
    $attachment_upload_url = esc_url( get_the_author_meta( 'cupp_upload_meta', $user_id ) );

    // get the external image
    $attachment_ext_url = esc_url( get_the_author_meta( 'cupp_meta', $user_id ) );
    $attachment_url = '';
    if($attachment_upload_url){
        $attachment_url = $attachment_upload_url;
    } elseif($attachment_ext_url) {
        $attachment_url = $attachment_ext_url;
    }

    // grabs the id from the URL using Frankie Jarretts function
    $attachment_id = get_attachment_image_by_url( $attachment_url );

    // retrieve the thumbnail size of our image
    $image_thumb = wp_get_attachment_image_src( $attachment_id, $size );

    // return the image thumbnail
    return $image_thumb[0];
}

?>