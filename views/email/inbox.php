<div class="container">


<!--Inbox table  -->
Select all: <input type="checkbox" id="select-all"></input>
<table style='table-layout:fixed;' class="table table-striped table-hover table-condensed">
   <thead>
      <!-- <th class="col&#45;xs&#45;2"></th> -->
      <!-- <th>Date Received</th> -->
      <!-- <th>From</th> -->
      <!-- <th>Subject</th> -->
      <!-- <th></th> -->
   </thead>
   <tbody>

   <?php foreach($emails as $email):?>
<?php
      $uid = trim($email->uid);

      echo "<tr>
         <td><input type='checkbox' class='checkbox select-row' data-Msgno='$uid'</td>

         <td style='cursor:pointer;' class='date-row clickable-row' data-date='$email->date' data-href='".base_url('email')."/read_message/$uid'></td>
         <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$uid'>$email->from</td>
         <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$uid'>$email->subject</td>


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
