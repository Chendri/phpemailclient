<div class="container">
<table class="table table-striped table-hover table-condensed">
   <thead>
      <th>From</th>
      <th>Subject</th>
   </thead>
   <tbody>
   <?php foreach($emails as $email):?>
   <tr style="cursor:pointer;" class="clickable-row" data-href="<?php echo base_url('email').'/read_message/'.$email->msgno?>">
      <td><?php echo $email->from?></td>
      <td><?php echo $email->subject?></td>
   </tr>
   <?php endforeach;?>
   </tbody>
</table>
</div>
<script src="application/js/inbox.js"></script>
