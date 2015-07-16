$(document).ready(function(){
   $('.message').each(function(){
      Retrieve_Message(this, $(this).data('href'));
   });
   $('.glyphicon').click(function(){
      if($(this).hasClass('glyphicon-minus'))
      {
         $(this).removeClass('glyphicon-minus');

         $(this).addClass('glyphicon-plus');
      }
      else
      {

         $(this).removeClass('glyphicon-plus');

         $(this).addClass('glyphicon-minus');
      }
   });
   $('.msg-body').each(function(){
      var gmail   = $(this).find("div[class*='gmail']");
      var block   = $(this).find("blockquote");
      if(gmail.length)
      {
         gmail.first().removeClass('in');

         gmail.first().addClass('collapse');
      }
      if(block.length){
         block.first().removeClass('in');

         block.first().addClass('collapse');
      }

      var collapse = $(this).find(".collapse");
      if(collapse.length){
         var id = $(this).attr('id')+'-email-chain';
         console.log(id);
         console.log(this);
         collapse.first().attr('id', id);
         $(this).find(".collapse:gt(0)").removeClass("collapse");
         $(this).next().removeClass("hidden");
      }
   });
});


