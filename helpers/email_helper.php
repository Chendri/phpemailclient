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
      $username = '';
      $password = '';

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
