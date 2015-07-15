<?php
class Email_model extends CI_Model{
   public function __construct(){
      $this->load->helper('email');
      $this->load->library('session');
   }
   public function open_stream(){
      if(!isset($this->session->all_client))
      {
         $this->session->all_client        = open_all();
      }
      if(!isset($this->session->inbox_client))
      {
         $this->session->inbox_client      = open_inbox();
      }
   }

   public function fetch_inbox(){
      $this->open_stream();
      $client = $this->session->inbox_client;
      return fetch_inbox($client);
   }

   public function read_message($uid){
      $this->open_stream();

      $client = $this->session->all_client;

      $header              = imap_fetch_overview($client, $uid, FT_UID)[0];
      $data['body']         = $this->retrieve_message($uid, $client);

      $data['reply_chain']  = check_conversation($header, $client);

      return $data; 
   }
   public function retrieve_message($uid, $client=null){
      $this->open_stream();

      if(is_null($client))
      {
         $client = $this->session->all_client;
      }

      return fetch_message($client,$uid);
   }
   public function send_message($to, $subject, $content)
   {
      $this->open_stream();

      $file_path = process_attachments();

      $results = compose_message($to, $subject, $content, $file_path); 

      $message = $results['message'];
      $headers = $results['headers'];

      return(mail($to, $subject, $message, $headers));
   }
   public function send_reply($to, $subject, $content, $reply_id)
   {
      $this->open_stream();

      $file_path = process_attachments();

      $results = compose_message($to, $subject, $content, $file_path, $reply_id); 

      $message = $results['message'];
      $headers = $results['headers'];

      return(mail($to, $subject, $message, $headers));
   }

   public function delete_messages($checked_messages)
   {
      $this->open_stream();

      delete_messages(open_inbox(), $checked_messages);
   }

   public function get_debug_info($id)
   {
      $this->open_stream();

      $client = $this->session->all_client;

      return debug_info($client, $id);
   }
}
