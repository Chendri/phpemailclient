<?php
class Email extends MY_Controller{
   public function __construct(){
      parent::__construct();
      $this->load->model('email_model');
      $this->load->helper('url');
      $this->load->helper('form');
   }

   public function index(){
      $data['emails'] = $this->email_model->fetch_inbox();
      $this->load->view('email/inbox', $data);
   }

   public function write_message()
   {
      $this->load->view('email/write_message');
   }

   public function send_message()
   {
      $message = $this->input->post("message");
      $to      = $this->input->post("recipient");
      $subject = $this->input->post("subject");
      if($this->email_model->send_message($to, $subject, $message))
      {
         $this->load->view('email/write_message');
      }
      else
      {
         //TODO generate a proper error
         exit("something went wrong");
      }
   }

   public function read_message($uid){
      $data = $this->email_model->read_message($uid);
      $this->load->view('email/read_message', $data);
   }
}
