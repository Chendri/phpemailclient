<?php
class Email_model extends CI_Model{
   public function __construct(){
      $this->load->helper('email');
      $this->load->library('session');
      $this->load->database();
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
      $search = '';
      $search = $this->session->search;
      return fetch_inbox($search);
   }

   public function read_message($msgid){

      $data['emails'] = get_conversation($msgid);
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
      $file_path = process_attachments();
      $mail = compose_message($to, $subject, $content, $file_path);

      if(!$mail->send()){
         exit("problemo");
      }else{
         return(true);
      }
   }

   public function send_reply($to, $subject, $content, $reply_id)
   {
      // $this->open_stream();

      $file_path = process_attachments();

      $mail = compose_message($to, $subject, $content, $file_path, $reply_id);

      if(!$mail->send()){
         exit("problemo");
      }else{
         return(true);
      }
   }

   public function delete_messages($checked_messages)
   {
      $this->open_stream();

      delete_messages($checked_messages);
   }

   public function search($search)
   {
      $this->session->search = $search;
   }

   public function fetch_groups()
   {
      $query = $this->db->get('email_tags');

      if($query->num_rows() > 0)
      {
         return $query->result();
      }
      else
      {
         return FALSE;
      }
   }
   public function new_tag($tag_name)
   {
      return $this->db->insert('email_tags', array('tag_name' => $tag_name));
   }

   public function add_to_group($checked_messages, $tag_name)
   {
      foreach($checked_messages as $access_id)
      {
         $result = perform_query('messages', array('access_id' => $access_id));
         if($result !== FALSE)
         {
            $result = perform_query('email_tags', array('tag_name' => $tag_name));
            if($result !== FALSE)
            {
               $tag = reset($result);
               $tag_id = $tag->id;
               $this->db->insert('email_tag_x', array('access_id' => $access_id, 'tag_id' => $tag_id));
            }
         }
      }
   }
   public function remove_from_group($checked_messages, $tag_name)
   {
      foreach($checked_messages as $access_id)
      {
         //TODO:Make the query more specific to removal, could be faster.
         $result = perform_query('messages', array('access_id' => $access_id));
         if($result !== FALSE)
         {
            $result = perform_query('email_tags', array('tag_name' => $tag_name));
            if($result !== FALSE)
            {
               $tag = reset($result);
               $tag_id = $tag->id;
               $this->db->where(array('access_id' => $access_id, 'tag_id' => $tag_id));
               $this->db->delete('email_tag_x');
            }
         }
      }
   }
   public function get_group_members($tag_name)
   {
      $query = $this->db->query("SELECT m.access_id FROM messages m INNER JOIN email_tag_x x ON m.access_id = x.access_id INNER JOIN email_tags t ON x.tag_id = t.id WHERE t.tag_name='$tag_name'");
      if($query->num_rows() > 0)
      {
         return $query->result();
      }
      else
      {
         return FALSE;
      }
   }
   public function new_count(){
      return $this->db->query('SELECT status FROM messages WHERE status=1')->num_rows();
   }
   public function check_messages()
   {
      check_messages();
   }
   public function debug_sql()
   {
      check_messages('ALL');
   }
   public function get_debug_info($id)
   {
      $this->open_stream();

      $client = $this->session->all_client;

      return debug_info($client, $id);
   }
}
