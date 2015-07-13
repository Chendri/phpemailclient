jQuery(document).ready(function($) {
   $(".clickable-row").click(function() {
      window.document.location = $(this).data("href");
   });

   $("#select-all").change(function(){
      $(".select-row").prop('checked',$(this).prop("checked"));
   });
   $(".date-row").each(function(){
      var date = $(this).attr('data-date');
      $(this).text(format_date(date));
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
