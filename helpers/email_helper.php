<?php
require 'application/third_party/EmailMessage.php';
//TODO Have the email client read off of some config file only reachable by the server
if(!function_exists("open_stream"))
{
   function open_stream($username = '', $password='')
   {
      //Having some certification problem, going to deal with it later.
      $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
      //DEBUG SETTINGS
      $username = 'example@example.com';
      $password = 'password';

      return imap_open($hostname, $username, $password);
   }
}
if(!function_exists("fetch_inbox"))
{
   function fetch_inbox($client)
   {
      $MC = imap_check($client);

      return imap_fetch_overview($client, "1:{$MC->Nmsgs}",0);
   }
}
if(!function_exists("fetch_message"))
{
   function fetch_message($client, $msgno)
   {
      $emailMessage                     = new EmailMessage($client, $msgno);
      $emailMessage->fetch();
      process_inline($emailMessage);

      $data['body'] = $emailMessage->bodyHTML;

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
   function compose_message($to, $subject, $content, $path = '', $cc = '', $bcc = '', $_headers = false)
   {
      $rn = "\r\n";

      $boundary         = md5(rand());
      $boundary_content = md5(rand());

      //Set up headers
      $headers  = 'From:example <example@example.com>'.$rn;
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
