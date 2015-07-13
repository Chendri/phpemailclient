<?php
require 'application/third_party/EmailMessage.php';
//TODO Have the email client read off of some config file only reachable by the server
if(!function_exists("get_login_info"))
{
   function get_login_info()
   {
      $data['username'] = 'exmaple@example.com';
      $data['password'] = 'example';

      return $data;
   }
}

if(!function_exists("open_inbox"))
{
   function open_inbox($username = '', $password='')
   {
      //Having some certification problem, going to deal with it later.
      $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}[Gmail]/All Mail';
      $data = get_login_info();
      return imap_open($hostname, $data['username'], $data['password']);
   }
}

if(!function_exists("open_sent_folder"))
{
   function open_sent_mailbox()
   {
      $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}Sent Messages';

      $data = get_login_info();
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
if(!function_exists("fetch_inbox"))
{
   function fetch_inbox()
   {
      $client = open_all();
      $thread = imap_thread($client);

      do{
         $cur_val = current($thread);
         $cur_key = key($thread);

         $tree = explode('.', $cur_key);
         if($tree[1] == 'num' && $cur_val){

            $branch = new SplDoublyLinkedList();
            // $branch->push(imap_headerinfo($client, $cur_val));

            if(next($thread))
            {
               $next_val = current($thread);
               if($next_val && $next_val != 6)
               {
                  //Get the whole conversation subset and reverse it.
                  $end_branch_key = $tree[0].'.branch';
                  $key_array = array_keys($thread);

                  $offset = array_search($cur_key, $key_array);

                  $branch_key_subest = array_slice($key_array, $offset, (array_search( $end_branch_key, $key_array)-$offset)+1);

                  // $limb = array_reverse(array_intersect_key($thread, array_flip($branch_key_subest)));
                  $limb = array_intersect_key($thread, array_flip($branch_key_subest));
                  // exit(var_dump($limb));

                  $data['branch'] = branch_recurse($client, $limb, $branch);

                  $CG = get_instance();
                  $CG->load->view('email/debug.php', $data);
               }
            }   
         }
      }while(next($thread) !== FALSE);
      return $data;
   }
}
if(!function_exists("branch_recurse"))
{
   function branch_recurse($client, $limb, $branch, $key = '')
   {
      if(!empty($key))
      {
         if(empty($limb[$key]))
         {
            return array('message' => 'end_of_limb', 'this_branch' => $branch);
         }
         $cur_val = $limb[$key];
         $cur_key = $key;
      }
      else
      {
         $cur_val = current($limb);
         $cur_key = key($limb);
      }

      next($limb);
      $next_key = key($limb);

      $cur_tag = explode('.', $cur_key);

      switch($cur_tag[1]){
      case 'branch':
         if($cur_val)
         {
            exit($next_key);
            //Sibling branches present
            $sibling_key = $cur_val.'.num';

            $sibling_branch = new SplDoublyLinkedList();
            $sibling_branch = branch_recurse($client, $limb,$sibling_branch,  $sibling_key);

            $data = array('message' => 'sibling_found', 'this_branch' => $branch, 'sibling_branch' => $sibling_branch);

            return $data;
         }
         else
         {
             $data = array('message' => 'no_siblings', 'this_branch' => $branch);
             return $data;
         }
         break;
      case 'next':
         if($cur_val)
         {

            //There are still more values in this branch
            $child_results = branch_recurse($client, $limb, $branch,$next_key);
         
            $self_results  = branch_recurse($client, $limb, $branch, $cur_tag[0].'.branch');

            if(!empty($child_results['message']) && $child_results['message'] == 'sibling_found')
            {
               $temp_branch = new SplDoublyLinkedList();
               $temp_branch->push($child_results['this_branch']);
               $temp_branch->push($child_results['sibling_branch']);

               $self_results['message']     = "branch_finish";
               $self_results['this_branch'] = $temp_branch;
            }

            return $self_results;
         }
         else
         {
            //The branch ends here, check for siblings
            return branch_recurse($client, $limb, $branch,$next_key);
         }
         break;
      case 'num':
         if($cur_val)
         {
            $results = branch_recurse($client, $limb, $branch,$next_key);
            if(!empty($results['message']))
            {
               switch($results['message']){
               case 'branch_finish':
                  $temp_branch = $results['this_branch'];
                  $temp_branch->unshift($cur_val);
                  $branch->push($temp_branch);
                  break;
               case 'sibling_found':
                  $results['this_branch']->push($cur_val);
                  return $results;
                  break;
               case 'end_of_limb':
                  return $results;
                  break;
               }
            }
         }
         break;
      }
   }
}
// if(!function_exists("branch_recurse"))
// {
//    function branch_recurse($client, $limb, $branch, $root_tag)
//    {
//       $cur_val = current($limb);
//       $cur_key = key($limb);
//
//       $cur_tag = explode('.', $cur_key);
//
//       if($cur_tag[1] == 'branch')
//       {
//          $next_val = next($limb);
//          $next_tag = explode('.', key($limb));
//
//          if($next_tag[1] != 'branch')
//          {
//             prev($limb);
//             $temp_branch = new SplDoublyLinkedList();
//
//             while($next_tag[1] != 'branch' && next($limb) !== FALSE)
//             {
//                $next_val = current($limb);
//                $next_tag = explode('.', key($limb));
//
//                if($next_tag[1] == 'num')
//                {
//                   if($next_val)
//                   {
//                      // $temp_branch->unshift(imap_fetchheader($client, $next_val));
//                      $branch->unshift($next_tag[0]);
//                   }
//                }
//             }
//             return $branch;
//          }
//          else
//          {
//             $next_val = next($limb);
//             $next_tag = explode('.', key($limb));
//
//             if($next_tag[1] != 'branch')
//             {
//                if($next_tag[1] == 'next')
//                {
//                   $temp_branch = new SplDoublyLinkedList();
//                   while($next_tag[1] != 'branch' && next($limb) !== FALSE)
//                   {
//                      $next_val = current($limb);
//                      $next_tag = explode('.', key($limb));
//
//                      if($next_tag[1] == 'num')
//                      {
//                         if($next_val)
//                         {
//                            // $temp_branch->unshift(imap_fetchheader($client, $next_val));
//                            $temp_branch->unshift($next_tag[0]);
//                         }
//                      }
//                   }
//                   if(current($limb))
//                   {
//                      $branch->unshift($temp_branch);
//                      $branch = branch_recurse($client, $limb, $branch, $root_tag);
//                   }
//                   else
//                   {
//                      while($temp_branch->valid())
//                      {
//                         //This seems inefficient, might want to try and see if there's a 
//                         //way to just append the whole list, or look for another way to temporarily store the values
//                         $branch->unshift($temp_branch->pop());
//                      }
//                   }
//                   return $branch;
//                }
//                prev($limb);
//             }
//             else{
//                prev($limb);
//                $branch = branch_recurse($client, $limb, $branch, $root_tag);
//                return $branch;
//             }
//          }
//       }
//       else{
//          exit($cur_val);
//       }
//
//    }
// }
if(!function_exists("fetch_message"))
{
   function fetch_message($client, $msgno)
   {
      $structure = imap_fetchstructure($client, $msgno);

      if($structure->type)
      {
         //The message is not just plaintext
         $emailMessage                     = new EmailMessage($client, $msgno);
         $emailMessage->fetch();
         process_inline($emailMessage);
         $data['body'] = $emailMessage->bodyHTML;
      }
      else
      {
         //The message is plaintext
         $data['body'] = imap_fetchbody($client, $msgno, 1);
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
   function compose_message($to, $subject, $content, $path = '', $cc = '', $bcc = '', $_headers = false)
   {
      $rn = "\r\n";

      $boundary         = md5(rand());
      $boundary_content = md5(rand());

      //Set up headers
      $headers  = 'From: exmaple  <example@example.com>'.$rn;
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

      $data['header']           = imap_fetchheader($client, $id);
      $data['body']             = imap_fetchbody($client, $id, '');
      $data['structure']        = imap_fetchstructure($client, $id);
      $data['folders']          = imap_getmailboxes($client, '{imap.gmail.com:993/imap/ssl/novalidate-cert}','*');
      $data['threads']           = imap_thread($client);

      return $data;
   }
}
