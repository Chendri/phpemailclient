<div class="container">
<table class="table table-striped table-hover table-condensed">
   <thead>
      <th>From</th>
      <th>Subject</th>
   </thead>
   <tbody>
   <?php foreach($emails as $email):?>
   <tr class="clickable-row" data-href="<?php echo base_url('email').'/read_message/'.$email->msgno?>">
      <td><?php echo $email->from?></td>
      <td><?php echo $email->subject?></td>
      <td><a href="<?php echo base_url('index.php/email').'/read_message/'.$email->uid?>">test</a></td>
   </tr>
   <?php endforeach;?>
   </tbody>
</table>
</div>
