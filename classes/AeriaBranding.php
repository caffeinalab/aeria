<?php
// Exit if accessed directly.
if( false === defined('AERIA') ) exit;

class AeriaBranding {

	static public function add($options){
		if(IS_NOT_ADMIN) return;
		foreach ($options as $section_type => $section) {
			switch ($section_type) {
				case 'menulogo':
					$url = isset($section['url'])?$section['url']:'';
					$css = isset($section['css'])?implode('',(array)$section['css']):'';

					add_action('admin_head', function() use ($url,$css){
						echo '<style>';
						echo '#adminmenuback:after{opacity:0.2;content:"";z-index:0;display:block;position:fixed;left:20px;bottom:10px;background:url(',$url,') center center no-repeat;background-size:cover;width:100px;height:100px;',$css,'}';
						echo '</style>';
					});

				break;

				case 'watermark':
					$url = isset($section['url'])?$section['url']:'';
					$css = isset($section['css'])?implode('',(array)$section['css']):'';

					add_action('admin_head', function() use ($url,$css){
						echo '<style>';
						echo '#wpbody:after{opacity:0.6;content:"";z-index:-1;display:block;position:fixed;right:0;bottom:0;background:url(',$url,') center center no-repeat;background-size:contain;width:70%;height:50%;',$css,'}';
						echo '#wpbody-content{z-index:1}';
						echo '</style>';
					});

				break;

				case 'background':
					$css = isset($section['css'])?implode('',(array)$section['css']):'';

					add_action('admin_head', function() use ($css){
						echo '<style>';
						echo 'html{',$css,'}';
						echo '#wpbody-content .wrap h2{text-shadow:0 1px 1px #fff}';
						echo '#wpfooter{color:#1B1B1B;text-shadow:0 1px 0 rgba(255,255,255,.4);}';
						echo '</style>';
					});

				break;

				case 'css':
					$css = isset($section['css'])?implode('',(array)$section['css']):'';

					add_action('admin_head', function() use ($section){
						echo '<style>';
						echo $section;
						echo '</style>';
					});

				break;

				case 'remove-admin-menu-logo':
					add_action('wp_before_admin_bar_render', function(){
				        global $wp_admin_bar;
				        $wp_admin_bar->remove_menu('wp-logo');
					}, 0);
				break;

				default:
				break;
			}
		}
	}

}