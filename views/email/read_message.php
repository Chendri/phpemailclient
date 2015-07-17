<div class="container">

<?php $first = reset($emails);?>
<?php $last = end($emails);?>
<?php if(count($emails) > 1):?>
<div class="list-group">
<?php foreach($emails as $key=>$email):?>


<div class='list-group-item'>
   <div class="msg-body"id=<?php echo $email->access_id;?>><?php echo $email->body?></div>

   <?php if(isset($email->parent)):?>
      <i class="glyphicon glyphicon-plus hidden" id='icon-<?php echo $email->access_id;?>'data-toggle='collapse' href='#<?php echo $email->access_id."-email-chain"?>'></i><br/>
   <?php endif;?>
</div>
<?php endforeach;?>

</div>
<?else:?>

<?php echo $first->body ?>
<?endif;?>
<?php 
if($first)
{
   $to = $first->from_address;
}
else
{
   $to = $header->from_address;
   $last = $header;
   $first = $header;
}
?>
   <?php echo form_open_multipart("email/send_reply/$uid", array('role' => 'form', 'class' => 'form-horizontal'))?>

      <input class="hidden"  type="text" id="in_reply_to" name="in_reply_to" value="<?php echo $last->message_id?>"></input>

      <input class="hidden"  type="email" id="recipient" name="recipient" value="<?php echo $to?>"></input>

      <input class="hidden"  type="text"  id="subject" name="subject" value="<?php echo 'Re: '.$first->subject?>"></input>

      <div class="form-group">
         <label class="control-label col-sm-2" for="message">Reply</label>
         <div class="col-sm-10">
            <textarea class="form-control" rows="4" cols="50" id="message" name="message"></textarea>
         </div>
      </div>

      <div class="form-group">
         <div class="col-sm-offset-2 col-sm-10">
            <input type="file" class="btn btn-default" name="userfile" size="20"></input>
         </div>
      </div>

      <div class="form-group"> 
         <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-default">Send Reply</button>
         </div>
      </div>  

   </form>

<a href="<?php echo base_url().'email'?>" class="btn btn-default">Go Back<a/>
</div>

<script src="<?php echo base_url().'application/js/read_message.js'?>"></script>

