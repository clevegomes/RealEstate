<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/**
** Data Structure Library
** Developed 04/9/2013
**/

class wpl_property_types
{
	var $property_types;
	
	public static function remove_property_type($property_type_id)
	{
		$query = "DELETE FROM `#__wpl_property_types` WHERE `id`='$property_type_id'";
		$result = wpl_db::q($query);
		
        /** trigger event **/
		wpl_global::event_handler('property_type_removed', array('id'=>$property_type_id));
        
		return $result;	
	}
	
	/** Deprecated :: use wpl_global::get_property_types instead. **/
	public static function get_property_type($property_type_id)
	{
		return wpl_global::get_property_types($property_type_id);
	}
	
	public static function insert_property_type($parent,$name)
	{
		$query = "INSERT INTO `#__wpl_property_types`(`parent`, `enabled`, `editable`, `index`, `listing`, `name`) VALUE ('$parent', '1', '2', '00.00', '0', '$name')";
		$id = wpl_db::q($query, 'insert');
		$query = "UPDATE `#__wpl_property_types` SET `index`='$id.00' WHERE id=$id";
		wpl_db::q($query);
		return $id;
	}
	
	public static function sort_property_types($sort_ids)
	{
		$query = "SELECT `id`, `index` FROM `#__wpl_property_types` WHERE `id` IN ($sort_ids) ORDER BY `index` ASC";
		$property_types = wpl_db::select($query, 'loadAssocList');
		
		$conter = 0;
		$ex_sort_ids = explode(',', $sort_ids);
		
		foreach($ex_sort_ids as $ex_sort_id)
		{
			self::update($ex_sort_id, 'index', $property_types[$conter]['index']);
			$conter++;
		}
		
		return $conter;
	}
	
	public static function update($id, $key, $value = '')
	{
		/** first validation **/
		if(trim($id) == '' or trim($key) == '') return false;
		return wpl_db::set('wpl_property_types', $id, $key, $value);
	}
	
	/** Deprecated :: use wpl_global::get_property_types instead. **/
	public static function get_property_types()
	{
		return wpl_global::get_property_types('', 0);
	}
	
	public static function get_property_types_category()
	{
		$query = "SELECT * FROM `#__wpl_property_types` WHERE `parent` = '0' ORDER BY `index` ASC";
		return wpl_db::select($query, 'loadAssocList');
	}
	public static function have_properties($property_type_id)
	{
		$query = "SELECT count(`id`) as 'id' FROM `#__wpl_properties` WHERE `property_type` = '$property_type_id'";
		$res = wpl_db::select($query, 'loadAssocList');
		return $res[0]['id'];
	}
}