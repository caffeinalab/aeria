<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaAdmin {
	public static $pages		= [];


  public static function addSeparator($position){
  	static $separators = 0;
  	$separators++;
  	add_action('admin_menu',function() use ($position,$separators){
		  global $menu;
		  foreach($menu as $offset => $section) {
		    if ($offset >= $position) {
		      $menu[$position] = ['','read',"separator-adm-{$separators}",'','wp-menu-separator'];
		      break;
		    }
			}
			ksort($menu);
		});
  }


	public static function page( $options ) {
		static $anon  = 0;
		static $index = 0;
		
		// Dashicons
		// https://developer.wordpress.org/resource/dashicons
		
		$options = (object)array_merge([
			'id'     			=> null,
			'title'  			=> 'Opzioni',
			'parent'			=> null,
			'menu'   			=> 'Opzioni',
			'icon'   			=> 'dashicons-admin-generic',
			'render' 			=> function(){},
			'full_render' => false,
		],(array)$options);

		if (!$options->id) $options->id = 'admin-page-'.($anon++);
		static::$pages[$options->id] = $options;
		$index++;

		// First call
		if ($index==1) self::addSeparator('59.999');

		add_action('admin_menu',function() use ($options, $index) {
			global $menu;
			add_menu_page(
				$options->title,
				$options->menu,
				'manage_options',
				"adm-{$options->id}",
				function() use ($options){
					if (!$options->full_render) {
						echo '<div class="wrap" style="height:700px">';
						echo "<h2><span class=\"dashicons dashicons-feedback\" style=\"width:40px;height:40px;font-size:40px;\"></span> {$options->title}</h2><br>";
					}
					call_user_func($options->render,$options);
					if (!$options->full_render)	echo '</div>';
				},
				$options->icon,
				"59.".str_pad($index, 3, 0, STR_PAD_LEFT)
			);
			ksort($menu);
		});
	}

	public static function editor( $options ) {
		
		$options = (object)array_merge([
			'json'  			=> false,
			'mode'  			=> false,
			'views'  			=> 'tree,code,form',
		],(array)$options);

		if (!$options->json || !file_exists($options->json)) 
			throw new Exception("AeriaAdmin::editor : JSON file not specified", 1);

		$options->views = preg_split('~\s*,\s*~',$options->views);

		if (!$options->mode) $options->mode = $options->views[0];

		$options->render = function($options){ 
				$options 	= (object)$options;

				if ($_POST['action']=='save'){
					if ($_POST['json'] != '~'){
						file_put_contents($options->json,json_encode(json_decode(stripslashes($_POST['json'])),JSON_PRETTY_PRINT));
						$message = 'Options saved to : '.basename($options->json);
					} else $message = 'Something is wrong... file was not modified.';
				} else $message = '';

				$json = @file_get_contents($options->json)?:'{}';

			?>
				<?php if ($message) {?>
				<div class="updated fade"><p><strong><?=$message?></strong></p></div>
				<?php } ?>

				<link rel="stylesheet" href="<?=AERIA_RESOURCE_URL?>css/jsoneditor.min.css" type="text/css" media="all" />
				<style>
				#jsoneditor {
					color: #1A1A1A;
				  border: 1px solid #C5C5C5;
				  width: 100%;
				  height: 100%;
				  overflow: auto;
				  position: relative;
				  padding: 0;
				  line-height: 100%;
				  background: #fff;
				  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.14);
				}
				.jsoneditor .menu {
					background-color: #EFEFEF;
		  		border-bottom: 1px solid #DBDBDB;
				}
				.jsoneditor .menu button {
					border: 1px solid rgba(0, 0, 0, 0.12);
				  border-radius: 100%;
				}
				.jsoneditor tr.highlight {
				  background-color: rgba(77, 149, 219, 0.13);
				}
				</style>
				<div id="jsoneditor"></div>
				<form id="jsoneditor_save" method="post" action="">
					<p class="submit">
						<input type="hidden" name="action" value="save">
						<input id="jsoneditor_payload" type="hidden" name="json" 	 value="~">
						<input type="submit" class="button-primary" value="<?php _e( 'Save', 'aeria' ); ?>" />
					</p>
				</form>
				<script src="<?=AERIA_RESOURCE_URL?>js/jsoneditor.min.js"></script>
				<script>
				  var editor = new JSONEditor(document.getElementById('jsoneditor'), {
				    mode: '<?=$options->mode?>',
				    modes: <?=json_encode($options->views)?>,
				    error: function (err) { alert(err.toString()); }
				  }, <?=$json?>);
					jQuery('#jsoneditor_save').on('submit',function(e){
						jQuery('#jsoneditor_payload').val(editor.getText());
					});
				</script>
			<?php
		};

		self::page($options);
	}

}