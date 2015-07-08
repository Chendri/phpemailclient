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
   public function read_message($uid){
      $client = open_stream();
      return fetch_message($client,$uid); 
   }
}
