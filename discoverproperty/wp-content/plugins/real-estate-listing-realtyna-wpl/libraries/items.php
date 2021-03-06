<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/**
** Items Library
** Developed 07/18/2013
**/

class wpl_items
{
	/**
		@input {parent_id}, [item_type], [parent_kind], [category], [enabled] and [condition]
		@return items
	**/
	public static function get_items($parent_id, $item_type = '', $parent_kind = 0, $category = '', $enabled = 1, $condition = '')
	{
		/** first validation **/
		if(trim($parent_id) == '') return NULL;
		
		if(trim($condition) == '')
		{
			$condition = "";
			$condition .= " AND `parent_id`='$parent_id' AND `parent_kind`='$parent_kind'";
			
			if(trim($item_type) != '') $condition .= " AND `item_type`='$item_type'";
			if(trim($category) != '') $condition .= " AND `item_cat`='$category'";
			if(trim($enabled) != '') $condition .= " AND `enabled`>='$enabled'";
			
			$condition .= " ORDER BY `index` ASC";
		}
		
		$query = "SELECT * FROM `#__wpl_items` WHERE 1 ".$condition;
		$records = wpl_db::select($query);
		
		if(trim($item_type) != '') return $records;
		
		$items = array();
		foreach($records as $record)
		{
			$items[$record->item_type][] = $record;
		}
		
		return $items;
	}
	
	/**
		@input {values} and [item_id]
		@return affected rows or insert id
		@description use this function for insert or edit an item. if the item id is passed it tries to update current item using item id
	**/
	public static function save($values = array(), $item_id = '')
	{
		/** first validation **/
		if(!is_array($values) or count($values) == 0) return false;
		
		if($item_id) $result = wpl_items::update($item_id, $values);
		else $result = wpl_items::insert($values);
		
		return $result;
	}
	
	/**
		@input {item_id} and {values}
		@return affected rows
	**/
	public static function update($item_id, $values = array())
	{
		/** first validation **/
		if(!trim($item_id) or count($values) == 0) return false;
		
		$q = '';
		foreach($values as $key=>$value) $q .= "`$key`='$value', ";
		$q = trim($q, ", ");
		
		$query = "UPDATE `#__wpl_items` SET ".$q." WHERE `id`='$item_id'";
		$affected_rows = wpl_db::q($query, 'update');
		
		/** trigger event **/
		wpl_global::event_handler('item_updated', array('item_id'=>$item_id));
		
		return $affected_rows;
	}
	
	/**
		@input {values}
		@return id of new record
	**/
	public static function insert($values = array())
	{
		/** first validation **/
		if(count($values) == 0) return false;
		
		$q1 = '';
		$q2 = '';
		
		foreach($values as $key=>$value)
		{
			$q1 .= "`$key`,";
			$q2 .= "'$value',";
		}
		
		$q1 = trim($q1, ', ');
		$q2 = trim($q2, ', ');
		
		$query = "INSERT INTO `#__wpl_items` (".$q1.") VALUES (".$q2.")";
		$insert_id = wpl_db::q($query, 'insert');
		
		/** trigger event **/
		wpl_global::event_handler('item_added', array('item_id'=>$insert_id));
		
		return $insert_id;
	}
	
	/**
		@input {item_id}
		@return item record
	**/
	public static function get($item_id, $selects = '*')
	{
		/** get item **/
		return wpl_db::get($selects, 'wpl_items', 'id', $item_id);
	}
	
	/**
		@input {item_id}
		@return boolean
	**/
	public static function delete($item_id)
	{
		/** trigger event **/
		wpl_global::event_handler('item_deleted', array('item_id'=>$item_id));
		
		/** delete item **/
		return wpl_db::delete('wpl_items', $item_id);
	}
	
	/**
		@input {parent_id}, {kind}
		@return boolean
	**/
	public static function delete_all_items($parent_id, $kind = 0)
	{
		/** first validation **/
		if(!trim($parent_id) or trim($kind) == '') return false;
		
		/** trigger event **/
		wpl_global::event_handler('all_items_deleted', array('parent_id'=>$parent_id, 'kind'=>$kind));
		
		/** delete items **/
		$query = "DELETE FROM `#__wpl_items` WHERE `parent_kind`='$kind' AND `parent_id`='$parent_id'";
		return wpl_db::q($query);
	}
	
	/**
		@input {parent_id}, [order], and [column]
		@sort items based on order and order have value of column in each record
	**/
	public static function sort_items($parent_id, $order, $column = 'item_name')
	{
		$order_array = explode(',' , $order);
		$counter = 0;
		
		foreach($order_array as $file_name)
		{
			self::update_file($file_name, $parent_id, array('index'=>(++$counter)));
		}
	}
	
	/**
		@input {file_name}, [parent_id], and [kind]
		@delete record from item table and files
	**/
	public static function delete_file($file_name, $parent_id, $kind = 0)
	{
		if(!trim($file_name) or !trim($parent_id)) return false;
		
		$query = "DELETE FROM `#__wpl_items` WHERE `parent_kind`='$kind' AND `parent_id`='$parent_id' AND `item_name`='$file_name'";
		$affected_rows = wpl_db::q($query, 'delete');
		
		$folder = wpl_items::get_path($parent_id, $kind);
		
		if(wpl_file::exists($folder . $file_name))
		{
			wpl_file::delete($folder . $file_name);
			if(wpl_file::exists($folder . 'thumbnail' .DS. $file_name)) wpl_file::delete($folder . 'thumbnail' .DS. $file_name);
        }
		
		/** trigger event **/
		wpl_global::event_handler('item_deleted', array('file_name'=>$file_name,'parent_id'=>$parent_id));	
	}
	
	/**
		@input {file_name}, [parent_id], and [values]
		@return update record of files in item table
	**/
	public static function update_file($file_name, $parent_id, $values = array())
	{
		/** first validation **/
		if(!trim($file_name) or !trim($parent_id) or count($values) == 0) return false;
		
		$q = '';
		
		foreach($values as $key=>$value) $q .= "`$key`='$value', ";
		
		$q = trim($q, ", ");
		$file_name = trim($file_name);
		
		$query = "UPDATE `#__wpl_items` SET ".$q." WHERE `parent_id`='$parent_id' AND `item_name`='$file_name'";
		$affected_rows = wpl_db::q($query, 'update');
		
		/** trigger event **/
		wpl_global::event_handler('item_updated', array('file_name'=>$file_name,'parent_id'=>$parent_id));
		return $affected_rows;
	}
	
	/**
		@input [item_type], [parent_kind][condition]
		@return items categories
	**/
	public static function get_item_categories($item_type, $parent_kind, $condition = '')
	{
		if(trim($condition) == '')
		{
			$condition = "";
			
			if(trim($item_type) != '') $condition .= " AND `item_type`='$item_type'";
			
			$condition .= " AND `parent_kind`='$parent_kind'";
			$condition .= " ORDER BY `index` ASC";
		}
		
		$query = "SELECT * FROM `#__wpl_item_categories` WHERE 1 ".$condition;
		$records = wpl_db::select($query);
		
		if(trim($item_type) != '') return $records;
		
		$items = array();
		foreach($records as $record)
		{
			$items[$record->item_type][] = $record;
		}
		
		return $items;
	}
	
	/**
		@input {parent_id}, [parent_kind]
		@return WPL folder for uploaded files
	**/
	public static function get_folder($parent_id, $kind = 0)
	{
		if($kind == 2) return wpl_global::get_wp_site_url().'wp-content/uploads/WPL/users/'.$parent_id.'/';
		else
			return wpl_global::get_wp_site_url().'wp-content/uploads/WPL/'.$parent_id.'/';
	}
	
	/**
		@input {parent_id}, [parent_kind]
		@return WPL path for uploaded files
	**/
	public static function get_path($parent_id, $kind = 0)
	{
		if($kind == 2) $path = wpl_global::get_upload_base_path(). 'users' .DS. $parent_id .DS;
		else $path = wpl_global::get_upload_base_path(). $parent_id .DS;
		
		if(!wpl_folder::exists($path)) wpl_folder::create($path);
		
		return $path;
	}
	
	/**
		@input {parent_id}, [item_type], [parent_kind], [category], [enabled] and [condition]
		@return maximum index
	**/
	public static function get_maximum_index($parent_id, $item_type = '', $parent_kind = 0, $category = '', $enabled = '', $condition = '')
	{
		/** first validation **/
		if(trim($parent_id) == '') return NULL;
		
		if(trim($condition) == '')
		{
			$condition = "";
			$condition .= " AND `parent_id`='$parent_id' AND `parent_kind`='$parent_kind'";
			
			if(trim($item_type) != '') $condition .= " AND `item_type`='$item_type'";
			if(trim($category) != '') $condition .= " AND `item_cat`='$category'";
			if(trim($enabled) != '') $condition .= " AND `enabled`>='$enabled'";
		}
		
		$query = "SELECT MAX(`index`) as max FROM `#__wpl_items` WHERE 1 ".$condition;
		
		$index = wpl_db::select($query,'loadObject');
		return $index->max;
	}
	
	/**
		@input {parent_id}, [parent_kind], [category] and [enabled]
		@return rendered gallery
	**/
	public static function get_gallery($parent_id, $parent_kind = 0, $category = '', $enabled = 1)
	{
		$items = wpl_items::get_items($parent_id, 'gallery', $parent_kind , $category, $enabled);
		
		/** render items **/
		return wpl_items::render_gallery($items);
	}
	
	/**
		@input array images (getted by wpl_items::get_items)
		@return rendered gallery
	**/
	public static function render_gallery($images = array())
	{
		/** force to array **/
		$images = (array) $images;
		$return = array();
		$i = 0;
		
		foreach($images as $image)
		{
			/** force to array **/
			$image = (array) $image;
			
			$image_path = self::get_path($image['parent_id'], $image['parent_kind']) . $image['item_name'];
			$image_url = self::get_folder($image['parent_id'], $image['parent_kind']) . $image['item_name'];
			
            /** external images **/
            if(isset($image['item_cat']) and $image['item_cat'] == 'external')
            {
                $image_path = $image['item_extra3'];
                $image_url = $image['item_extra3'];
            }
            
			/** existance check **/
			if(!wpl_file::exists($image_path) and $image['item_cat'] != 'external') continue;
			
			$pathinfo = @pathinfo($image_path);
			
			$return[$i]['item_id'] = $image['id'];
			$return[$i]['path'] = $image_path;
			$return[$i]['url'] = $image_url;
			$return[$i]['size'] = @filesize($image_path);
			$return[$i]['title'] = (string) $image['item_extra1'];
			$return[$i]['description'] = (string) $image['item_extra2'];
			$return[$i]['category'] = $image['item_cat'];
			$return[$i]['ext'] = $pathinfo['extension'];
			$return[$i]['raw'] = $image;
			
			$i++;
		}
		
		return $return;
	}
	
	/**
		@input array attachments (getted by wpl_items::get_items)
		@return rendered attachments
	**/
	public static function render_attachments($attachments = array())
	{
		_wpl_import('libraries.render');
		
		/** force to array **/
		$attachments = (array) $attachments;
		$return = array();
		$i = 0;
		
		foreach($attachments as $attachment)
		{
			/** force to array **/
			$attachment = (array) $attachment;
			
			$att_path = self::get_path($attachment['parent_id'], $attachment['parent_kind']) . $attachment['item_name'];
			$att_url = self::get_folder($attachment['parent_id'], $attachment['parent_kind']) . $attachment['item_name'];
			
			/** existance check **/
			if(!wpl_file::exists($att_path)) continue;
			
			$pathinfo = @pathinfo($att_path);
			$filesize = @filesize($att_path);
			
			$return[$i]['item_id'] = $attachment['id'];
			$return[$i]['name'] = $attachment['item_name'];
			$return[$i]['path'] = $att_path;
			$return[$i]['url'] = $att_url;
			$return[$i]['size'] = $filesize;
			$return[$i]['rendered_size'] = wpl_render::render_file_size($filesize);
			$return[$i]['title'] = (string) $attachment['item_extra1'];
			$return[$i]['description'] = (string) $attachment['item_extra2'];
			$return[$i]['category'] = $attachment['item_cat'];
			$return[$i]['ext'] = $pathinfo['extension'];
			$return[$i]['raw'] = $attachment;
			
			/** attachment icon **/
			$icon_url = wpl_global::get_wpl_asset_url('img/extentions/'.$pathinfo['extension'].'.png');
			$icon_path = WPL_ABSPATH. 'assets' .DS. 'img' .DS. 'extentions' .DS. $pathinfo['extension'].'.png';
			
			if(!wpl_file::exists($icon_path))
			{
				$icon_url = wpl_global::get_wpl_asset_url('img/extentions/default.png');
				$icon_path = WPL_ABSPATH .DS. 'assets' .DS. 'img' .DS. 'extentions' .DS. 'default.png';
			}
			
			$return[$i]['icon_path'] = $icon_path;
			$return[$i]['icon_url'] = $icon_url;
			
			$i++;
		}
		
		return $return;
	}
	
	/**
		@input array videos (getted by wpl_items::get_items)
		@return rendered videos
	**/
	public static function render_videos($videos = array())
	{
		/** force to array **/
		$videos = (array) $videos;
		$return = array();
		$i = 0;
		
		foreach($videos as $video)
		{
			/** force to array **/
			$video = (array) $video;
			
			$return[$i]['item_id'] = $video['id'];
			$return[$i]['category'] = $video['item_cat'];
				
			if($video['item_cat'] == 'video')
			{
				$video_path = self::get_path($video['parent_id'], $video['parent_kind']) . $video['item_name'];
				$video_url = self::get_folder($video['parent_id'], $video['parent_kind']) . $video['item_name'];
				
				/** existance check **/
				if(!wpl_file::exists($video_path)) continue;
				
				$pathinfo = @pathinfo($video_path);
				
				$return[$i]['path'] = $video_path;
				$return[$i]['url'] = $video_url;
				$return[$i]['size'] = @filesize($video_path);
				$return[$i]['title'] = (string) $video['item_extra1'];
				$return[$i]['description'] = (string) $video['item_extra2'];
				$return[$i]['ext'] = $pathinfo['extension'];
			}
			elseif($video['item_cat'] == 'video_embed')
			{
				$return[$i]['path'] = '';
				$return[$i]['url'] = (string) $video['item_extra2'];
				$return[$i]['size'] = '';
				$return[$i]['title'] = (string) $video['item_name'];
				$return[$i]['description'] = (string) $video['item_extra1'];
				$return[$i]['ext'] = '';
			}
			
			$return[$i]['raw'] = $video;
			$i++;
		}
		
		return $return;
	}
	
	/**
		@input string $property_id, array $property_gallery, array $custom_sizes
		@param array $custom_sizes -- array('100_200', '50_75')
		@return array rendered gallery
	**/
	public static function render_gallery_custom_sizes($property_id, $images = '', $custom_sizes = array())
	{
		$kind = wpl_property::get_property_kind($property_id);
		if(!$images) $images = wpl_items::get_items($property_id, 'gallery', $kind);
		
		/** no image gallery **/
		if(!count($images)) return array();
		
		$return = array();
		foreach($custom_sizes as $custom_size)
		{
			$custom_size = str_replace('*', '_', $custom_size);
			list($x, $y) = explode('_', $custom_size);
			if(trim($x) == '' or trim($y) == '') continue;
			if(!is_numeric($x) or !is_numeric($y)) continue;
			
			$i = 0;
			foreach($images as $image)
			{
				/** force to array **/
				$image = (array) $image;
				
				$source_path = self::get_path($image['parent_id'], $image['parent_kind']) . $image['item_name'];
				$source_url = self::get_folder($image['parent_id'], $image['parent_kind']) . $image['item_name'];
				
				$params = array('image_name'=>$image['item_name'], 'image_source'=>$source_path, 'image_parentid'=>$image['parent_id'], 'image_parentkind'=>$image['parent_kind']);
                
                /** taking care for external images **/
				if($image['item_cat'] != 'external')
                {
                    $dest_url = wpl_images::create_gallary_image($x, $y, $params, 0, 0);
                    $pathinfo = @pathinfo($dest_url);
                    $dest_path = self::get_path($image['parent_id'], $image['parent_kind']) . $pathinfo['basename'];
                }
                else
                {
                    $dest_url = $source_url;
                    $pathinfo = @pathinfo($dest_url);
                    $dest_path = $source_path;
                }
				
				$return[$custom_size][$i]['item_id'] = $image['id'];
				$return[$custom_size][$i]['custom_size'] = $custom_size;
				$return[$custom_size][$i]['path'] = $dest_path;
				$return[$custom_size][$i]['url'] = $dest_url;
				$return[$custom_size][$i]['size'] = @filesize($dest_path);
				$return[$custom_size][$i]['title'] = (string) $image['item_extra1'];
				$return[$custom_size][$i]['description'] = (string) $image['item_extra2'];
				$return[$custom_size][$i]['category'] = $image['item_cat'];
				$return[$custom_size][$i]['ext'] = $pathinfo['extension'];
				$return[$custom_size][$i]['raw'] = $image;
				
				$i++;
			}
		}
		
		return $return;
	}
}