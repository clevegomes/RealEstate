<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

_wpl_import('libraries.flex');
_wpl_import('libraries.property');

class wpl_listing_controller extends wpl_controller
{
	var $tpl_path = 'views.backend.listing.tmpl';
	var $tpl;
	
	public function wizard($instance = array())
	{
        /** load assets **/
        $this->load_assets();
        
        /** check access **/
		if(!wpl_users::check_access('propertywizard'))
		{
			/** import message tpl **/
			$this->message = __("You don't have access to this part!", WPL_TEXTDOMAIN);
			return parent::render($this->tpl_path, 'message');
		}
		
		$this->kind = trim(wpl_request::getVar('sf_select_kind')) != '' ? wpl_request::getVar('sf_select_kind') : 0;
		
		$this->field_categories = wpl_flex::get_categories(0, $this->kind);
		$this->kind_label = wpl_flex::get_kind_label($this->kind);
		
		$this->property_id = trim(wpl_request::getVar('pid')) != '' ? wpl_request::getVar('pid') : 0;
		$this->mode = $this->property_id ? 'edit' : 'add';
		
		if($this->mode == 'add')
		{
			/** checking access **/
			if(!wpl_users::check_access($this->mode))
			{
				$this->message = __("Limit reached. You Can't add more property!", WPL_TEXTDOMAIN);
				return parent::render($this->tpl_path, 'message');
			}
			
			/** generate new property **/
			$this->property_id = wpl_property::create_property_default('', $this->kind);
		}
		
		$this->values = wpl_property::get_property_raw_data($this->property_id);
		$this->finalized = isset($this->values['finalized']) ? $this->values['finalized'] : 0;
		
		if($this->mode == 'edit')
		{
			if(!$this->values)
			{
				$this->message = __("Property not exists!", WPL_TEXTDOMAIN);
				return parent::render($this->tpl_path, 'message');
			}
			
			/** checking access **/
			if(!wpl_users::check_access($this->mode, $this->values['user_id']))
			{
				$this->message = __("You Can't edit this property!", WPL_TEXTDOMAIN);
				return parent::render($this->tpl_path, 'message');
			}
		}
		
		/** import tpl **/
		$this->tpl = 'wizard';
		parent::render($this->tpl_path, $this->tpl);
	}
	
	protected function generate_slide($category)
	{
		$tpl = 'internal_slide';
		
		$this->fields = wpl_property::get_pwizard_fields($category->id, $this->kind, 1);
		$this->field_category = $category;
		
		/** import tpl **/
		parent::render($this->tpl_path, $tpl);
	}
    
    protected function load_assets()
	{
		/** add scripts and style sheet for uploaders **/
        $style = array();
        $style[] = (object) array('param1' => 'ajax-fileupload-style', 'param2' => 'js/ajax_uploader/css/style.css');
        $style[] = (object) array('param1' => 'ajax-fileupload-ui', 'param2' => 'js/ajax_uploader/css/jquery.fileupload-ui.css');
        foreach($style as $css) wpl_extensions::import_style($css);

        $scripts = array();
        $scripts[] = (object) array('param1' => 'jquery_file_upload', 'param2' => 'js/ajax_uploader/jquery.fileupload.js');
        foreach($scripts as $script) wpl_extensions::import_javascript($script);
	}
}