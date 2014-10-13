<?php

//Aeria Search

if( false === defined('AERIA') ) exit;

class AeriaUtils {

	public static function search($q=null,$page=1,$posts_per_page=10,$post_type='post'){
		$q = isset($_REQUEST['q'])?$_REQUEST['q']:$q;
		$page = isset($_REQUEST['page'])?$_REQUEST['page']:$page;
		$posts_per_page = isset($_REQUEST['posts_per_page'])?$_REQUEST['posts_per_page']:$posts_per_page;
		$post_type = isset($_REQUEST['post_type'])?$_REQUEST['post_type']:$post_type;

		if(is_numeric($page) && is_numeric($posts_per_page)) {

			if(strlen($q)>0) {
				$results = get_posts([
					'posts_per_page' => $posts_per_page,
					'offset' => ($page-1)*$posts_per_page,
					'post_type' => $post_type,
					'orderby' => 'title',
					'order' => 'ASC',
					'post_title_like' => $q,
					'suppress_filters' => false

				]);
			}else{
				$results = get_posts([
					'posts_per_page' => $posts_per_page,
					'offset' => ($page-1)*$posts_per_page,
					'post_type' => $post_type,
					'orderby' => 'title',
					'order' => 'ASC',
					'suppress_filters' => false
				]);
			}


			$number_results = wp_count_posts($post_type);

			$posts_result = [];
			foreach ($results as $key => $result) {
				$posts_result[] = [
					'id' => $result->ID,
					'text' => $result->post_title
				];
			}

			$posts = [
				'total' => $number_results->publish,
				'result' => $posts_result
			];

		}

	  header("Content-Type: application/json",true);
	  echo json_encode($posts);
	  exit;

	}

	public static function search_init($id=null,$multiple=false){

		$id = isset($_REQUEST['id'])?$_REQUEST['id']:$id;
		$multiple = isset($_REQUEST['multiple'])?$_REQUEST['multiple']:$multiple;

		if($multiple=="true") {
			$result = [];
			$posts = explode(',',$id);
			foreach ($posts as $key => $post_id) {
				$post = new AeriaPost($post_id);
				$result[] = [
					'id' => $post->id,
					'text' => $post->title
				];
			}
		}else{
			$post = new AeriaPost($id);

			$result = [
				'id' => $post->id,
				'text' => $post->title
			];
		}

		header("Content-Type: application/json",true);
		echo json_encode($result);
		exit;

	}

	public static function title_like_posts_where( $where, &$wp_query ) {
	    global $wpdb;
	    if ( $post_title_like = $wp_query->get( 'post_title_like' ) ) {
	        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'' . esc_sql( like_escape( $post_title_like ) ) . '%\'';
	    }
	    return $where;
	}
}
