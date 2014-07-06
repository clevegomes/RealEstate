<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

/* WPL WIDGET - 21/07/2013 */
class wpl_widget extends WP_Widget
{
	var $data;
	
	function __construct($widget_id = null, $widget_name = '')
	{
		parent::__construct($widget_id, $widget_name);
	}
    
	/*
	 * Get The List of Layouts in the Widget
	 */
	public function get_layouts($widget_name)
	{
		$path = WPL_ABSPATH. 'widgets' .DS. $widget_name .DS. 'tmpl';
		$layouts = wpl_folder::files($path, '.php', false, false);
		return $layouts;
	}
	
	/*
	 * List the layouts in <option> fields
	 */
	public function generate_layouts_selectbox($widget_name, $instance)
	{
		// Base Layouts
		$layouts = self::get_layouts($widget_name);
		$i = 0;
		$data = '';
        
		while($i < count($layouts))
		{
			$data .= '<option ';
			if(str_replace('.php', '', $layouts[$i]) == $instance['layout']) $data .= 'selected="selected" ';
			$data .= 'value="'.str_replace('.php', '', $layouts[$i]).'"';
			$data .= '>';
			$data .= str_replace('.php', '', $layouts[$i]);
			$data .= '</option>';
		    $i++;
		}
		
		return $data;
	}
    
    /*
	 * List the layouts in <option> fields
	 */
	public function generate_pages_selectbox($instance, $field_name = 'wpltarget')
	{
        $pages = wpl_global::get_wp_pages();
        $data = '';
        
        foreach($pages as $page)
        {
            $data .= '<option ';
			if(isset($instance[$field_name]) and $page->ID == $instance[$field_name]) $data .= 'selected="selected" ';
			$data .= 'value="'.$page->ID.'"';
			$data .= '>';
			$data .= substr($page->post_title, 0, 100);
			$data .= '</option>';
        }
		
		return $data;
	}
	
	/*
	 * Load Registered Widget with Shortcode
	 */
	public function load_widget_instance($atts)
	{
        if(!wpl_global::check_addon('pro'))
        {
            return __('Pro addon must be installed for this!', WPL_TEXTDOMAIN);
        }
        
		extract(shortcode_atts(array('id'=>''), $atts));
        
	    ob_start();
		wpl_widget::widget_instance($id);
		$output = ob_get_contents();
	    ob_end_clean();
        
	    return $output;
    }

	public function widget_instance($widget_id) 
	{
        $wp_registered_widgets = self::get_registered_widgets();

	    // validation
	    if(!array_key_exists($widget_id, $wp_registered_widgets))
		{
			echo 'No widget found with id = '.$widget_id; 
			return;
	    }
		
		$params = array_merge(array(array_merge(array('widget_id' => $widget_id,
		'widget_name' => $wp_registered_widgets[$widget_id]['name']))), (array) $wp_registered_widgets[$widget_id]['params']);
  
	    $callback = $wp_registered_widgets[$widget_id]['callback'];
		
		if(is_callable($callback))
		{
		    call_user_func_array($callback, $params);
	    }
	}
	
	/*
	 * Get Widgets Instance For Listing in Shortcode Wizard
	 */
	public static function get_existing_widgets()
	{
		$wp_registered_widgets = self::get_registered_widgets();
        
	    $sidebar_widgets = wp_get_sidebars_widgets();
	    $widgets_with_title = array();
	
	    foreach($sidebar_widgets as $sidebar => $widgets)
		{
		    $widgets_with_title[$sidebar] = array();
		
		    foreach($widgets as $widget_id)
			{
		        array_push($widgets_with_title[$sidebar], array('id'=>$widget_id));
	        }
        }
		
		return $widgets_with_title;
	}
    
    /*
	 * Get registered widgets
	 */
	public function get_registered_widgets()
	{
		global $wp_registered_widgets;
        return $wp_registered_widgets;
	}
}