<div class="container">

<!-- Add new tag -->
<?php echo form_open('email/new_tag', array('role' => 'form', 'class' => 'form-inline'))?>
   <input class="form-control" type="text" name="tag_name" placeholder="Tag Name"></input>
   <button class="btn btn-default" type="submit" name="submit">Submit</button>
</form>
<!-- Add new tag -->

<!-- Tag Set -->
<?php if($groups !== FALSE && count($groups) > 0):?>
   <div class="form-inline form-group">
      <select class="form-control" id="group-sel">
      <?php foreach($groups as $group):?>
         <option><?php echo $group->tag_name?></option>
      <?php endforeach;?>
      </select>
      <button class="btn btn-default" id="add-group-button">Add</button>
      <button class="btn btn-default" id="del-group-button">Remove</button>
   </div>
<?php endif;?>
<!-- Tag Set -->

<!-- Search form -->
<?php echo form_open('email/search', array('role' => 'form', 'class' => 'form-inline'))?>
<div class="form-group">
   <label class="sr-only" for="search_text">Search: </label>
   <input class="form-control" type="text" name="search_text" placeholder="Search.."></input>
</div>
<button class="btn btn-default" type="submit" name="submit">Go</button>
</form>
<!-- Search form -->

<!--Inbox table  -->
Select all: <input type="checkbox" id="select-all"></input>
<table style='table-layout:fixed;' class="table table-striped table-hover table-condensed">
   <thead>
   </thead>
   <tbody>

   <?php foreach($emails as $email):?>
<?php
      $access_id = trim($email->access_id);

      echo "<tr>
         <td><input type='checkbox' class='checkbox select-row' data-msgno='$access_id'</td>

         <td style='cursor:pointer;' class='date-row clickable-row' data-href='".base_url('email')."/read_message/$access_id'>$email->date</td>
         <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$access_id'>$email->from</td>
         <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$access_id'>$email->subject</td>


         </tr>";
?>
   <?php endforeach;?>
   </tbody>
</table>
<!--Inbox table  -->

<button class="btn btn-danger" type="button" data-toggle="modal" data-target="#confirm-delete">Delete</button>
<a class="btn btn-default" href="<?php echo base_url('email').'/write_message'?>">Compose Message</a>

<!--Confirm deletion modal  -->
<div id="confirm-delete" class="modal fade" role="dialog">                                                                                                                                    
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Are you sure you wish to delete these emails?</h4>
         </div>
         <div class="modal-body">
            <p>This operation cannot be reversed</p>
         </div>
         <div class="model-footer">
            <button class="btn btn-danger btn-ok" onClick="delete_messages()">Delete</a>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
         </div>
      </div>
   </div>
</div>
<!--Confirm deletion modal  -->

</div>
<script src="<?php echo base_url().'application/js/inbox.js'?>"></script>
