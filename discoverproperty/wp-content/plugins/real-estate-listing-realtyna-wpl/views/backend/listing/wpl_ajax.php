<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

_wpl_import('libraries.flex');
_wpl_import('libraries.property');
_wpl_import('libraries.locations');
_wpl_import('libraries.render');
_wpl_import('libraries.items');

class wpl_listing_controller extends wpl_controller
{
	var $tpl_path = 'views.backend.listing.tmpl';
	var $tpl;
	
	public function display()
	{
		/** check permission **/
		wpl_global::min_access('agent');
		
		$function = wpl_request::getVar('wpl_function');
		
		if($function == 'save')
		{
			$table_name = wpl_request::getVar('table_name');
			$table_column = wpl_request::getVar('table_column');
			$value = wpl_request::getVar('value');
			$item_id = wpl_request::getVar('item_id');
			
			self::save($table_name, $table_column, $value, $item_id);
		}
		elseif($function == 'location_save')
		{
			$table_name = wpl_request::getVar('table_name');
			$table_column = wpl_request::getVar('table_column');
			$value = wpl_request::getVar('value');
			$item_id = wpl_request::getVar('item_id');
			
			self::location_save($table_name, $table_column, $value, $item_id);
		}
		elseif($function == 'get_locations')
		{
			$location_level = wpl_request::getVar('location_level');
			$parent = wpl_request::getVar('parent');
			$current_location_id = wpl_request::getVar('current_location_id');
			
			self::get_locations($location_level, $parent, $current_location_id);
		}
		elseif($function == 'finalize')
		{
			$item_id = wpl_request::getVar('item_id');
			$mode = wpl_request::getVar('mode');
			$value = wpl_request::getVar('value', 1);
			
			self::finalize($item_id, $mode, $value);
		}
        elseif($function == 'item_save')
		{
			self::item_save();
		}
	}
	
	private function save($table_name, $table_column, $value, $item_id)
	{
		$field_type = wpl_global::get_db_field_type($table_name,$table_column);
		if ($field_type == 'datetime' || $field_type == 'date') $value = wpl_render::derender_date($value);

		$res = wpl_db::set($table_name, $item_id, $table_column, $value, 'id');
		
		$res = (int) $res;
		$message = $res ? __('Saved.', WPL_TEXTDOMAIN) : __('Error Occured.', WPL_TEXTDOMAIN);
		$data = NULL;
		
		$response = array('success'=>$res, 'message'=>$message, 'data'=>$data);
		
		echo json_encode($response);
		exit;
	}
	
	private function location_save($table_name, $table_column, $value, $item_id)
	{
		$location_settings = wpl_global::get_settings('3'); # location settings
		
		$location_level = str_replace('_id', '', $table_column);
		$location_level = substr($location_level, -1);
		
		if($table_column == 'zip_id') $location_level = 'zips';
		
		$location_data = wpl_locations::get_location($value, $location_level);
		$location_name_column = $location_level != 'zips' ? 'location'.$location_level.'_name' : 'zip_name';
		
		/** update property location data **/
		if($location_settings['location_method'] == 2 or ($location_settings['location_method'] == 1 and in_array($location_level, array(1, 2)))) $res = wpl_db::update($table_name, array($table_column=>$value, $location_name_column=>$location_data->name), 'id', $item_id);
		else $res = wpl_db::update($table_name, array($location_name_column=>$value), 'id', $item_id);
		
		$res = (int) $res;
		$message = $res ? __('Saved.', WPL_TEXTDOMAIN) : __('Error Occured.', WPL_TEXTDOMAIN);
		$data = NULL;
		
		$response = array('success'=>$res, 'message'=>$message, 'data'=>$data);
		
		echo json_encode($response);
		exit;
	}
	
	private function get_locations($location_level, $parent, $current_location_id = '')
	{
		$location_data = wpl_locations::get_locations($location_level, $parent, '');
		$location_settings = wpl_global::get_settings('3'); # location settings
		
		$res = count($location_data) ? 1 : 0;
		if(!is_numeric($parent)) $res = 1;
		
		$message = $res ? __('Fetched.', WPL_TEXTDOMAIN) : __('Error Occured.', WPL_TEXTDOMAIN);
		$data = $location_data;
		
		/** website is configured to use location text **/
		if($location_settings['location_method'] == 1 and ($location_level >= 3 or $location_level == 'zips'))
		{
			$html = '<input type="text" name="wpl_listing_location'.$location_level.'_select" id="wpl_listing_location'.$location_level.'_select" onchange="wpl_listing_location_change(\''.$location_level.'\', this.value);" />';
		}
		/** website is configured to use location database **/
		elseif($location_settings['location_method'] == 2 or ($location_settings['location_method'] == 1 and $location_level <= 2))
		{
			$html = '<select name="wpl_listing_location'.$location_level.'_select" id="wpl_listing_location'.$location_level.'_select" onchange="wpl_listing_location_change(\''.$location_level.'\', this.value);">';
			$html .= '<option value="0">'.__('Select', WPL_TEXTDOMAIN).'</option>';
			
			foreach($location_data as $location)
			{
				$html .= '<option value="'.$location->id.'" '.($current_location_id == $location->id ? 'selected="selected"' : '').'>'.$location->name.'</option>';
			}
			
			$html .= '</select>';
		}
		
		$response = array('success'=>$res, 'message'=>$message, 'data'=>$data, 'html'=>$html, 'keyword'=>__($location_settings['location'.$location_level.'_keyword'], WPL_TEXTDOMAIN));
		
		echo json_encode($response);
		exit;
	}
	
	private function finalize($item_id, $mode, $value = 1)
	{
		if($value) wpl_property::finalize($item_id, $mode);
		else wpl_property::unfinalize($item_id);
		
		$res = 1;
		$message = $res ? __('Saved.', WPL_TEXTDOMAIN) : __('Error Occured.', WPL_TEXTDOMAIN);
		$data = NULL;
		
		$response = array('success'=>$res, 'message'=>$message, 'data'=>$data);
		
		echo json_encode($response);
		exit;
	}
    
    private function item_save()
	{
		$kind = wpl_request::getVar('kind', 0);
		$parent_id = wpl_request::getVar('item_id', 0);
        $item_type = wpl_request::getVar('item_type', '');
        $item_cat = wpl_request::getVar('item_cat', '');
        $item_name = wpl_request::getVar('value', '');
        $item_extra1 = wpl_request::getVar('item_extra1', '');
        $item_extra2 = wpl_request::getVar('item_extra2', '');
        $item_extra3 = wpl_request::getVar('item_extra3', '');
        
        $query = "SELECT `id` FROM `#__wpl_items` WHERE `parent_kind`='$kind' AND `parent_id`='$parent_id' AND `item_type`='$item_type' AND `item_cat`='$item_cat'";
        $item_id = wpl_db::select($query, 'loadResult');
        
        $item = array('parent_id'=>$parent_id, 'parent_kind'=>$kind, 'item_type'=>$item_type, 'item_cat'=>$item_cat, 'item_name'=>$item_name, 
					  'creation_date'=>date("Y-m-d H:i:s"), 'index'=>'1.00', 'item_extra1'=>$item_extra1, 'item_extra2'=>$item_extra2, 'item_extra3'=>$item_extra3);
		
		wpl_items::save($item, $item_id);
        
		$res = 1;
		$message = $res ? __('Saved.', WPL_TEXTDOMAIN) : __('Error Occured.', WPL_TEXTDOMAIN);
		$data = NULL;
		
		$response = array('success'=>$res, 'message'=>$message, 'data'=>$data);
		
		echo json_encode($response);
		exit;
	}
}