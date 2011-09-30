<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Checkin extends CI_Controller {

	public function __construct()
   {
		parent::__construct();
		// Your own constructor code
		//$this->load->model('CheckinGame');
   }
	public function index()
	{
		
	}
	
	function get_last_question()
	{
		//fake data
		$data['question_id'] = 1;
		$data['last_location'] = array(
			'x'   => 25,
			'y'   => 32,
			'name'=>'台大資工系'
		
		);
		$data['last_question'] = 
		array
		(
			array('option_id'=>1,'x'=>1,'y'=>2,'name'=>'科技大樓'),
			array('option_id'=>2,'x'=>1,'y'=>2,'name'=>'辛亥隧道'),
			array('option_id'=>3,'x'=>1,'y'=>2,'name'=>'公館捷運站'),
			array('option_id'=>4,'x'=>1,'y'=>2,'name'=>'臺師大')
		
		);
		//$this->CheckinGame->get_last_question();
		$data['result'] = true;
		echo json_encode($data);
		exit(1);
		
	
	}
	
	function answer_question()
	{
		$question_id = $this->input->post('question_id');
		$option_id = $this->input->post('option_id');
		//$this->CheckinGame->answer_question($question_id,$option_id);
		$data['result'] = true;
		echo json_encode($data);
		exit(1);
	}
	function get_gps_inf()
	{
		$x = $this->input->post('x');
		$y = $this->input->post('y');
		$checkin_radius = 100;
		$quection_radius = 10000;
		//fake data
		$data['checkin_list'] = 
		array
		(
			array('page_id'=>1,'x'=>1,'y'=>2,'name'=>'資訊大樓'),
			array('page_id'=>2,'x'=>1,'y'=>2,'name'=>'德田館'),
			array('page_id'=>3,'x'=>1,'y'=>2,'name'=>'電機館'),
			array('page_id'=>4,'x'=>1,'y'=>2,'name'=>'應力館')		
		);
		$data['question_list'] = 
		array
		(
			array('page_id'=>1,'x'=>1,'y'=>2,'name'=>'科技大樓'),
			array('page_id'=>2,'x'=>1,'y'=>2,'name'=>'辛亥隧道'),
			array('page_id'=>3,'x'=>1,'y'=>2,'name'=>'公館捷運站'),
			array('page_id'=>4,'x'=>1,'y'=>2,'name'=>'臺師大')		
		);		
		//$this->CheckinGame->get_gps_inf($x,$y,$checkin_radius,$quection_radius);
		$data['result'] = true;
		echo json_encode($data);
		exit(1);
		
		
	}
	
	function checkin()
	{
		$checkin_page_id = $this->input->post('checkin_page_id');		
		$msg= $this->input->post('msg');
		$questionIDList = $this->input->post('questionIDList');
		$question_page_id = split(',',$questionIDList);
		$num = count($question_page_id );
		if($checkin_page_id==0) $errno = 1;//for unexecepted err
		else if($num<2) $errno = 2;//for un enough option
		//$this->CheckinGame->checkin($checkin_page_id,$msg,$question_page_id);
		if($errno==0)$data['result'] = true;
		else $data['result'] = false;
		echo json_encode($data);
		exit(1);
			
		
	}
	
}

/* End of file checkin.php */
/* Location: ./application/controllers/checkin.php */
?>
