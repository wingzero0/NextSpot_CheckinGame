<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(dirname(__FILE__)."/FB_info.php");

class Facebook_Handshaking extends CI_Model{
	private $facebook = NULL;
	private $permission = null;
	public function __construct()
	{
		// Call the Model constructor
		parent::__construct();
		$this->facebook = FB_creater::create_FB();
		$this->permission = array('scope' => 'publish_checkins,publish_stream,read_stream');
	}
	public function get_login_url(){
		try{
			$url = $this->facebook->getLoginUrl(
				$this->permission
			);
			return $url;
		} catch (FacebookApiException $e) {
			echo $result = $e->getMessage();
			error_log($e);
			return null;
		}
	}
	public function get_logout_url(){
		try{
			$url = $this->facebook->getLogoutUrl();
			return $url;
		} catch (FacebookApiException $e) {
			echo $result = $e->getMessage();
			error_log($e);
			return null;
		}
	}
	public function is_login(){
		try{
			$f = FB_creater::create_FB();
			$user = $f->getUser();
			if (!$user){
				//$loginUrl = $f->getLoginUrl();
				//$result = sprintf("<a href=%s>login</a>",$loginUrl);
				return false;
			}else{
				// this is a hook to force error occur if the browser session 
				// miss detection of the user logout
				$user_profile = $f->api('/me');
				$logoutUrl = $f->getLogoutUrl();
				$result["url"] = sprintf("<a href=%s>logout</a>",$logoutUrl);
				$result["profile"] = $user_profile;
				return true;
			}
		} catch (FacebookApiException $e) {
			echo $result = $e->getMessage();
			error_log($e);
			return false;
		}

	}
}
