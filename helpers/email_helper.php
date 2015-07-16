<?php
require 'application/third_party/EmailMessage.php';
//TODO Have the email client read off of some config file only reachable by the server
if(!function_exists("get_login_info"))
{
   function get_login_info()
   {
      $data['username'] = 'example@gmail.com';
      $data['password'] = 'example';

      return $data;
   }
}

if(!function_exists("open_inbox"))
{
   function open_inbox($username = '', $password='')
   {
      //Having some certification problem, going to deal with it later.
      $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
      $data = get_login_info();
      return imap_open($hostname, $data['username'], $data['password']);
   }
}

if(!function_exists("open_sent_folder"))
{
   function open_sent_folder()
   {
      $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}[Gmail]/Sent Mail';

      $data = get_login_info();
      return imap_open($hostname, $data['username'], $data['password']);
   }
}
if(!function_exists('open_all'))
{
   function open_all()
   {
      $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}[Gmail]/All Mail';
      $data = get_login_info();
      return imap_open($hostname, $data['username'], $data['password']);
   }
}

if(!function_exists('perform_query'))
{
   function perform_query($table, $lookup = '')
   {
      $CG = get_instance();
      if(!empty($lookup) && is_array($lookup))
      {
         $CG->db->where($lookup);
      }
      $query = $CG->db->get($table);
      if($query->num_rows() > 0)
      {
         return $query->result();
      }
      else
      {
         return FALSE;
      }
   }
}
if(!function_exists('search'))
{
   function search($search)
   {
      //Builds a basic search using from, to, subject, and tag fields
      if(gettype($search) != 'string')
      {
         exit("Search needs to be a string");
      }

      $CG = get_instance();
      $CG->db->like('from', $search);
      $CG->db->or_like('to', $search);
      $CG->db->or_like('subject', $search);
      $CG->db->or_like('email_tags.tag_name', $search);
      $CG->db->join('email_tag_x', 'email_tag_x.access_id = messages.access_id');
      $CG->db->join('email_tags', 'email_tags.id = email_tag_x.tag_id');
   }
}
if(!function_exists('check_messages'))
{
   function check_messages($options = "UNSEEN"){
      $client = open_all();
      $CG = get_instance();

      $mailcheck = imap_check($client);

      $srch = imap_search($client, $options);

      if($srch !== FALSE)
      {
         $emails = imap_fetch_overview($client, implode(',', $srch),0);

         foreach($emails as $email)
         {
            $message_id = $email->message_id;
            $check_query = $CG->db->get_where('messages', array('message_id' => $message_id));
            if($check_query->num_rows() == 0)
            {
               $uid = $email->uid;
               $body = fetch_message($client, $uid);
               add_to_inbox($client, $email, $body);
            }
         }
      }
   }
}

if(!function_exists('add_to_inbox'))
{
   function add_to_inbox($client, $header, $body)
   {
      $CG = get_instance();

      $data = array(
         'uid'          => $header->uid,
         'message_id'   => $header->message_id,
         'body'         => quoted_printable_decode($body),
         'subject'      => $header->subject,
         'date'         => date("Y-m-d H:i:s",strtotime($header->date)),
         'from'         => $header->from,
         'to'           => $header->to
      );
      $to_fix = array();
      if(isset($header->in_reply_to))
      {
         $parent = $header->in_reply_to;
         while(true){
            $query = $CG->db->get_where('messages', array('message_id' => $parent));
            if($query->num_rows() > 0)
            {
               if(!is_null($query->row()->parent))
               {
                  $parent = $query->row()->parent;

                  $query = $CG->db->get_where('messages', array('message_id' => $parent));
                  if($query->num_rows() > 0)
                  {
                     if(!is_null($query->row()->parent))
                     {
                        $to_fix[] = $query->row()->message_id;
                     }
                  }
               }
               else{
                  break;
               }
            }
            else{
               break;
            }
         }

         foreach($to_fix as $msgid)
         {
            $CG->db->set('parent', $parent);
            $CG->db->where('message_id', $msgid);
            $CG->db->update('messages');
         }

         $data['parent'] = $parent;
      }

      $CG->db->insert('messages', $data);
      $id = $CG->db->insert_id();

      $CG->db->insert('email_tag_x', array('access_id' => $id, 'tag_id' => 1));
   }
}

if(!function_exists("fetch_inbox"))
{
   function fetch_inbox($search='')
   {
      $CG = get_instance();

      $CG->db->where('parent', null);
      $CG->db->from('messages');

      if(!empty($search))
      {
         search($search);
      }

      $query = $CG->db->get();
      if($query->num_rows() > 0)
      {
         return $query->result();
      }
      else
      {
         exit("no emails!");
      }
   }
}
if(!function_exists("entry_exists"))
{
   function entry_exists($table, $col, $value)
   {
      $CG = get_instance();
      return ($CG->db->get_where($table, array($col => $value))->num_rows() > 0);
   }
}
if(!function_exists("get_conversation"))
{
   function get_conversation($access_id)
   {
      $CG = get_instance();

      if(entry_exists('messages', 'access_id', $access_id))
      {
         $msgid = $CG->db->get_where('messages', array('access_id'=> $access_id))->row()->message_id;

         $CG->db->where('parent', $msgid);
         $CG->db->or_where('message_id', $msgid);
         $CG->db->order_by('date ASC');
         $query = $CG->db->get('messages');

         if($query->num_rows() > 0)
         {
            return $query->result();
         }
         else
         {
            exit('invalid message id');
         }
      }
   }
}

if(!function_exists("fetch_message"))
{
   /*
      Returns formatted message bodies
      and processes their attachments.
    */
   function fetch_message($client, $uid)
   {
      $structure = imap_fetchstructure($client, $uid,  FT_UID);

      //Check if email is plaintext or MIME type
      if($structure->type)
      {
         //The message is not just plaintext
         $emailMessage                     = new EmailMessage($client, $uid);
         $emailMessage->fetch();
         process_inline($emailMessage);
         $data = $emailMessage->bodyHTML;
      }
      else
      {
         //Is the message a reply?
         $email_header = imap_fetch_overview($client, $uid, FT_UID)[0];

         //The message is plaintext
         if(!isset($email_header->in_reply_to))
         {
            $data = imap_fetchbody($client, $uid, 1, FT_UID);
         }
         else{
            // Message is a reply, need to format returned value
            $data  = imap_fetchbody($client, $uid, 1, FT_UID);
            if(preg_match('/>\s\t*On/im', $data))
            {
               $data  = preg_replace('/>\s\t*On/im', '<br/><div class="collapse"> > On', $data);
               $data .= '</div>';
            }
         }
      }

      return($data);
   }
}

if(!function_exists('mark_for_deletion'))
{
   function mark_for_deletion($client, $checked_messages)
   {
      foreach($checked_messages as $message)
      {
         $CG = get_instance();
         if(entry_exists('messages','access_id', $message)){

            $email = $CG->db->get_where('messages', array('access_id' => $message))->row();

            if(isset($email->uid))
            {
               //Delete it off the server
               imap_delete($client, $email->uid, FT_UID);

               //Delete message and all its children
               $CG->db->where('access_id', $message);
               $CG->db->or_where('parent', $email->message_id);
               $CG->db->delete('messages');

               //Delete message's tag lookups
               //TODO:Delete message's children's tag lookups
               $CG->db->where('access_id', $message);
               $CG->db->delete('email_tag_x');
            }
         }
      }
   }
}

if(!function_exists('delete_messages'))
{
   function delete_messages($client, $checked_messages = '')
   {
      //NOTE:Client must ALWAYS be the client used to store UID's on the database, otherwise
      //this function may delete the wrong messages
      if(!empty($checked_messages))
      {
         mark_for_deletion($client, $checked_messages);
      }
      return imap_expunge($client);
   }
}

//Proccesses inline attachments
if(!function_exists("process_inline"))
{
   function process_inline($emailMessage)
   {
      $CG = get_instance();
      $CG->load->helper('string');
      preg_match_all('/src="cid:(.*)"/Uims', $emailMessage->bodyHTML, $matches);
      if(count($matches)) {

         $search = array();
         $replace = array();

         foreach($matches[1] as $match) {
            $uniqueFilename = "cidattach".random_string().".jpg";
            file_put_contents("images/$uniqueFilename", $emailMessage->attachments[$match]['data']);
            $search[] = "src=\"cid:$match\"";
            $replace[] = "src=\"http://emailclient.com/images/$uniqueFilename\"";
         }

         $emailMessage->bodyHTML = str_replace($search, $replace, $emailMessage->bodyHTML);

      }
   }
}

//Formats message for proper MIME formatting

/*
   NOTE:This function was borrowed from the php forums some modification will
   be made as the client progresses
 */

if(!function_exists("compose_message"))
{
   function compose_message($to, $subject, $content, $path = '', $is_reply = false, $cc = '', $bcc = '', $_headers = false)
   {
      $rn = "\r\n";

      $boundary         = md5(rand());
      $boundary_content = md5(rand());

      //Set up headers
      $headers  = 'From: example Client Test  <example@gmail.com>'.$rn;
      if($is_reply)
      {
         $headers .= 'Reply-To: '.$to.$rn;
         $headers .='In-Reply-To: '.$is_reply.$rn;
      }
      $headers .= 'Mime-version: 1.0'.$rn;
      $headers .= 'Content-Type: multipart/related;boundary='.$boundary.$rn;

      if(!empty($cc))
      {
         $headers .= 'Cc: '.$cc.$rn;
      }

      if(!empty($bcc))
      {
         $headers .= 'Bcc: '.$bcc.$rn;
      }

      $headers .= $rn;

      //Set up body
      $msg  = $rn . '--' . $boundary . $rn;
      $msg .= 'Content-Type: multipart/alternative;'.$rn;
      $msg .= " boundary=\"$boundary_content\"".$rn;


      //Plain text
      $msg .= $rn . '--' . $boundary_content  . $rn;
      $msg .= 'Content-Type: text/plain; charset=ISO-8859-1'.$rn;
      $msg .= strip_tags($content);

      //HTML
      $msg .= $rn . '--' . $boundary_content  . $rn;
      $msg .= 'Content-Type: text/html; charset=ISO-8859-1'.$rn;
      $msg .= 'Content-Transfer-Encoding: quoted-printable'.$rn;

      if($_headers)
      {
         $msg .= $rn . '<img src=3D"cid:template-H.PNG" />' . $rn;
      }

      $msg .= $rn . '<div>' . nl2br(str_replace("=", "=3D", $content)) . '</div>' . $rn;
      if ($_headers) {
         $msg .= $rn . '<img src=3D"cid:template-F.PNG" />' . $rn;
      }
      $msg .= $rn . '--' . $boundary_content . '--' . $rn;

      //TODO make sure this code is correct, probably  not correct with my system
      if ($path != '' && file_exists($path)) {
         $conAttached = prepare_attatchment($path);
         if ($conAttached !== false) {
            $msg .= $rn . '--' . $boundary . $rn;
            $msg .= $conAttached;
         }
      }

      if ($_headers) {
         $imgHead = dirname(__FILE__) . '/../../../../modules/notification/ressources/img/template-H.PNG';
         $conAttached = prepare_attatchment($imgHead);
         if ($conAttached !== false) {
            $msg .= $rn . '--' . $boundary . $rn;
            $msg .= $conAttached;
         }
         $imgFoot = dirname(__FILE__) . '/../../../../modules/notification/ressources/img/template-F.PNG';
         $conAttached = prepare_attatchment($imgFoot);
         if ($conAttached !== false) {
            $msg .= $rn . '--' . $boundary . $rn;
            $msg .= $conAttached;
         }
      }

      $msg .= $rn . '--' . $boundary . '--' . $rn;

      return array('message' => $msg, 
                   'headers' => $headers);
   }
}

if(!function_exists('process_attachments'))
{
   //TODO make this work with multiple uploads
   function process_attachments()
   {
      $upload_status = perform_upload();
      if(!$upload_status['success'])
      {
         //Something went wrong with upload, report the error
         //TODO: create a view specifically for reporting this error
         $error_message =$upload_status['error'];
         if(strpos($error_message, 'You did not select a file to upload.') === FALSE)
         {
            exit($error_message);
         }
         else
         {
            return '';
         }
      }
      else
      {
         return $upload_status['data']['file_path'].$upload_status['data']['file_name'];
      }
   }
}

//Helper function for process_attachments
if(!function_exists("perform_upload"))
{
   function perform_upload()
   {
      $config = array(
         'upload_path'   => './tmp/attachments/',
         'allowed_types' => '*',
      );

      $CG = get_instance();
      $CG->load->library('upload', $config);

      if ( ! $CG->upload->do_upload('userfile'))
      {
         //Upload failed, return false and the error message
         return array('success' => false,
                      'error'   => $CG->upload->display_errors());

      }
      else
      {
         //Upload succeeded, return true and information on the new file
         return array('success' => true,
                      'data'    => $CG->upload->data());
      }
   }
}

//Formats files for attachment
if(!function_exists("prepare_attatchment"))
{
   function prepare_attatchment($path)
   {
      $rn = "\r\n";

      if (file_exists($path)) {
         $finfo = finfo_open(FILEINFO_MIME_TYPE);
         $ftype = finfo_file($finfo, $path);
         $file = fopen($path, "r");
         $attachment = fread($file, filesize($path));
         $attachment = chunk_split(base64_encode($attachment));
         fclose($file);

         $msg = 'Content-Type: \'' . $ftype . '\'; name="' . basename($path) . '"' . $rn;
         $msg .= "Content-Transfer-Encoding: base64" . $rn;
         $msg .= 'Content-ID: <' . basename($path) . '>' . $rn;
         //            $msg .= 'X-Attachment-Id: ebf7a33f5a2ffca7_0.1' . $rn;
         $msg .= $rn . $attachment . $rn . $rn;
         return $msg;
      } else {
         return false;
      }
   }
}
if(!function_exists('debug_info'))
{
   function debug_info($client, $id)
   {
      $data['header']           = imap_fetchheader($client, $id, FT_UID);
      $data['body']             = imap_fetchbody($client, $id, '', FT_UID);
      $data['structure']        = imap_fetchstructure($client, $id, FT_UID);
      $data['folders']          = imap_getmailboxes($client, '{imap.gmail.com:993/imap/ssl/novalidate-cert}','*');
      $data['threads']           = imap_thread($client, SE_UID);

      return $data;
   }
}

// function create_thread_structure($client, $limb)
// {
//    $root_val = current($limb);
//    $root_tag = explode('.', key($limb));
//
//    $structure = array();
//    while(next($limb) !== FALSE){
//       $cur_val = current($limb);
//       $cur_key = key($limb);
//       $cur_tag = explode('.', $cur_key);
//
//       if($cur_tag[1] == 'branch')
//       {
//          if($cur_val && $cur_val != 19)
//          {
//             $end_branch_key = $cur_tag[0].'.num';
//             $key_array = array_keys($limb);
//
//             $offset = array_search($cur_key, $key_array);
//
//             $branch_key_subest = array_slice($key_array, $offset, (array_search( $end_branch_key, $key_array)-$offset)+1);
//
//             $sub_limb = array_intersect_key($limb, array_flip($branch_key_subest));
//
//
//             array_unshift($structure,create_thread_structure($client, $sub_limb));
//
//             $limb = array_diff_key($limb, $sub_limb);
//
//          }
//       }
//       elseif($cur_tag[1] == 'num')
//       {
//
//          array_unshift($structure, imap_headerinfo($client, $cur_val));
//
//          unset($limb[$cur_key]);
//          reset($limb);
//
//          if($cur_tag[0] == $root_tag[0])
//          {
//             return $structure;
//          }
//       }
//    }
//    return $structure;
// }

