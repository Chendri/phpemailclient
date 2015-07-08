<div class="container">

   <?php echo validation_errors('<div class="alert alert-danger fade in"> 
                                 <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>',
                                 '</div>');?>

   <?php echo form_open_multipart('email/send_message', array('role' => 'form', 'class' => 'form-horizontal'))?>

      <div class="form-group">
         <label class="control-label col-sm-2" for="recipient">Recipient:</label>
         <div class="col-sm-10">
            <input class="form-control"  type="email" id="recipient" name="recipient" placeholder="Recipient's email address"></input>
         </div>
      </div>

      <div class="form-group">
         <label class="control-label col-sm-2" for="subject">Subject:</label>
         <div class="col-sm-10">
            <input class="form-control"  type="text" id="subject" name="subject" placeholder="Message subject"></input>
         </div>
      </div>

      <div class="form-group">
         <label class="control-label col-sm-2" for="message">Message</label>
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
            <button type="submit" class="btn btn-default">Send Message</button>
         </div>
      </div>  

   </form>

   <a href="<?php echo base_url().'email'?>" class="btn btn-default">Go Back</a>

</div>
