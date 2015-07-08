<?php
class Email_model extends CI_Model{
   private $client = NULL;
   public function __construct(){
      $this->load->helper('email');
   }
   public function open_stream(){
         return open_stream();
   }
   public function fetch_inbox(){
      $client = open_stream();
      return fetch_inbox($client);
   }
   public function read_message($msgno){
      $client = open_stream();
      return fetch_message($client,$msgno); 
   }
   public function send_message($to, $subject, $content)
   {
      $file_path = process_attachments();

      $results = compose_message($to, $subject, $content, $file_path); 

      $message = $results['message'];
      $headers = $results['headers'];

      return(mail($to, $subject, $message, $headers));
   }
}
