jQuery(document).ready(function($) {
   $(".clickable-row").click(function() {
      window.document.location = $(this).data("href");
   });
   $("#add-group-button").click(function(){
      console.log('test');
      checked_messages = Array();
      var tag_name = $('#group-sel').val();
      $(".select-row").each(function(){
         if($(this).prop('checked')){
            checked_messages.push($(this).attr("data-msgno"));
         }
      });

      $.ajax({
         type:"POST",
         url: "http://emailclient.com/email/add_to_group",
         data: {checked_messages:checked_messages, tag_name:tag_name},
         success:function(data){
            console.log('success');
            // location.reload();
         }
      });
   });
   $("#del-group-button").click(function(){
      checked_messages = Array();
      var tag_name = $('#group-sel').val();
      $(".select-row").each(function(){
         if($(this).prop('checked')){
            checked_messages.push($(this).attr("data-msgno"));
         }
      });

      $.ajax({
         type:"POST",
         url: "http://emailclient.com/email/remove_from_group",
         data: {checked_messages:checked_messages, tag_name:tag_name},
         success:function(data){
            console.log('success');
            // location.reload();
         }
      });
   });

   $("#select-all").change(function(){
      $(".select-row").prop('checked',$(this).prop("checked"));
   });

   $('.accordion-body').on('shown.bs.collapse', function () {
      var pHeader = '#' + $(this).data('parent');
      $(pHeader + " i.indicator").removeClass("glyphicon-chevron-up").addClass("glyphicon-chevron-down");
   });

   $('.accordion-body').on('hidden.bs.collapse', function () {
      var pHeader = '#' + $(this).data('parent');
      $(pHeader + " i.indicator").removeClass("glyphicon-chevron-down").addClass("glyphicon-chevron-up");
   });

});

function delete_messages(){

   checked_messages = Array();

   $(".select-row").each(function(){

      if($(this).prop("checked"))
      {
         checked_messages.push($(this).attr("data-msgno"));
      }
   });

   $.ajax({
      type:"POST",
      url: "http://emailclient.com/email/delete_messages",
      data:{checked_messages:checked_messages},
      success:function(data){
         console.log("success");
         location.reload();
      }
   });
}
function format_date(date){
   var d1 = new Date(date);
   return d1.toLocaleDateString();
}
