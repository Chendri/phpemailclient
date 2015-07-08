<?php
class Email extends MY_Controller{
   public function __construct(){
      parent::__construct();
      $this->load->model('email_model');
      $this->load->helper('url');
   }

   public function index(){
      $data['emails'] = $this->email_model->fetch_inbox();
      $this->load->view('email/inbox', $data);
   }
   public function read_message($uid){
      $data = $this->email_model->read_message($uid);
      $this->load->view('email/read_message', $data);
   }
}
