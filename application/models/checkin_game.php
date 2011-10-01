<?php
//if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(dirname(__FILE__)."/FB_info.php");

class Checkin_Game extends CI_Model{
	private $facebook = NULL;
	public function __construct(){
		//parent::__construct();
		$this->facebook = FB_creater::create_FB();
	}
	public function get_gps_page_info($latitude,$longitude,$checkin_radius,$question_radius){
		try{
			$query = sprintf("/search?type=place&center=%lf,%lf&distance=%d&limit=10",$latitude, $longitude, $checkin_radius);
			$place = $this->facebook->api($query);
			foreach ($place['data'] as $i => $v){
				$list["checkin_list"][$i]["page_id"] = $v["id"];
				$list["checkin_list"][$i]["page_name"] = $v["name"];
				$list["checkin_list"][$i]["latitude"] = $v["location"]["latitude"];
				$list["checkin_list"][$i]["longitude"] = $v["location"]["longitude"];
			}
			
			$query = sprintf("/search?type=place&center=%lf,%lf&distance=%d&limit=50",$latitude, $longitude, $question_radius);
			$place = $this->facebook->api($query);
			foreach ($place['data'] as $i => $v){
				$list["question_list"][$i]["page_id"] = $v["id"];
				$list["question_list"][$i]["page_name"] = $v["name"];
				$list["question_list"][$i]["latitude"] = $v["location"]["latitude"];
				$list["question_list"][$i]["longitude"] = $v["location"]["longitude"];
			}

			return $list;
		}catch (FacebookApiException $e) {
			echo $result = $e->getMessage();
			print_r($e);
			error_log($e);
			return null;
		}
	}
	public function checkin($checkin_page_id, $checkin_msg ,$question_page_ids){
		try{
			$page = $this->facebook->api(sprintf("/%d", $checkin_page_id));
			
			$message = "你們覺得我等一下應該去哪裏？\n";
			//$message = $checkin_msg;
			foreach ($question_page_ids as $i => $v){
				$pages[$i] = $this->facebook->api(sprintf("/%d", $v));
				$message = sprintf("%s　%d %s\n", $message,$i, $pages[$i]["name"]);
			}
			
			$me = $this->facebook->api("/me");

			$comment_hook = $me["id"]."_".time();
			$link_mag = "http://kit.csie.ntu.edu.tw/CheckinGame?id=".$comment_hook;

			$attachment = array(
				"message" => $checkin_msg."\n".$message.$link_mag,
				'coordinates' => '{"latitude":"'.$page["location"]["latitude"].'", "longitude": "'.$page["location"]["longitude"].'"}',
				"place" => $checkin_page_id
			);
			$checkin_obj = $this->facebook->api("/me/checkins", "post", $attachment);

			// save the page location
			$this->load->database();
			$this->location_page_db_insert($page);
			foreach ($pages as $i => $v){
				$this->location_page_db_insert($v);
			}

			// save the checkin info
			$insert_data = array(
				"checkin_id" => $checkin_obj["id"],
				"time" => date("Y-m-d H:i:s"),
				"user_id" => "0_".$me["id"],
				"page_id" => $checkin_page_id,
				//"latitude" => $page["location"]["latitude"],
				//"longitude" => $page["location"]["longitude"],
				"comment_hook" => $comment_hook
			);
			$this->db->insert("checkin",$insert_data);
				
			// save the options info
			foreach($pages as $i => $v){
				$insert_data = array(
					"checkin_id" => $checkin_obj["id"],
					"page_id" => $v["id"],
				);
				$this->db->insert("option",$insert_data);
			}
			return $checkin_obj;// should return true
		}catch (FacebookApiException $e) {
			echo $result = $e->getMessage();
			print_r($e);
			error_log($e);
			return false;
		}
	}
	private function location_page_db_insert($page){ // $page is a facebook page obj
		$query = $this->db->query("select `id` from `location_page` where `id` = '".$page["id"]."'");
		if ($query->num_rows() == 0){
			$insert_data = array(
				"id" => $page["id"],
				"name" => $page["name"],
				"latitude" => $page["location"]["latitude"],
				"longitude" => $page["location"]["longitude"]
			);
			$this->db->insert("location_page", $insert_data);
		}
	}
	public function get_options_with_hook($comment_hook){
		$this->load->database();
		$sql = sprintf("
			SELECT  `option`.`checkin_id` , `location_page`.`id`, `location_page`.`name`,`location_page`.`latitude`, `location_page`.`longitude`
			FROM  `option` 
			LEFT JOIN  `location_page` ON  `option`.`page_id` =  `location_page`.`id` 
			WHERE  `option`.`checkin_id` 
			IN (
				SELECT  `checkin_id` 
				FROM  `checkin` 
				WHERE  `comment_hook` =  '%s'
			)", $comment_hook);
		$query = $this->db->query($sql);
		foreach ($query->result_array() as $i => $v){
			$result["checkin_id"] = $v["checkin_id"];
			//$result["option"][$i]["page_id"] = $v["id"];
			$result["option_name"][$i] = $v["name"];
			//$result["option"][$i]["latitude"] = $v["latitude"];
			//$result["option"][$i]["longitude"] = $v["longitude"];
		}
		return $result;
	}
	public function checkin_comment($checkin_id, $message){
		try{
			$attachment = array(
				"message" => $message
			);
			$comment_obj = $this->facebook->api("/$checkin_id/comments/", "post", $attachment);

			return true;
		}catch (FacebookApiException $e) {
			echo $result = $e->getMessage();
			print_r($e);
			error_log($e);
			return false;
		}
	}
	public function last_question(){
		$this->load->database();
		$me = $this->facebook->api("/me");
		$user_id = "0_".$me["id"];
		$sql = sprintf("
			SELECT  `checkin_id` ,  `time` ,  `location_page`.`name` ,  `location_page`.`latitude` ,  `location_page`.`longitude` 
			FROM  `checkin` 
			LEFT JOIN  `location_page` ON  `checkin`.`page_id` =  `location_page`.`id`
			WHERE  `checkin`.`user_id` =  '%s'
			AND  `checkin`.`answer_bit` =1
			ORDER BY  `time` DESC 
			LIMIT 1
			", $user_id);
		$query = $this->db->query($sql);
		if ($query->num_rows == 0){
			return NULL;
		}else if ($query->num_rows <0 ){
			echo "db error, num_rows < 0\n";
			return NULL;
		}
		
		$result["last_checkin"] = $query->row_array();
		$sql = sprintf("
			SELECT  `o`.`row_id` , `o`.`page_id`, `l`.`name` ,  `l`.`latitude` ,  `l`.`longitude` 
			FROM  `option` AS  `o` 
			LEFT JOIN  `location_page` AS  `l` ON  `o`.`page_id` =  `l`.`id` 
			WHERE  `o`.`checkin_id` =  '%s'
			", $result["last_checkin"]["checkin_id"]);
		$query = $this->db->query($sql);
		if ($query->num_rows == 0){
			return NULL;
		}else if ($query->num_rows <0 ){
			echo "db error, num_rows < 0\n";
			return NULL;
		}
		$result["last_options"] = $query->result_array();
		return $result;	
	}
	public function answer_question($checkin_id, $option_row_id, $name){
		try{
			//echo $option_row_id."\n";
			if ($option_row_id != 0 && $option_row_id != "0"){
				$attachment = array(
					"message" => "我最後去了".$name
				);
				$comment_obj = $this->facebook->api("/$checkin_id/comments/", "post", $attachment);
				$this->load->database();
				$sql = sprintf("
					update `option` set `final_answer` = 1
					where `row_id` = '%d'
					", $option_row_id);
				$query = $this->db->query($sql);
			}

			$sql = sprintf("
				update `checkin` set `answer_bit` = 0
				where `checkin_id` = '%s'
				", $checkin_id);
			//echo $sql;
			$query = $this->db->query($sql);

			return $comment_obj;// should return true
		}catch (FacebookApiException $e) {
			echo $result = $e->getMessage();
			print_r($e);
			error_log($e);
			return false;
		}

	}

}

?>
