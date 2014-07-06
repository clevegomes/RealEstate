<?php
/** no direct access **/
defined('_WPLEXEC') or die('Restricted access');

class wpl_wpl_controller extends wpl_controller
{
	var $tpl_path = 'views.backend.wpl.tmpl';
	var $tpl;
	
	public function display()
	{
		/** check permission **/
		wpl_global::min_access('administrator');
		$function = wpl_request::getVar('wpl_function');
		
		if($function == 'install_package') self::install_package();
		elseif($function == 'check_addon_update') self::check_addon_update();
		elseif($function == 'update_package') self::update_package();
		elseif($function == 'save_realtyna_credentials') self::save_realtyna_credentials();
	}
	
	private function install_package()
	{
		/** upload file into tmp directory **/
		$file = wpl_request::getVar('wpl_addon_file', '', 'FILES');
		$tmp_directory = wpl_global::init_tmp_folder();
		$dest = $tmp_directory.'package.zip';
		
		$response = wpl_global::upload($file, $dest, array('zip'), 20971520); #20MB
		if(trim($response['error']) != '') self::response($response);
		
		$zip_file = $dest;
		wpl_global::zip_extract($zip_file, $tmp_directory);
		
		$script_file = $tmp_directory.'installer.php';
		if(!wpl_file::exists($script_file)) self::response(array('error'=>__("Installer file doesn't exist!", WPL_TEXTDOMAIN), 'message'=>''));
		
		/** including installer and run the install method **/
		include $script_file;
		if(!class_exists('wpl_installer')) self::response(array('error'=>__("Installer class doesn't exist!", WPL_TEXTDOMAIN), 'message'=>''));
		
		/** run install script **/
		$wpl_installer = new wpl_installer();
		$wpl_installer->path = $tmp_directory;
		
		if(!$wpl_installer->run()) self::response(array('error'=>$wpl_installer->error, 'message'=>''));
		
		$message = $wpl_installer->message ? $wpl_installer->message : __('Package installed.', WPL_TEXTDOMAIN);
		self::response(array('error'=>'', 'message'=>$message));
	}
	
	private function check_addon_update()
	{
		$addon_id = wpl_request::getVar('addon_id');
		$response = wpl_global::check_addon_update($addon_id);
		
		self::response($response);
	}
	
	private function update_package()
	{
		$sid = wpl_request::getVar('sid');
		
		$tmp_directory = wpl_global::init_tmp_folder();
		$dest = $tmp_directory.'package.zip';
		
		$zip_file = wpl_global::get_web_page('http://billing.realtyna.com/index.php?option=com_rls&view=downloadables&task=download&sid='.$sid.'&randomkey='.rand(1, 100));
		
		if(!$zip_file) self::response(array('success'=>'0', 'message'=>__('Error: #U202, Could not download the update package!', WPL_TEXTDOMAIN)));
		if(!wpl_file::write($dest, $zip_file)) self::response(array('success'=>'0', 'message'=>__('Error: #U203, Could not create the update file!', WPL_TEXTDOMAIN)));
		if(!wpl_global::zip_extract($dest, $tmp_directory)) self::response(array('success'=>'0', 'message'=>__('Error: #U204, Could not extract the update file!', WPL_TEXTDOMAIN)));
		
		$script_file = $tmp_directory.'installer.php';
		if(!wpl_file::exists($script_file)) self::response(array('error'=>__("Installer file doesn't exist!", WPL_TEXTDOMAIN), 'message'=>''));
		
		/** including installer and run the install method **/
		include $script_file;
		if(!class_exists('wpl_installer')) self::response(array('error'=>__("Installer class doesn't exist!", WPL_TEXTDOMAIN), 'message'=>''));
		
		/** run install script **/
		$wpl_installer = new wpl_installer();
		$wpl_installer->path = $tmp_directory;
		
		if(!$wpl_installer->run()) self::response(array('error'=>$wpl_installer->error, 'message'=>''));
		
		$message = $wpl_installer->message ? $wpl_installer->message : __('Addon Updated.', WPL_TEXTDOMAIN);
		self::response(array('error'=>'', 'message'=>$message));
	}
	
	private function save_realtyna_credentials()
	{
		/** import settings library **/
		_wpl_import('libraries.settings');
		
		$username = wpl_request::getVar('username');
		$password = wpl_request::getVar('password');
		
		wpl_settings::save_setting('realtyna_username', $username, 1);
		wpl_settings::save_setting('realtyna_password', $password, 1);
		$response = wpl_global::check_realtyna_credentials();
		
		self::response($response);
	}
	
	private function response($response)
	{
		echo json_encode($response);
		exit;
	}
}