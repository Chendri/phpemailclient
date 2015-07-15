<?php
class Email extends MY_Controller{
   public function __construct(){
      parent::__construct();
      $this->load->model('email_model');
      $this->load->helper('url');
      $this->load->helper('form');
   }

   public function index(){
      $this->email_model->open_stream();
      $data['emails'] = $this->email_model->fetch_inbox();
      $this->load->view('email/inbox', $data);
   }
   
   public function debug($id = 0){
      $data = $this->email_model->get_debug_info($id);

      $this->load->view('email/debug', $data);
   }
   public function write_message()
   {
      $this->load->view('email/write_message');
   }

   public function send_message()
   {
      putenv("TMPDIR=/tmp");

      //Set up form validation rules
      $this->load->library('form_validation');

      $this->form_validation->set_rules('message', 'Message', 'required');
      $this->form_validation->set_rules('recipient', 'Recipient', 'required|valid_emails');
      $this->form_validation->set_rules('subject', 'Subject', 'required');

      if($this->form_validation->run() == FALSE)
      {
         //Form validation failed, display errors
         $this->load->view('email/write_message');
      }
      else
      {
         //Form input checks out, get values and send them to the model
         $message = $this->input->post("message");
         $to      = $this->input->post("recipient");
         $subject = $this->input->post("subject");

         if($this->email_model->send_message($to, $subject, $message))
         {
            //Email was sent, return to message writer
            $this->load->view('email/write_message');
         }
         else
         {
            //TODO generate a proper error
            exit("something went wrong");
         }
      }
   }

   public function send_reply($uid)
   {
      putenv("TMPDIR=/tmp");//This is a hotfix to a problem I have with the mac set up.
      $this->load->library('form_validation');

      $this->form_validation->set_rules('message', 'Message', 'required');
      $this->form_validation->set_rules('recipient', 'Recipient', 'required|valid_emails');
      $this->form_validation->set_rules('subject', 'Subject', 'required');

      if($this->form_validation->run() == FALSE)
      {
         //Form validation failed, display errors
         $this->read_message($uid);
      }
      else
      {
         //Form input checks out, get values and send them to the model
         $message       = $this->input->post("message");
         $to            = $this->input->post("recipient");
         $subject       = $this->input->post("subject");
         $reply_id      = $this->input->post("in_reply_to");

         if($this->email_model->send_reply($to, $subject, $message, $reply_id))
         {
            //Email was sent, return to message writer
            $this->read_message($uid);
         }
         else
         {
            //TODO generate a proper error
            exit("something went wrong");
         }
      }
   }

   public function retrieve_message($msgno)
   {

      if(empty($msgno))
      {
         exit('no message no.');
      }

      exit(quoted_printable_decode($this->email_model->retrieve_message($msgno)));

   }
   public function read_message($uid){
      $data = $this->email_model->read_message($uid);
      $data['uid'] = $uid;
      $this->load->view('email/read_message', $data);
   }

   public function delete_messages(){
      $checked_messages = $this->input->post('checked_messages');
      if(!empty($checked_messages))
      {
         $this->email_model->delete_messages($checked_messages);
         exit("Messages deleted");
      }
      exit("No messages checked");
   }
}
