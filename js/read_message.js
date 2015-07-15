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
});
function Retrieve_Message(el, action)
{
   console.log(el);
   $.ajax({
      method: 'GET',
      url:action,
      success:function(data){
         console.log('success');
         $(el).html(data);
         //I"ll probably do this whole thing differently in the future.
         $(el).ready(function(){
            var gmail   = $(el).find("div[class*='gmail']");
            var block   = $(el).find("blockquote");
            if(gmail.length)
            {
               gmail.first().removeClass('in');

               gmail.first().addClass('collapse');
            }
            if(block.length){
               block.first().removeClass('in');

               block.first().addClass('collapse');
            }

            var collapse = $(el).find(".collapse");
            if(collapse.length){
               collapse.first().attr('id', $(el).attr('id')+'_reply-chain');
               $(el).find(".collapse:gt(0)").removeClass("collapse");
               $(el).next().removeClass("hidden");
            }
         });
         }
      });
};

