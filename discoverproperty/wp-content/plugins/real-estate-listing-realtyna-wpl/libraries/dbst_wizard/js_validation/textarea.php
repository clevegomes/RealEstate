<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

if(in_array($field->mandatory, array(1, 2)))
{
	$js_string .=
	'
	if(wplj.trim(wplj("#wpl_c_'.$field->id.'").val()) == "" && wplj("#wpl_listing_field_container'.$field->id.'").css("display") != "none") 
	{
		wpl_alert("'.__('Enter a valid', WPL_TEXTDOMAIN).' '.__($field->name, WPL_TEXTDOMAIN).'!");
		return false;
	}
	';
}