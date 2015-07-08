<div class="container">
<table class="table table-striped table-hover table-condensed">
   <thead>
      <th>Select all: <input type="checkbox" id="select-all"></input></th>
      <th>From</th>
      <th>Subject</th>
   </thead>
   <tbody>
   <?php foreach($emails as $email):?>
   <tr style="cursor:pointer;">
      <td><input type="checkbox" class="checkbox select-row" data-msgno="<?php echo $email->msgno?>"</td>
      <td class="clickable-row" data-href="<?php echo base_url('email').'/read_message/'.$email->msgno?>"><?php echo $email->from?></td>
      <td class="clickable-row" data-href="<?php echo base_url('email').'/read_message/'.$email->msgno?>"><?php echo $email->subject?></td>
   </tr>
   <?php endforeach;?>
   </tbody>
</table>

<button class="btn btn-danger" type="button" data-toggle="modal" data-target="#confirm-delete">Delete</button>
<a class="btn btn-default" href="<?php echo base_url('email').'/write_message'?>">Compose Message</a>

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

</div>
<script src="application/js/inbox.js"></script>
