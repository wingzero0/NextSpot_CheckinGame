<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	
	function model_test(){
		echo "<pre>\n";

		$this->load->model('Facebook_Handshaking');
		$ret = $this->Facebook_Handshaking->is_login();
		if ($ret === true){
			$href = sprintf("<a href='%s'>logout from fb</a>", $this->Facebook_Handshaking->get_logout_url());

			$this->load->model('Checkin_Game');
			$ret = $this->Checkin_Game->get_gps_page_info(25.019895, 121.541448, 100, 50000);
			//print_r($ret);
			for ($i = 0;$i < 4 && $i < count($ret["question_list"]);$i++){
				$q_list_id[$i] = $ret["question_list"][$i]["page_id"];
			}
			$ret = $this->Checkin_Game->checkin($ret["checkin_list"][0]["page_id"],"我在做封測，不要給我亂點",$q_list_id);
			print_r($ret);

		}else{
			$href = sprintf("<a href='%s'>login with fb</a>", $this->Facebook_Handshaking->get_login_url());
		}
		print_r($href);
		echo "\nhaha\n";
		echo "</pre>";
	}
	function comment_test(){	
		echo "<pre>\n";
		$this->load->model('Facebook_Handshaking');
		$ret = $this->Facebook_Handshaking->is_login();
		if ($ret === true){
			$href = sprintf("<a href='%s'>logout from fb</a>", $this->Facebook_Handshaking->get_logout_url());

			$this->load->model('Checkin_Game');
			$ret = $this->Checkin_Game->get_options_with_hook("598924680_1313122722");
			print_r($ret);	
			$ret = $this->Checkin_Game->checkin_comment($ret["checkin_id"], "comment time test".date("Y-m-d H:i:s"));
			print_r($ret);
		}else{
			$href = sprintf("<a href='%s'>login with fb</a>", $this->Facebook_Handshaking->get_login_url());
		}
		print_r($href);
		echo "</pre>";
	}
	function answer_test(){	
		echo "<pre>\n";
		$this->load->model('Facebook_Handshaking');
		$ret = $this->Facebook_Handshaking->is_login();
		if ($ret === true){
			$href = sprintf("<a href='%s'>logout from fb</a>", $this->Facebook_Handshaking->get_logout_url());

			$this->load->model('Checkin_Game');
			$ret = $this->Checkin_Game->last_question();
			print_r($ret);
			if ($ret != NULL){
				$ret = $this->Checkin_Game->answer_question($ret["last_checkin"]["checkin_id"], $ret["last_options"][1]["row_id"],$ret["last_options"][1]["name"]);
				//$ret = $this->Checkin_Game->answer_question($ret["last_checkin"]["checkin_id"], 0, NULL);
				print_r($ret);
			}else{
				echo "no question available\n";
			}
		}else{
			$href = sprintf("<a href='%s'>login with fb</a>", $this->Facebook_Handshaking->get_login_url());
		}
		echo "</pre>";
	}



}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
?>
