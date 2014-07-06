<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

_wpl_import('libraries.pagination');
_wpl_import('libraries.render');
_wpl_import('libraries.items');
_wpl_import('libraries.activities');
_wpl_import('libraries.settings');

class wpl_profile_listing_controller extends wpl_controller
{
	var $tpl_path = 'views.frontend.profile_listing.tmpl';
	var $tpl;
	var $wpl_profiles;
	var $model;
	
	public function display($instance = array())
	{
		/** property listing model **/
		$this->model = new wpl_users;
		
		/** global settings **/
		$settings = wpl_settings::get_settings();
		
		/** listing settings **/
		$this->page_number = wpl_request::getVar('wplpage', 1, '', true);
		$limit = wpl_request::getVar('limit', $settings['default_profile_page_size'], '', true);
		$start = wpl_request::getVar('start', (($this->page_number-1)*$limit), '', true);
		$orderby = wpl_request::getVar('wplorderby', $settings['default_profile_orderby'], '', true);
		$order = wpl_request::getVar('wplorder', $settings['default_profile_order'], '', true);
		
		/** set page if start var passed **/
		$this->page_number = ($start/$limit)+1;
		wpl_request::setVar('wplpage', $this->page_number);
		
		/** detect kind **/
		$this->kind = wpl_request::getVar('kind', 2);
		if(!in_array($this->kind, wpl_flex::get_kinds()))
		{
			/** import message tpl **/
			$this->message = __('Invalid Request!', WPL_TEXTDOMAIN);
			return parent::render($this->tpl_path, 'message', false, true);
		}
		
		$where = array('sf_tmin_id'=>1, 'sf_select_access_public_profile'=>1);
		
		/** start search **/
		$this->model->start($start, $limit, $orderby, $order, $where);
		$this->model->total = $this->model->get_users_count();
		
		/** validation for page_number **/
		$max_page = ceil($this->model->total / $limit);
		if($this->page_number <= 0 or ($this->page_number > $max_page)) $this->model->start = 0;
		
		/** run the search **/
		$query = $this->model->query();
		$profiles = $this->model->search();
		
		/** finish search **/
		$this->model->finish();
		$plisting_fields = $this->model->get_plisting_fields();
		
		$wpl_profiles = array();
		foreach($profiles as $profile)
		{
			$wpl_profiles[$profile->id] = $this->model->full_render($profile->id, $plisting_fields);
		}
		
		/** define current index **/
		$wpl_profiles['current'] = array();
		
		/** apply filters (This filter must place after all proccess) **/
		_wpl_import('libraries.filters');
		@extract(wpl_filters::apply('profile_listing_after_render', array('wpl_profiles'=>$wpl_profiles)));
		
		$this->pagination = wpl_pagination::get_pagination($this->model->total, $limit, true);
		$this->wpl_profiles = $wpl_profiles;
		
		/** import tpl **/
		return parent::render($this->tpl_path, $this->tpl, false, true);
	}
}