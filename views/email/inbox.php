<div class="container">


<!--Inbox table  -->
Select all: <input type="checkbox" id="select-all"></input>
<table class="table table-striped table-hover table-condensed">
   <thead>
      <th class="col-xs-2"></th>
      <th>Date Received</th>
      <th>From</th>
      <th>Subject</th>
      <th></th>
   </thead>
   <tbody>

   <?php foreach($emails as $branch):?>
<?php
   $branch->rewind();
   $head = $branch->current();//Get the head of the email branch
   $msgno = trim($head->Msgno);
   // exit(var_dump($head));
   if($branch->count() > 1){
      $branch->next(); 
   echo "<tr class='accordion-toggle' data-toggle='collapse' data-target='#$msgno-thread' id='$msgno'>

         <td><input type='checkbox' class='checkbox select-row' data-Msgno='$msgno'</td>

         <td style='cursor:pointer;' class='date-row clickable-row' data-date='$head->date' data-href='".base_url('email')."/read_message/$msgno'></td>
         <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$msgno'>$head->fromaddress</td>
         <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$msgno'>$head->subject</td>

         <td><i class='indicator glyphicon glyphicon-chevron-down pull-right'></i></td> 
      </tr>";
echo "<tr> 
         <td class='hidden-row'>
            <div class='accordion-body collapse in' id='$msgno-thread' data-parent='$msgno'>
               <table class='table table-striped table-bordered table-hover'>
                  <thead>
                     <tr>
                        <th>Date</th>
                        <th>From</th>
                     </tr>
                  </thead>
                  <tbody>";
   do{
   $current = $branch->current();

   $msgno = trim($current->Msgno);
   echo "<tr>
            <td style='cursor:pointer;' class='date-row clickable-row' data-date='$head->date' data-href='".base_url('email')."/read_message/$msgno'></td>
            <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$msgno'>$current->fromaddress</td>

         </tr>";
   $branch->next();
   }while($branch->valid());
   echo "</tbody>
      </table>
   </div>
   </td>
   </tr>";
}
else{
   echo "<tr>
      <td><input type='checkbox' class='checkbox select-row' data-Msgno='$msgno'</td>

      <td style='cursor:pointer;' class='date-row clickable-row' data-date='$head->date' data-href='".base_url('email')."/read_message/$msgno'></td>
      <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$msgno'>$head->fromaddress</td>
      <td style='cursor:pointer;' class='clickable-row' data-href='".base_url('email')."/read_message/$msgno'>$head->subject</td>

      <td><i class='indicator glyphicon glyphicon-chevron-down pull-right'></i></td> 

      </tr>";
}?>
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
<script src="application/js/inbox.js"></script>
