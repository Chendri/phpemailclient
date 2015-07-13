<?php
class Email_model extends CI_Model{
   public function __construct(){
      $this->load->helper('email');
   }
   public function open_stream(){
         return open_inbox();
   }
   public function fetch_inbox(){
      $client = open_inbox();
      return fetch_inbox($client);
   }
   public function read_message($msgno){
      $client = open_inbox();
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

   public function delete_messages($checked_messages)
   {
      delete_messages(open_inbox(), $checked_messages);
   }

   public function get_debug_info($id)
   {
      $client = open_inbox();
      return debug_info($client, $id);
   }
}
