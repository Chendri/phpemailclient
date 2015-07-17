jQuery(document).ready(function($) {
   $(".clickable-row").click(function() {
      window.document.location = $(this).data("href");
   });

   new_count();
   setInterval(function(){new_count();}, 60000);
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
            $("#select-all").prop('checked', false);
            $(".select-row").prop('checked', false);
         }
      });
   });

   //Remove checked messages    
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
            location.reload();
         }
      });
   });

   $(".tag-tab").click(function(){
      var tag_name = $(this).attr("id");
      var el = this;

      if(tag_name != 'inbox')
      {
         console.log(tag_name);
         $.ajax({
            type:"GET",
            url: "http://emailclient.com/email/get_group_members",
            data: {tag_name:tag_name},
            success:function(data){
               $('.tag-li.active').removeClass('active');
               $(el).parent().addClass('active');

               $('.message-row').hide();
               if(data == "NOMATCH")
               {
                  $('#no_message').show();
               }
               else{
                  $('#no_message').hide();

                  data = JSON.parse(data);

                  for(var i = 0, len = data.length; i < len; ++i)
                  {
                     $('#'+data[i].access_id).show();
                  }
               }
            }
         });
      }else{
         $('.tag-li.active').removeClass('active');
         $(el).parent().addClass('active');

         $('#no_message').hide();

         $('.message-row').show();
      }
   });
   $('.view-tab').click(function(){
      var view_index = $('.view-tab').index(this);

      $('.view-li.active').removeClass('active');
      $(this).parent().addClass('active');

      if(view_index > 0)
      {
         $('tr').hide();
         $("tr[data-status='"+view_index+"']").show();
      }
      else
      {
         $('tr').show();
      }
   });
   $("#new-messages").click(function(){
      location.reload();
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
function new_count(){
      $.ajax({
         type:"POST",
         url:"http://emailclient.com/email/new_count",
         success:function(data){
            if(data != "NONEW"){
               console.log(data+" new messages");
               $('#new-messages').html(data+" new message(s)");
               $('#new-messages').show();
            }
            else{
               $('#new-messages').hide();
            }
         }
      });
}
function format_date(date){
   var d1 = new Date(date);
   return d1.toLocaleDateString();
}
