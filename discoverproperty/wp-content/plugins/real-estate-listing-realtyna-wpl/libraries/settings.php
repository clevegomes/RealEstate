<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/**
** Settings Library
** Developed 03/01/2013
**/

class wpl_settings
{
	public static $wpl_settings = array(); /** used for caching in get_settings function **/
	
	/**
		@input [category] and [return_records]
		@params: boolean return_records: it returns raw records
		@return settings array or raw records
	**/
	public static function get_settings($category = '', $showable = 0, $return_records = false)
	{
		/** return from cache if exists **/
		$cache_key = trim($category) != '' ? $category : 'all';
		if(isset(self::$wpl_settings[$cache_key]) and !$return_records) return self::$wpl_settings[$cache_key];
		
		$condition = '';
		if(trim($category) != '')
		{
			if(!is_numeric($category)) $category = wpl_settings::get_category_id($category);
			$condition .= " AND `category`='$category'";
		}
		
		$condition .= " AND `showable`>='$showable'";
		
		$query = "SELECT * FROM `#__wpl_settings` WHERE 1 ".$condition." ORDER BY `index` ASC";
		$records = wpl_db::select($query);
		
		if($return_records)
		{
			return $records;
		}
		
		$settings = array();
		foreach($records as $record)
		{
			$settings[$record->setting_name] = $record->setting_value;
		}
		
		/** add to cache **/
		self::$wpl_settings[$cache_key] = $settings;
		return $settings;
	}
	
	/**
		@input setting name and [value] and [category] and [condition]
		@return affected rows or insert id
		@description this function takes care for modifying existing setting or inserting new record
	**/
	public static function save_setting($name, $value = '', $category = '', $condition = '')
	{
		/** first validation **/
		if(trim($name) == '') return false;
		
		$exists = wpl_settings::is_setting_exists($name, $category);
		
		if($exists) $result = wpl_settings::update_setting($name, $value, $category, $condition);
		else $result = wpl_settings::insert_setting($name, $value, $category);
		
		return $result;
	}
	
	/**
		@input setting name and [value] and [category] and [condition]
		@return affected rows
	**/
	public static function update_setting($name, $value = '', $category = '', $condition = '')
	{
		/** first validation **/
		if(trim($name) == '') return false;
		
		if(trim($condition) == '' and trim($category) != '')
		{
			if(!is_numeric($category)) $category = wpl_settings::get_category_id($category);
			$condition .= "AND `category`='$category'";
		}
		
		$query = "UPDATE `#__wpl_settings` SET `setting_value`='$value' WHERE `setting_name`='$name' ".$condition;
		$result = wpl_db::q($query, 'update');
        
        /** trigger event **/
        wpl_global::event_handler('settings_updated', array('setting_value'=>$value, 'setting_name'=>$name));
        
        return $result;
	}
	
	/**
		@input setting name and [value] and [category]
		@return id of new record
	**/
	public static function insert_setting($name, $value = '', $category = '')
	{
		/** first validation **/
		if(trim($name) == '') return false;
		
		$category_id = 1;
		if(trim($category) != '')
		{
			if(!is_numeric($category)) $category_id = wpl_settings::get_category_id($category);
			else $category_id = $category;
		}
		
		$query = "INSERT INTO `#__wpl_settings` (`setting_name`,`setting_value`,`category`) VALUES ('$name','$value','$category_id')";
		$id = wpl_db::q($query, 'insert');
        
        /** trigger event **/
        wpl_global::event_handler('settings_added', array('id'=>$id));
        
        return $id;
	}
	
	/**
		@input {setting_name} and [category]
		@return setting value
	**/
	public static function get($setting_name, $category = '')
	{
		/** return from cache if exists **/
		$cache_key = trim($category) != '' ? $category : 'all';
		if(isset(self::$wpl_settings[$cache_key][$setting_name])) return self::$wpl_settings[$cache_key][$setting_name];
		
		$condition = "`setting_name`='$setting_name' ";
		if(trim($category) != '')
		{
			if(!is_numeric($category)) $category = wpl_settings::get_category_id($category);
			$condition .= " AND `category`='$category' ";
		}
		
		/** get settings **/
		return wpl_db::get('setting_value', 'wpl_settings', '', '', true, $condition);
	}
	
	/**
		@input category (string)
		@return category id
	**/
	public static function get_category_id($category)
	{
		$category = strtolower($category);
		$query = "SELECT `id` FROM `#__wpl_setting_categories` WHERE LOWER(name)='$category'";
		
		return wpl_db::select($query, 'loadResult');
	}
	
	/**
		@input showable
		@return array category
	**/
	public static function get_categories($showable = 1)
	{
		$query = "SELECT * FROM `#__wpl_setting_categories` WHERE `showable`>='$showable' ORDER BY `index` ASC";
		return wpl_db::select($query, 'loadObjectList');
	}
	
	/**
		@input setting name and [category]
		@return boolean
	**/
	public static function is_setting_exists($name, $category = '')
	{
		$condition = '';
		
		if(trim($category) != '')
		{
			if(!is_numeric($category)) $category = wpl_settings::get_category_id($category);
			$condition .= "AND `category`='$category'";
		}
		
		$query = "SELECT COUNT(`id`) FROM `#__wpl_settings` WHERE `setting_name`='$name' ".$condition;
		$num = wpl_db::num($query);
		
		return ($num ? true : false);
	}
	
	/**
		@input setting record
		@return void
		@description this function generates setting form
	**/
	public static function generate_setting_form($setting_record)
	{
		/** first validation **/
		if(!$setting_record) return;
		
		$done_this = false;
		$type = $setting_record->type;
		$value = $setting_record->setting_value;
		$params = json_decode($setting_record->params, true);
		$options = json_decode($setting_record->options, true);
		
		$setting_title = trim($setting_record->title) != '' ? __($setting_record->title, WPL_TEXTDOMAIN) : __(str_replace('_', ' ', $setting_record->setting_name), WPL_TEXTDOMAIN);
		
		/** get files **/
		$path = WPL_ABSPATH .DS. 'libraries' .DS. 'settings_form';
		$files = array();
		
		if(wpl_folder::exists($path))
		{
			$files = wpl_folder::files($path, '.php$');
			
			foreach($files as $file)
			{
				include($path .DS. $file);
			}
		}
	}
	
	/**
		@input setting records
		@return void
		@description this function generates setting forms
	**/
	public static function generate_setting_forms($setting_records)
	{
		/** first validation **/
		if(!$setting_records) return;
		
		foreach($setting_records as $key=>$setting_record)
		{
			self::generate_setting_form($setting_record);
		}
	}
	
	/**
		@input string cache_type
		@return void
		@description use this function for removing WPL caches
	**/
	public static function clear_cache($cache_type = 'all')
	{
		/** first validation **/
		$cache_type = strtolower($cache_type);
		if(trim($cache_type) == '') return false;
		
        /** import libraries **/
        _wpl_import('libraries.property');
        _wpl_import('libraries.items');
        
        if($cache_type == 'unfinalized_properties' or $cache_type == 'all')
		{
			$query = "DELETE FROM `#__wpl_properties` WHERE `finalized`='0'";
			wpl_db::q($query);
		}
        
		if($cache_type == 'properties_cached_data' or $cache_type == 'all')
		{
			$query = "UPDATE `#__wpl_properties` SET `location_text`='', `rendered`=''";
			wpl_db::q($query);
		}
        
        if($cache_type == 'location_texts' or $cache_type == 'all')
		{
			$query = "UPDATE `#__wpl_properties` SET `location_text`=''";
			wpl_db::q($query);
		}
        
        if($cache_type == 'listings_thumbnails' or $cache_type == 'all')
		{
			$properties = wpl_db::select("SELECT `id`, `kind` FROM `#__wpl_properties` WHERE `id`>0", 'loadAssocList');
            $ext_array = array('jpg', 'jpeg', 'gif', 'png');
            
            foreach($properties as $property)
            {
                $path = wpl_items::get_path($property['id'], $property['kind']);
                $thumbnails = wpl_folder::files($path, 'th.*\.('.implode('|', $ext_array).')$', 3, true);
                
                foreach($thumbnails as $thumbnail)
                {
                    wpl_file::delete($thumbnail);
                }
            }
		}
        
        if($cache_type == 'users_thumbnails' or $cache_type == 'all')
		{
			$users = wpl_db::select("SELECT `id` FROM `#__wpl_users` WHERE `id`>0", 'loadAssocList');
            $ext_array = array('jpg', 'jpeg', 'gif', 'png');
            
            foreach($users as $user)
            {
                $path = wpl_items::get_path($user['id'], 2);
                $thumbnails = wpl_folder::files($path, 'th.*\.('.implode('|', $ext_array).')$', 3, true);
                
                foreach($thumbnails as $thumbnail)
                {
                    wpl_file::delete($thumbnail);
                }
            }
		}
		
        /** trigger event **/
        wpl_global::event_handler('cache_cleared', array('cache_type'=>$cache_type));
        
		return true;
	}
}