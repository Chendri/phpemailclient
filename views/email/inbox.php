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
<button class="btn btn-danger" type="button" onClick="delete_messages()">Delete</button>
<a class="btn btn-default" href="<?php echo base_url('email').'/write_message'?>">Compose Message</a>
</div>
<script src="application/js/inbox.js"></script>
