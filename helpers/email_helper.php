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

if(!function_exists('check_conversation'))
{
   function check_conversation($header, $imap, &$blacklist=null)
   {
      $threads = array();

      $subject = $header->subject;

      $msg_ids          = array();//Message id's
      $reply_ids        = array();//Id's contained in in_reply_to

      $temp_reply_ids   = array();//Temporary storage for ID's that may or may not be replies
      $temp_msg_ids     = array();//Temporary storage for Id's that may or may not be the main message

      $msg_ids[$header->message_id] = strtotime($header->date);

      //remove re: and fwd:
      $subject = trim(preg_replace("/Re\:|re\:|RE\:|Fwd\:|fwd\:|FWD\:/i", '', $subject));
      
      //search for subject in current mailbox
      $results = imap_search($imap,  'SUBJECT "'.$subject.'"', SE_UID);

      //because results can be false
      if(is_array($results)) {
         //now get all the emails details that were found
         $emails = imap_fetch_overview($imap, implode(',', $results), FT_UID);

         //foreach email
         foreach ($emails as $email) {
               if(isset($email->in_reply_to) && isset($msg_ids[$email->in_reply_to]))
               {
                  //add to threads
                  //we date date as the key because later we will sort it
                  $key = strtotime($email->date);
                  $threads[$key] = $email;
                  $msg_ids[$email->message_id]     = $key;
                  $reply_ids[$email->in_reply_to]  = $key;

                  if(!is_null($blacklist))
                  {
                     $blacklist[$email->message_id] = true;
                  }
               }
               elseif(!isset($email->in_reply_to))
               {
                  $key = strtotime($email->date);
                  $temp_msg_ids[$email->message_id] = array('date' => $key, 'email' => $email);

               }
               else{
                  $key = strtotime($email->date);
                  $temp_reply_ids[$email->message_id] = array('date' => $key, 'email' => $email);
               }
         }
         foreach($temp_reply_ids as $reply_id => $val)
         {
            if(isset($msg_ids[$reply_id]))
            {
               $date = $val['date'];
               $email = $val['email'];

               unset($temp_reply_ids[$reply_id]);

               $reply_ids[$email->in_reply_to]  = $date;
               $threads[$date] = $email;
               if(!is_null($blacklist))
               {
                  $blacklist[$email->message_id] = true;
               }
               reset($temp_reply_ids);
            }
         }
         foreach($temp_msg_ids as $msg_id => $val)
         {
            if(isset($reply_ids[$msg_id]))
            {
               $date = $val['date'];
               $email = $val['email'];

               if(!is_null($blacklist))
               {
                  $blacklist[$email->message_id] = true;
               }
               $threads[$date] = $email;
               break;
            }
         }
      }

      //sort keys so we get threads in chronological order
      ksort($threads);

      return($threads);
   }
}
if(!function_exists("fetch_inbox"))
{
   function fetch_inbox($client)
   {
      //TODO clean this up, imap_thread isn't necessary
      $thread = imap_thread($client, SE_UID);
      $blacklist = array();//Array containing message id's of emails that have already been searched

      do{
         $cur_val = current($thread);
         $cur_key = key($thread);

         $tree = explode('.', $cur_key);
         $subject = '';
         if($tree[1] == 'num' && $cur_val){
            $header = imap_fetch_overview($client, $cur_val, FT_UID)[0];

            next($thread);

            if(!isset($blacklist[$header->message_id]))
            {
               $blacklist[$header->message_id] = true;

               $imap = open_all();

               $results = check_conversation($header, $imap, $blacklist);

               if($results)
               {
                  $header = reset($results);
               }

               $data[$header->message_id] = $header;
            }
         }
      }while(next($thread) !== FALSE);
      return $data;
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
         $is_reply = imap_fetch_overview($client, $uid, FT_UID)[0]->in_reply_to;

         //The message is plaintext
         if(!$is_reply)
         {
            $data = imap_fetchbody($client, $uid, 1, FT_UID);
         }
         else{
            // Message is a reply, need to format returned value
            $data  = imap_fetchbody($client, $uid, 1, FT_UID);
            $data  = preg_replace('/> On/', '<br/><div> > On', $data);
            $data .= '</div>';
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
         imap_delete($client, $message);
      }
   }
}

if(!function_exists('delete_messages'))
{
   function delete_messages($client, $checked_messages = '')
   {
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
      $headers  = 'From: example client test  <example@gmail.com>'.$rn;
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
      // $data['header']           = imap_fetchheader($client, $id, FT_UID);
      // $data['body']             = imap_fetchbody($client, $id, '', FT_UID);
      // $data['structure']        = imap_fetchstructure($client, $id, FT_UID);
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

