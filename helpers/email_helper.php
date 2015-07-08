<?php
if(!function_exists("open_stream"))
{
   function open_stream()
   {
      //Having some certification problem, going to deal with it later.
      $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX';
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
   function fetch_message($client, $uid)
   {
      $message['header']        = imap_fetchheader($client, $uid, FT_UID);
      $message['body']          = imap_fetchbody($client, $uid, FT_UID);
      return($message);
   }
}
