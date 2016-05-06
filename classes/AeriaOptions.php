<?php

if( false === defined('AERIA') ) exit;

class AeriaOptions {

	public static function register($options=[]){


		if(empty($options)) trigger_error('AeriaOptions: You must define one or more options.',E_USER_ERROR);

		add_action('admin_menu', function() use ($options) {

			add_submenu_page( 'options-general.php', 'Opzioni', 'Opzioni', 'manage_options', 'settings-options', function() use ($options){

			    if (isset($_POST["update_options"])) {
			        foreach ($options as $id => $label) {

			            update_option($id, isset($_POST[$id])?$_POST[$id]:'');
			        }
			    }

			    wp_enqueue_style('aeria-options', AERIA_RESOURCE_URL.'css/aeria-options.css');
			    wp_enqueue_style('icheck', AERIA_RESOURCE_URL.'css/icheck.css');
			    wp_enqueue_script('icheck', AERIA_RESOURCE_URL.'js/icheck.min.js');

			?>
			<script>
				jQuery(function($){
					jQuery('input').iCheck({
						checkboxClass: 'icheckbox_flat',
    					radioClass: 'iradio_flat'
					});
				});

			</script>
			<div class="wrap">
				<h2>Opzioni Generali</h2>

				 <form method="POST" action="">
				 	<input type="hidden" name="update_options" value="Y" />
			        <table class="form-table options-table">

			            <?php
			                foreach ($options as $id => $label) {
			                ?>
			                     <tr valign="top">
			                    	<td>
			                            <input type="checkbox" id="<?= $id ?>" name="<?= $id ?>" value="on" <?php if(self::get($id) === 'on'): ?>checked="checked"<?php endif; ?>>
			                        </td>
			                        <th scope="row">
			                            <label for="<?= $id ?>">
			                               <?= $label ?>
			                            </label>
			                        </th>
			                    </tr>
			                <?php
			                }
			            ?>

			            <tr>
			            	<td colspan="2"><input type="submit" value="Save" class="button-primary"/></td>
			            </tr>
			        </table>
			    </form>

			</div>

			<?php
			});

		});

	}

	public static function get($key){
		return get_option($key);
	}
}
