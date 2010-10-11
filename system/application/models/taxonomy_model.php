<?php
if (! defined ( 'BASEPATH' )) {
	exit ( 'No direct script access allowed' );
}
/**

 * Microweber
 *
 * An open source CMS and application development framework for PHP 5.1 or newer
 *

 * @package     Microweber
 * @author      Peter Ivanov
 * @copyright   Copyright (c), Mass Media Group, LTD.
 * @license     http://ooyes.net
 * @link        http://ooyes.net
 * @since       Version 1.0

 */

// ------------------------------------------------------------------------


/** 

 * Content class

 *

 * @desc Functions for manipulation categories and tags

 * @access      public
 * @category   Taxonomy API
 * @subpackage      Core
 * @author      Peter Ivanov
 * @link        http://ooyes.net

 */

class taxonomy_model extends Model {
	function __construct() {
		parent::Model ();
	
	}
	
	function taxonomy_helpers_generateTagCloud($href = false, $criteria = false, $beginning_only_with_letter = false, $clould_order = false, $clould_limits = false, $min_max_sizes = false, $only_for_categories = false) {
		
		global $cms_db_tables;
		
		//return false;
		

		$table = $cms_db_tables ['table_taxonomy'];
		
		//$sql = "SELECT taxonomy_value from $table where taxonomy_type='tag' and taxonomy_value LIKE ('%$item%') group by taxonomy_value  limit 1 ";
		

		//      $q = $this->core_model->sqlQuery ( $sql );
		

		//var_dump($criteria);
		

		if (! empty ( $criteria )) {
			
			$get = $this->core_model->getDbData ( $table, $criteria, $limit = false, $offset = false, $orderby = false, $cache_group = 'taxonomy/global' );
			
			if (! empty ( $get )) {
				
				$ids = array ();
				
				foreach ( $get as $item ) {
					
					$ids [] = intval ( $item ['id'] );
				
				}
			
			}
			
			$ids = array_unique ( $ids );
		
		}
		
		if (! empty ( $ids )) {
			
			$idq = " AND id IS NOT NULL and (id=0  ";
			
			foreach ( $ids as $i ) {
				
				$idq .= " OR id=$i  ";
			
			}
			
			$idq .= " )   ";
		
		}
		
		if (is_array ( $only_for_categories ) == true) {
			
			$chidlern_ids = array ();
			
			foreach ( $only_for_categories as $only_for_cat )
				
				$cchidlern_ids = $this->taxonomyGetChildrenItems ( $only_for_cat );
			
			if (! empty ( $cchidlern_ids )) {
				
				foreach ( $cchidlern_ids as $temp ) {
					
					$chidlern_ids [] = $temp ['id'];
				
				}
			
			}
			
			if (! empty ( $chidlern_ids )) {
				
				//var_dump($chidlern_ids);
				

				$chidlern_ids_implode = implode ( ',', $chidlern_ids );
				
				$q = " SELECT id, to_table_id from $table where taxonomy_type='category_item' and id IN ($chidlern_ids_implode) group  by   to_table_id ";
				
				$q = $this->core_model->dbQuery ( $q );
				
				//var_dump($q);
				

				$some_more_ids = array ();
				
				foreach ( $q as $some ) {
					
					$some_more_ids [] = $some ['to_table_id'];
				
				}
				
				if (! empty ( $some_more_ids )) {
					
					$some_more_idsimplode = implode ( ',', $some_more_ids );
					
					$idq .= " AND to_table_id IN ($some_more_idsimplode)  ";
				
				}
			
			}
			
		//$qq1 =  "  SELECT id, to_table_id, to_table from  $table where   "
		

		}
		
		if ($beginning_only_with_letter != '') {
			
			$beginning_only_with_letter_q = " AND LEFT(taxonomy_value,1) = '$beginning_only_with_letter' ";
		
		} else {
			
			$beginning_only_with_letter_q = false;
		
		}
		
		//AND LEFT(taxonomy_value,1) = 'v'
		

		if ($clould_order != false) {
			
			$order_q = " ORDER BY {$clould_order[0]} {$clould_order[1]}  ";
		
		} else {
			
			$order_q = " ORDER BY taxonomy_value ASC ";
		
		}
		
		if ($clould_limits != false) {
			
			$limit_q = " LIMIT {$clould_limits[0]}, {$clould_limits[1]}  ";
		
		} else {
			
			$limit_q = "  ";
		
		}
		
		$query = "SELECT *, COUNT(id) AS quantity

        ,  LEFT(taxonomy_value,1)  as taxonomy_value_letter

        FROM $table

        where taxonomy_type='tag' $idq



        $beginning_only_with_letter_q



        GROUP BY taxonomy_value

        $order_q



        $limit_q



        ";
		
		$q = $this->core_model->sqlQuery ( $query );
		
		foreach ( $q as $row ) {
			
			//$tags [$row ['tag']] = $row ['quantity'];
			

			//$tags [$row ['taxonomy_value']] = $row ['quantity'];
			

			$tags [] = $row;
			
			$tags_velues [$row ['taxonomy_value']] = $row ['quantity'];
		
		}
		
		// change these font sizes if you will
		

		if (empty ( $min_max_sizes )) {
			
			$max_size = 250; // max font size in %
			

			$min_size = 100; // min font size in %
		

		} else {
			
			$max_size = $min_max_sizes [1]; // max font size in %
			

			$min_size = $min_max_sizes [0]; // min font size in %
		

		}
		
		// get the largest and smallest array values
		

		if (! empty ( $tags_velues )) {
			
			$max_qty = max ( array_values ( $tags_velues ) );
			
			$min_qty = min ( array_values ( $tags_velues ) );
		
		} else {
			
			$max_qty = false;
			
			$min_qty = false;
		
		}
		
		// find the range of values
		

		$spread = $max_qty - $min_qty;
		
		if (0 == $spread) { // we don't want to divide by zero
			

			$spread = 1;
		
		}
		
		// determine the font-size increment
		

		// this is the increase per tag quantity (times used)
		

		$step = ($max_size - $min_size) / ($spread);
		
		// loop through our tag array
		

		//foreach ( $tags as $key => $value ) {
		

		if (! empty ( $tags )) {
			
			foreach ( $tags as $item ) {
				
				$the_href = false;
				
				$the_href = $href;
				
				// calculate CSS font-size
				

				// find the $value in excess of $min_qty
				

				// multiply by the font-size increment ($size)
				

				// and add the $min_size set above
				

				$size = $min_size + (($item ['quantity'] - $min_qty) * $step);
				
				// uncomment if you want sizes in whole %:
				

				// $size = ceil($size);
				

				if ($href == false) {
					
				//  $href = "#";
				

				}
				
				foreach ( $item as $k => $v ) {
					
					$the_href = str_ireplace ( "{" . $k . "}", $v, $the_href );
				
				}
				
				//
				

				if (empty ( $ids )) {
					
					$to_print = true;
				
				} else {
					
					if (in_array ( $item ['id'], $ids ) == true) {
						
						$to_print = true;
					
					} else {
						
						$to_print = false;
					
					}
				
				}
				
				if ($to_print == true) {
					
					// you'll need to put the link destination in place of the #
					

					// (assuming your tag links to some sort of details page)
					

					echo '<a href="' . $the_href . '" style="font-size: ' . $size . '%"';
					
					// perhaps adjust this title attribute for the things that are tagged
					

					echo ' title="' . $item ['quantity'] . ' things tagged with ' . $item ['taxonomy_value'] . '"';
					
					echo '>' . $item ['taxonomy_value'] . '</a> ';
				
				}
				
			// notice the space at the end of the link
			

			}
		
		}
	
	}
	
	function taxonomySave($data, $preserve_cache = false) {
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		if ($data ['content_body'] == '') {
			
			$data ['content_body'] = $this->parseContentBodyItems ( $data ['content_body'], $data ['taxonomy_value'] );
		
		}
		
		if ($data ['taxonomy_silo_keywords']) {
			
			$data ['taxonomy_silo_keywords'] = strip_tags ( $data ['taxonomy_silo_keywords'] );
		
		}
		
		$content_ids = false;
		
		if ($data ['content_id']) {
			
			if (is_array ( $data ['content_id'] ) and ! empty ( $data ['content_id'] ) and trim ( $data ['taxonomy_type'] ) != '') {
				
				//p($data, 1);
				

				$content_ids = $data ['content_id'];
			
			}
		
		}
		
		$save = $this->core_model->saveData ( $table, $data );
		
		if (intval ( $save ) == 0) {
			
			return false;
		
		}
		
		if (! empty ( $content_ids )) {
			
			$content_ids = array_unique ( $content_ids );
			
			//  p($content_ids, 1);
			

			$taxonomy_type = trim ( $data ['taxonomy_type'] ) . '_item';
			
			$content_ids_all = implode ( ',', $content_ids );
			
			$q = "delete from $table where to_table='table_content'



            and content_type='post'

            and parent_id=$save

            and  taxonomy_type ='{$taxonomy_type}' ";
			
			//p($q,1);
			

			$this->core_model->dbQ ( $q );
			
			foreach ( $content_ids as $id ) {
				
				$item_save = array ();
				
				$item_save ['to_table'] = 'table_content';
				
				$item_save ['to_table_id'] = $id;
				
				$item_save ['taxonomy_type'] = $taxonomy_type;
				
				$item_save ['content_type'] = 'post';
				
				$item_save ['parent_id'] = intval ( $save );
				
				$item_save = $this->core_model->saveData ( $table, $item_save );
				
				$this->core_model->cleanCacheGroup ( 'content' . DIRECTORY_SEPARATOR . $id );
			
			}
		
		}
		
		$this->taxonomyFixPositionsForId ( $save );
		
		//  $this->core_model->cleanCacheGroup ( 'taxonomy' );
		

		if ($preserve_cache == false) {
			
			//$this->core_model->cleanCacheGroup ( 'taxonomy' );
			$this->core_model->cleanCacheGroup ( 'taxonomy' . DIRECTORY_SEPARATOR . $save );
			$this->core_model->cleanCacheGroup ( 'taxonomy' . DIRECTORY_SEPARATOR . '0' );
			$this->core_model->cleanCacheGroup ( 'taxonomy' . DIRECTORY_SEPARATOR . 'global' );
		}
		
		return $save;
	
	}
	
	function taxonomyGetIds($data = false, $orderby = false) {
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		if ($orderby == false) {
			
			$orderby [0] = 'updated_on';
			
			$orderby [1] = 'DESC';
		
		}
		
		if (($data ['taxononomy_type']) == '') {
			
			$limit = false;
		
		} else {
			
			$limit = false;
		
		}
		
		if ($no_limits == true) {
			
			$limit = false;
		
		}
		
		//  print ($data['taxononomy_type']);
		

		if (($data ['taxonomy_type']) == 'category') {
			
			$limit = false;
		
		}
		
		$save = $this->core_model->getDbData ( $table, $data, $limit, $offset = false, $orderby, $cache_group = 'taxonomy/global', $debug = false, $ids = false, $count_only = false, $only_those_fields = array ('id' ), $exclude_ids = false, $force_cache_id = $function_cache_id );
		
		if (empty ( $save )) {
			
			return false;
		
		}
		
		$ids = array ();
		
		foreach ( $save as $item ) {
			
			$ids [] = $item ['id'];
		
		}
		
		return $ids;
	
	}
	
	function taxonomyGet($data = false, $orderby = false, $no_limits = false, $no_cache = false) {
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id .= $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		
		if (intval ( $data ['id'] ) != 0) {
			$data = $this->taxonomyGetSingleItemById ( $data ['id'] );
			return $data;
		} elseif (intval ( $data ['parent_id'] ) != 0) {
			$data = $this->taxonomyGetSingleItemById ( $data ['parent_id'] );
			return $data;
		
		} else {
			
			$function_cache_id = __FUNCTION__ . '_' . $data ['taxononomy_type'] . '_' . $data ['to_table'] . '_' . $data ['to_table_id'] . '_' . md5 ( $function_cache_id );
			
			global $cms_db_tables;
			
			$table = $cms_db_tables ['table_taxonomy'];
			
			if ($orderby == false) {
				
				$orderby [0] = 'updated_on';
				
				$orderby [1] = 'DESC';
			
			}
			
			if (($data ['taxononomy_type']) == '') {
				
				$limit [0] = 0;
				
				$limit [1] = 10;
			
			} else {
				
				$limit = false;
			
			}
			
			if ($no_limits == true) {
				
				$limit = false;
			
			}
			
			//  print ($data['taxononomy_type']);
			

			if (($data ['taxonomy_type']) == 'category') {
				
				$limit = false;
			
			}
			
			if ($no_cache == false) {
				
				$cache_group = 'taxonomy/global';
			
			} else {
				
				$cache_group = false;
			
			}
			
			$save = $this->core_model->getDbData ( $table, $data, $limit = $limit, $offset = false, $orderby, $cache_group = $cache_group, $debug = false, $ids = false, $count_only = false, $only_those_fields = false, $exclude_ids = false, $force_cache_id = false );
			
			return $save;
		}
	
	}
	
	function taxonomyGetThumbnailImageById($id, $size = 128) {
		
		//$data ['id'] = $id;
		

		//$data = $this->taxonomyGet ( $data );
		

		//var_dump ( $data );
		

		$data = $this->core_model->mediaGetThumbnailForItem ( $to_table = 'table_taxonomy', $to_table_id = $id, $size );
		
		return $data;
	
	}
	
	function taxonomyGetUrlForTaxonomyIdAndCache($id) {
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$taxonomy_id = intval ( $id );
		$cache_group = 'taxonomy/' . $taxonomy_id;
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		} else {
			
			$to_cache = $this->taxonomyGetUrlForTaxonomyId ( $id );
			
			//var_dump($to_cache);
			

			$this->core_model->cacheWriteAndEncode ( $to_cache, $function_cache_id, $cache_group );
			
			return $to_cache;
		
		}
	
	}
	
	function taxonomyGetUrlForTaxonomyId($id) {
		
		//return false ;
		

		if (intval ( $id ) == 0) {
			
			return false;
		
		}
		
		$data = array ();
		
		$data ['id'] = $id;
		
		$data = $this->taxonomyGetSingleItemById ( $id );
		
		if (empty ( $data )) {
			
			return false;
		
		}
		//$this->load->model ( 'Content_model', 'content_model' );
		$CI = get_instance ();
		$content = array ();
		
		$content ['content_subtype'] = 'blog_section';
		
		$content ['content_subtype_value'] = $id;
		
		$orderby = array ('id', 'desc' );
		
		$content = $CI->content_model->getContentAndCache ( $content, $orderby );
		
		$content = $content [0];
		
		$url = false;
		
		if (! empty ( $content )) {
			
			if ($content ['content_type'] == 'page') {
				
				$url = $CI->content_model->getContentURLByIdAndCache ( $content ['id'] );
			
			}
			
			if ($content ['content_type'] == 'post') {
				
				$url = $CI->content_model->contentGetHrefForPostId ( $content ['id'] );
			
			}
		
		}
		
		if ($url != false) {
			
			return $url;
		
		}
		
		$parent_ids = $this->taxonomyGetParentIdsForId ( $data ['id'] );
		
		foreach ( $parent_ids as $item ) {
			
			$content = array ();
			
			$content ['content_subtype'] = 'blog_section';
			
			$content ['content_subtype_value'] = $item;
			
			$orderby = array ('id', 'desc' );
			
			$content = $CI->content_model->getContentAndCache ( $content, $orderby );
			
			$content = $content [0];
			
			$url = false;
			
			if (! empty ( $content )) {
				
				if ($content ['content_type'] == 'page') {
					$url = $CI->content_model->getContentURLByIdAndCache ( $content ['id'] );
					$url = $url . '/category:' . $data ['taxonomy_value'];
				}
				if ($content ['content_type'] == 'post') {
					$url = $CI->content_model->contentGetHrefForPostId ( $content ['id'] );
				}
			}
			
			if ($url != false) {
				
				return $url;
			
			}
		
		}
		
		return false;
		
	//var_dump ( $parent_ids );
	

	}
	
	function taxonomyCategoriesGetNextOrPrevCategoryFotCategoryId($cat_id, $next_or_prev = 'next') {
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$item = $this->taxonomyGetSingleItemById ( $cat_id );
		
		$item_main = $item;
		
		if (empty ( $item )) {
			
			return false;
		
		}
		
		if ($next_or_prev == 'next') {
			
			$pos = $item ['position'];
			
			$q = " SELECT id from $table where position > $pos and taxonomy_type = 'category' and parent_id = '{$item['parent_id']}' order by position ASC  limit 0,1";
			
			$q = $this->core_model->dbQuery ( $q );
			
			$q = $q [0] ['id'];
			
			$item = $this->taxonomyGetSingleItemById ( $q );
			
			if (empty ( $item )) {
				
				$q = " SELECT id from $table where position > 0 and taxonomy_type = 'category' and parent_id = '{$item_main['id']}' order by position ASC limit 0,1";
				
				$q = $this->core_model->dbQuery ( $q );
				
				$q = $q [0] ['id'];
				
				$item = $this->taxonomyGetSingleItemById ( $q );
			
			}
			
			if (empty ( $item )) {
				
				$q = " SELECT id from $table where position > 0 and taxonomy_type = 'category' and parent_id = '{$item_main['parent_id']}' order by position ASC limit 0,1";
				
				$q = $this->core_model->dbQuery ( $q );
				
				$q = $q [0] ['id'];
				
				$item = $this->taxonomyGetSingleItemById ( $q );
			
			}
			
			return $item;
		
		}
		
		if ($next_or_prev == 'prev') {
			
			$pos = $item ['position'];
			
			$q = " SELECT id from $table where position < $pos and taxonomy_type = 'category' and parent_id = '{$item['parent_id']}' order by position DESC  limit 0,1";
			
			//var_dump($q);
			

			$q = $this->core_model->dbQuery ( $q );
			
			$q = $q [0] ['id'];
			
			$item = $this->taxonomyGetSingleItemById ( $q );
			
			if (empty ( $item )) {
				
				$q = " SELECT id from $table where position < 10000 and taxonomy_type = 'category' and parent_id = '{$item_main['id']}' order by position DESC limit 0,1";
				
				//var_dump($q);
				

				$q = $this->core_model->dbQuery ( $q );
				
				$q = $q [0] ['id'];
				
				$item = $this->taxonomyGetSingleItemById ( $q );
			
			}
			
			if (empty ( $item )) {
				
				$q = " SELECT id from $table where position < 10000 and taxonomy_type = 'category' and parent_id = '{$item_main['parent_id']}' order by position DESC limit 0,1";
				
				$q = $this->core_model->dbQuery ( $q );
				
				$q = $q [0] ['id'];
				
				$item = $this->taxonomyGetSingleItemById ( $q );
			
			}
			
			return $item;
		
		}
	
	}
	
	function taxonomyChangePosition($id, $direction) {
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		if (($direction == 'up') or ($direction == 'down')) {
			
			$item = $this->taxonomyGetSingleItemById ( $id );
			
			if (empty ( $item )) {
				
				return false;
			
			} else {
				
				$cur_item = $item;
				
				$cur_position = intval ( $item ['position'] );
				
				if ($cur_position > 1) {
					
					$prev_position = $cur_position - 1;
				
				}
				
				$next_position = $cur_position + 1;
				
				if (($direction == 'up')) {
					
					$prev_item = array ();
					
					$prev_item ['parent_id'] = $cur_item ['parent_id'];
					
					$prev_item ['taxonomy_type'] = $cur_item ['taxonomy_type'];
					
					$prev_item ['position'] = $prev_position;
					
					$prev_item = $this->taxonomyGet ( $prev_item );
					
					$prev_item = $prev_item [0];
					
					if ($prev_item ['id'] != $cur_item ['id']) {
						
						$update_pos = array ();
						
						$update_pos ['id'] = $cur_item ['id'];
						
						$update_pos ['position'] = $prev_item ['position'];
						
						//$this->taxonomySave ( $update_pos );
						

						$q = " UPDATE $table set position='{$update_pos['position']}' where id='{$update_pos['id']}'  ";
						
						$q = $this->core_model->dbQ ( $q );
						
						$update_pos = array ();
						
						$update_pos ['id'] = $prev_item ['id'];
						
						$update_pos ['position'] = $cur_item ['position'];
						
						$q = " UPDATE $table set position='{$update_pos['position']}' where id='{$update_pos['id']}'  ";
						
						$q = $this->core_model->dbQ ( $q );
						
					//$this->taxonomySave ( $update_pos );
					

					}
				
				} else {
					
					$next_item = array ();
					
					$next_item ['parent_id'] = $cur_item ['parent_id'];
					
					$next_item ['taxonomy_type'] = $cur_item ['taxonomy_type'];
					
					$next_item ['position'] = $next_position;
					
					$next_item = $this->taxonomyGet ( $next_item );
					
					$next_item = $next_item [0];
					
					if ($next_item ['id'] != $cur_item ['id']) {
						
						$update_pos = array ();
						
						$update_pos ['id'] = $cur_item ['id'];
						
						$update_pos ['position'] = $next_item ['position'];
						
						//$this->taxonomySave ( $update_pos, true );
						

						$q = " UPDATE $table set position='{$update_pos['position']}' where id='{$update_pos['id']}'  ";
						
						$q = $this->core_model->dbQ ( $q );
						
						$update_pos = array ();
						
						$update_pos ['id'] = $next_item ['id'];
						
						$update_pos ['position'] = $cur_item ['position'];
						
						//$this->taxonomySave ( $update_pos, true );
						

						$q = " UPDATE $table set position='{$update_pos['position']}' where id='{$update_pos['id']}'  ";
						
						$q = $this->core_model->dbQ ( $q );
					
					}
				
				}
				
				$this->core_model->cleanCacheGroup ( 'taxonomy' );
				
				$this->core_model->cleanCacheGroup ( 'global' );
				
				//  $this->taxonomyFixPositionsForId ( $cur_item ['id'] );
				

				//var_dump ( $item );
				

				//exit ();
				

				return false;
			
			}
		
		} else {
			
			return false;
		
		}
	
	}
	
	function taxonomyFixPositionsForId($id) {
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$item = $this->taxonomyGetSingleItemById ( $id );
		
		if (empty ( $item )) {
			
			return false;
		
		}
		
		$cur_item = $item;
		
		$items = array ();
		
		$items ['parent_id'] = $cur_item ['parent_id'];
		
		$items ['taxonomy_type'] = $cur_item ['taxonomy_type'];
		
		$orderby [0] = 'position';
		
		$orderby [1] = 'ASC';
		
		$items = $this->taxonomyGet ( $items, $orderby );
		
		$i = 1;
		
		//var_dump($items);
		

		foreach ( $items as $item ) {
			
			$update_pos = array ();
			
			$update_pos ['id'] = $item ['id'];
			
			$update_pos ['position'] = $i;
			
			//$this->taxonomySave ( $update_pos, true );
			

			$q = " UPDATE $table set position='{$update_pos['position']}' where id='{$update_pos['id']}'  ";
			
			$q = $this->core_model->dbQ ( $q );
			
			$i ++;
		
		}
		
		//  $this->core_model->cacheDelete ( 'cache_group', 'taxonomy' );
		

		return $id;
	
	}
	
	function taxonomyGetMasterCategories($data = array()) {
		
		$get_master_categories = $data;
		
		$get_master_categories ['taxonomy_type'] = 'category';
		
		$get_master_categories ['parent_id'] = '0';
		
		if ($orderby == false) {
			
			$orderby [0] = 'position';
			
			$orderby [1] = 'ASC';
		
		}
		
		$items = $this->taxonomyGet ( $get_master_categories, $orderby );
		
		return $items;
	
	}
	
	/**

	 * @desc Get a single row from the taxonomy_table by given ID and returns it as one dimensional array

	 * @param int

	 * @return array

	 * @author      Peter Ivanov

	 * @version 1.0

	 * @since Version 1.0

	 */
	
	function taxonomyGetSingleItemById($id) {
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$taxonomy_id = intval ( $id );
		$cache_group = 'taxonomy/' . $taxonomy_id;
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		}
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$id = intval ( $id );
		
		$q = " select * from $table where id = $id limit 0,1";
		
		$q = $this->core_model->dbQuery ( $q );
		
		$q = $q [0];
		
		if (! empty ( $q )) {
			
			$this->core_model->cacheWriteAndEncode ( $q, $function_cache_id, $cache_group );
			
			//return $to_cache;
			

			return $q;
		
		} else {
			
			return false;
		
		}
	
	}
	
	function taxonomyCheckIfParamExistForSingleItemIdAndReturnVal($id, $the_param) {
		
		$data = $this->taxonomyGetSingleItemById ( $id );
		
		if ($data ["taxonomy_params"] != '') {
			
			//var_dump($data ["taxonomy_params"], $the_param);
			

			$params = explode ( ',', $data ["taxonomy_params"] );
			
			$params = mb_trimArray ( $params );
			
			foreach ( $params as $item ) {
				
				$itemss = explode ( ':', $item );
				
				//var_dump( $item);
				

				//foreach($itemss as $itm){
				

				$item1 = mb_trim ( $itemss [0] );
				
				$item2 = trim ( $itemss [0] );
				
				if (mb_strtolower ( $item1 ) == mb_strtolower ( $the_param )) {
					return $itemss [1];
				
				}
				
				if (strtolower ( $item2 ) == strtolower ( $the_param )) {
					return $itemss [1];
				
				}
				
				if (md5 ( $item2 ) == md5 ( $the_param )) {
					return $itemss [1];
				
				}
				
			//}
			

			}
		
		}
	
	}
	
	function taxonomyGetParentItemsAndReturnOnlyIds($id) {
		
		$items = $this->taxonomyGetParentItemsAndCache ( $id );
		
		if (! empty ( $items )) {
			
			$ids = array ();
			
			foreach ( $items as $item ) {
				
				$ids [] = $item ['id'];
			
			}
			
			$ids = array_unique ( $ids );
			
			return $ids;
		
		} else {
			
			return false;
		
		}
	
	}
	
	function taxonomyGetParentItemsAndCache($id) {
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$taxonomy_id = intval ( $id );
		$cache_group = 'taxonomy/' . $taxonomy_id;
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		}
		
		$data = $this->taxonomyGetParentItems ( $id );
		
		$this->core_model->cacheWriteAndEncode ( $data, $function_cache_id, $cache_group );
		
		return $data;
	
	}
	
	function taxonomyGetParentItems($id) {
		
		//var_dump($id);
		

		$data = array ();
		
		$data ['id'] = $id;
		$data ['taxonomy_type'] = 'category';
		
		$data = $this->taxonomyGet ( $data );
		
		$data = $data [0];
		
		$to_return = array ();
		
		if (empty ( $data )) {
			
			return false;
		
		}
		
		$to_return [] = $data;
		
		//$to_return
		

		if (intval ( $data ['parent_id'] ) != 0) {
			
			$data1 = array ();
			
			$data1 ['id'] = $data ['parent_id'];
			
			$data1 = $this->taxonomyGet ( $data1 );
			
			foreach ( $data1 as $item ) {
				
				$to_return [] = $item;
				
				$more = $this->taxonomyGetParentItems ( $item ['id'] );
				
				if (! empty ( $more )) {
					
					foreach ( $more as $mo ) {
						
						$to_return [] = $mo;
					
					}
				
				}
			
			}
		
		}
		
		//$to_return = array_unique($to_return);
		

		return $to_return;
	
	}
	
	function taxonomyGetChildrenItems($parent_id, $taxonomy_type = false, $orderby = false) {
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		if ($orderby == false) {
			
			$orderby [0] = 'updated_on';
			
			$orderby [1] = 'DESC';
		
		}
		
		$data = array ();
		
		$data ['parent_id'] = $parent_id;
		
		if ($taxonomy_type != false) {
			
			$data ['taxonomy_type'] = $taxonomy_type;
		
		} else {
			$data ['taxonomy_type'] = 'category';
		}
		
		$save = $this->taxonomyGet ( $data, $orderby, $no_limits = true );
		
		return $save;
	
	}
	
	function taxonomyGetChildrenItemsIdsRecursiveAndCache($parent_id, $type = false, $visible_on_frontend = false) {
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$taxonomy_id = intval ( $parent_id );
		$cache_group = 'taxonomy/' . $taxonomy_id;
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		} else {
			
			$to_cache = $this->taxonomyGetChildrenItemsIdsRecursive ( $parent_id, $type, $visible_on_frontend );
			
			$this->core_model->cacheWriteAndEncode ( $to_cache, $function_cache_id, $cache_group );
			
			return $to_cache;
		
		}
	
	}
	
	function taxonomyGetChildrenItemsIdsRecursive($parent_id, $type = false, $visible_on_frontend = false) {
		
		global $cms_db_tables;
		$taxonomy_id = intval ( $parent_id );
		$cache_group = 'taxonomy/' . $taxonomy_id;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$table_content = $cms_db_tables ['table_content'];
		
		if ($orderby == false) {
			
			$orderby [0] = 'updated_on';
			
			$orderby [1] = 'DESC';
		
		}
		
		if (intval ( $parent_id ) == 0) {
			
			return false;
		
		}
		
		$data = array ();
		
		$data ['parent_id'] = $parent_id;
		
		if ($type != FALSE) {
			
			$data ['taxonomy_type'] = $type;
			
			$type_q = " and taxonomy_type='$type'   ";
		
		} else {
			$type = 'category_item';
			$data ['taxonomy_type'] = $type;
			
			$type_q = " and taxonomy_type='$type'   ";
		
		}
		
		if ($visible_on_frontend == true) {
			
			$visible_on_frontend_q = " and to_table_id in (select id from $table_content where visible_on_frontend='y') ";
		
		}
		
		//$save = $this->taxonomyGet ( $data = $data, $orderby = $orderby );
		

		$cache_group = 'taxonomy/' . $parent_id;
		$q = " SELECT id,    parent_id from $table where parent_id= $parent_id   $type_q  $visible_on_frontend_q";
		//var_dump($cache_group);
		$q_cache_id = __FUNCTION__ . md5 ( $q );
		//var_dump($q_cache_id);
		$save = $this->core_model->dbQuery ( $q, $q_cache_id, $cache_group );
		
		//$save = $this->taxonomyGetSingleItemById ( $parent_id );
		if (empty ( $save )) {
			return false;
		}
		$to_return = array ();
		if (! empty ( $save )) {
			$to_return [] = $parent_id;
		}
		foreach ( $save as $item ) {
			$to_return [] = $item ['id'];
			$clidren = $this->taxonomyGetChildrenItemsIdsRecursive ( $item ['id'], $type, $visible_on_frontend );
			if (! empty ( $clidren )) {
				foreach ( $clidren as $temp ) {
					$to_return [] = $temp;
				}
			}
		}
		
		$to_return = array_unique ( $to_return );
		
		return $to_return;
	
	}
	
	function taxonomyDelete($id) {
		
		if (intval ( $id ) == 0) {
			
			return false;
		
		}
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$item_to_delete = array ();
		
		$item_to_delete ['id'] = $id;
		
		$item_to_delete = $this->taxonomyGet ( $item_to_delete );
		
		$item_to_delete = $item_to_delete [0];
		
		if (empty ( $item_to_delete )) {
			
			return false;
		
		}
		
		$new_parent = intval ( $item_to_delete ['parent_id'] );
		
		$old_parent = intval ( $item_to_delete ['id'] );
		
		$q = "UPDATE $table set parent_id = $new_parent where parent_id= $old_parent";
		
		$this->core_model->dbQ ( $q );
		
		$this->core_model->deleteDataById ( $table, $id );
		
		$this->core_model->cleanCacheGroup ( 'taxonomy' . DIRECTORY_SEPARATOR . $id );
		$this->core_model->cleanCacheGroup ( 'taxonomy' . DIRECTORY_SEPARATOR . $new_parent );
		$this->core_model->cleanCacheGroup ( 'taxonomy' . DIRECTORY_SEPARATOR . 'global' );
		$this->core_model->cleanCacheGroup ( 'taxonomy' . DIRECTORY_SEPARATOR . '0' );
		
		return true;
	
	}
	
	function taxonomyGetTaxonomyToTableIdsForTaxonomyRootIdAndCache($root, $visible_on_frontend = false, $non_recursive = false) {
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$taxonomy_id = intval ( $root );
		$cache_group = 'taxonomy/' . $taxonomy_id;
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		} else {
			
			$to_cache = $this->taxonomyGetTaxonomyToTableIdsForTaxonomyRootId ( $root, $visible_on_frontend, $non_recursive );
			
			$this->core_model->cacheWriteAndEncode ( $to_cache, $function_cache_id, $cache_group );
			
			return $to_cache;
		
		}
	
	}
	function taxonomyGetTaxonomyToTableIdsForTaxonomyRootId($root, $visible_on_frontend = false, $non_recursive = false) {
		
		if (intval ( $root ) == 0) {
			
			return false;
		
		}
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$table_content = $cms_db_tables ['table_content'];
		
		$ids = array ();
		
		//$ids [] = $root;
		

		if ($visible_on_frontend == true) {
			
			$visible_on_frontend_q = " and to_table_id in (select id from $table_content where visible_on_frontend='y') ";
		
		}
		
		$root = intval ( $root );
		
		$data = array ();
		
		$data ['parent_id'] = $root;
		$root = intval ( $root );
		
		$q = " SELECT id, parent_id,to_table_id from $table where parent_id=$root $visible_on_frontend_q and taxonomy_type='category_item' ";
		
		$taxonomies = $this->core_model->dbQuery ( $q, __FUNCTION__ . md5 ( $q ), 'taxonomy/' . $root );
		
		//var_dump($taxonomies);
		

		if (! empty ( $taxonomies )) {
			
			foreach ( $taxonomies as $item ) {
				
				if (intval ( $item ['to_table_id'] ) != 0) {
					
					$ids [] = $item ['to_table_id'];
				}
				
				if ($non_recursive == false) {
					$next = $this->taxonomyGetTaxonomyToTableIdsForTaxonomyRootId ( $item ['id'], $visible_on_frontend );
					
					if (! empty ( $next )) {
						
						foreach ( $next as $n ) {
							
							if ($n != '') {
								
								$ids [] = $n;
							
							}
						
						}
					
					}
				}
			
			}
		
		}
		
		if (! empty ( $ids )) {
			
			$ids = array_unique ( $ids );
			
			asort ( $ids );
			
			return $ids;
		
		} else {
			
			return false;
		
		}
	
	}
	
	function taxonomyGetTaxonomyIdsForTaxonomyRootIdAndCache($root, $incliude_root = false, $recursive = false, $type = 'category') {
		
		//return false;
		

		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		$root = intval ( $root );
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$cache_group = 'taxonomy/' . $root;
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		} else {
			
			$to_cache = $this->taxonomyGetTaxonomyIdsForTaxonomyRootId ( $root, $incliude_root, $recursive, $type );
			
			$this->core_model->cacheWriteAndEncode ( $to_cache, $function_cache_id, $cache_group );
			
			return $to_cache;
		
		}
	
	}
	
	function taxonomyGetTaxonomyIdsForTaxonomyRootId($root, $incliude_root = false, $recursive = false, $type = 'category') {
		
		if (intval ( $root ) == 0) {
			
			return false;
		
		}
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$ids = array ();
		
		if ($incliude_root == true) {
			
			$ids [] = $root;
		
		}
		
		$root = intval ( $root );
		$q = " select id from $table where parent_id = $root and taxonomy_type='{$type}' ";
		
		$taxonomies = $this->core_model->dbQuery ( $q, $cache_id = __FUNCTION__ . md5 ( $q . $root . serialize ( $incliude_root ) ), $cache_group = 'taxonomy/' . $root );
		
		if (! empty ( $taxonomies )) {
			
			foreach ( $taxonomies as $item ) {
				
				if (intval ( $item ['id'] ) != 0) {
					
					$ids [] = $item ['id'];
				
				}
				
				if ($recursive == true) {
					$next = $this->taxonomyGetTaxonomyIdsForTaxonomyRootId ( $item ['id'], false, $recursive, $type );
					
					if (! empty ( $next )) {
						
						foreach ( $next as $n ) {
							
							if ($n != '') {
								
								$ids [] = $n;
							
							}
						
						}
					
					}
				}
			}
		
		}
		
		if (! empty ( $ids )) {
			
			$ids = array_unique ( $ids );
			
			//asort ( $ids );
			

			//
			

			return $ids;
		
		} else {
			
			return false;
		
		}
	
	}
	
	function taxonomyGetParentIdsForId($id, $without_main_parrent = false, $taxonomy_type = 'category') {
		
		if (intval ( $id ) == 0) {
			
			return FALSE;
		
		}
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$ids = array ();
		
		$data = array ();
		
		if ($without_main_parrent == true) {
			
			$with_main_parrent_q = " and parent_id<>0 ";
		
		} else {
			
			$with_main_parrent_q = false;
		
		}
		$id = intval ( $id );
		$q = " select id, parent_id  from $table where id = $id and  taxonomy_type='{$taxonomy_type}'  $with_main_parrent_q ";
		
		$taxonomies = $this->core_model->dbQuery ( $q, $cache_id = __FUNCTION__ . md5 ( $q ), $cache_group = 'taxonomy/' . $id );
		
		//var_dump($q);
		

		//  var_dump($taxonomies);
		

		//  exit;
		

		if (! empty ( $taxonomies )) {
			
			foreach ( $taxonomies as $item ) {
				
				if (intval ( $item ['id'] ) != 0) {
					
					$ids [] = $item ['parent_id'];
				
				}
				
				$next = $this->taxonomyGetParentIdsForId ( $item ['parent_id'], $without_main_parrent );
				
				if (! empty ( $next )) {
					
					foreach ( $next as $n ) {
						
						if ($n != '') {
							
							$ids [] = $n;
						
						}
					
					}
				
				}
			
			}
		
		}
		
		if (! empty ( $ids )) {
			
			$ids = array_unique ( $ids );
			
			//asort ( $ids );
			

			//
			

			return $ids;
		
		} else {
			
			return false;
		
		}
	
	}
	
	function taxonomyGetCategoriesForContentId($content_id) {
		
		if (intval ( $content_id ) == 0) {
			
			return false;
		
		}
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		$content_id = intval ( $content_id );
		$cache_group = 'content/' . $content_id;
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		}
		
		$cat_ids = $this->taxonomyGetTaxonomyIdsForContentId ( $content_id, $taxonomy_type = 'categories' );
		
		$to_return = array ();
		
		foreach ( $cat_ids as $item ) {
			
			$cat = $this->taxonomyGetSingleItemById ( $item );
			
			$to_return [] = $cat;
		
		}
		
		$this->core_model->cacheWriteAndEncode ( $to_return, $function_cache_id, $cache_group );
		
		return $to_return;
	
	}
	
	function taxonomyGetTaxonomyIdsForContentId($content_id, $taxonomy_type = 'categories') {
		
		if (intval ( $content_id ) == 0) {
			
			return false;
		
		}
		
		$function_cache_id = false;
		
		$args = func_get_args ();
		
		foreach ( $args as $k => $v ) {
			
			$function_cache_id = $function_cache_id . serialize ( $k ) . serialize ( $v );
		
		}
		$content_id = intval ( $content_id );
		$cache_group = 'content/' . $content_id;
		
		$function_cache_id = __FUNCTION__ . md5 ( $function_cache_id );
		
		$cache_content = $this->core_model->cacheGetContentAndDecode ( $function_cache_id, $cache_group );
		
		if (($cache_content) != false) {
			
			return $cache_content;
		
		}
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		$data = array ();
		
		$data ['to_table'] = 'table_content';
		
		$data ['to_table_id'] = $content_id;
		$taxonomy_type_q = false;
		if ($taxonomy_type == 'categories') {
			
			$data ['taxonomy_type'] = 'category_item';
			$taxonomy_type_q = "and taxonomy_type = 'category_item' ";
		
		}
		
		if ($taxonomy_type == 'tags') {
			
			$data ['taxonomy_type'] = 'tag_item';
			$taxonomy_type_q = "and taxonomy_type = 'tag_item' ";
		}
		
		$q = "select parent_id from $table where  to_table='to_table' and to_table_id=$content_id $taxonomy_type_q ";
		$data = $this->core_model->dbQuery ( $q, __FUNCTION__ . md5 ( $q ), $cache_group = 'content/' . $content_id );
		// var_dump ( $data );
		

		if (! empty ( $data )) {
			
			$results = array ();
			
			foreach ( $data as $item ) {
				
				$results [] = $item ['parent_id'];
			
			}
			
			$results = array_unique ( $results );
		
		}
		
		$this->core_model->cacheWriteAndEncode ( $results, $function_cache_id, $cache_group );
		
		return $results;
		
	//var_dump ( $data );
	

	}
	
	function contentActiveCategoriesForToTableId($to_table_id = false) {
	
	}
	
	function taxonomyGetAvailableTags($to_table = false, $to_table_id = false) {
		
		global $cms_db_tables;
		
		$table = $cms_db_tables ['table_taxonomy'];
		
		if ($to_table != false) {
			
			$q1 = " and to_table='$to_table'   ";
		
		}
		
		if ($to_table_id != false) {
			
			$q2 = " and to_table_id='$to_table_id'   ";
		
		}
		
		$q = "select taxonomy_value from $table where taxonomy_type='tag'  $q1  $q2 group by taxonomy_value";
		
		$cache_id = __FUNCTION__ . md5 ( $q );
		
		$q = $this->core_model->dbQuery ( $q, $cache_id, 'taxonomy/global' );
		
		$to_return = array ();
		
		if (! empty ( $q )) {
			
			foreach ( $q as $item ) {
				
				$to_return [] = $item ['taxonomy_value'];
			
			}
			
			return $to_return;
		
		} else {
			
			return false;
		
		}
	
	}

}
