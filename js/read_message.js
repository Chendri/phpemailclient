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

            $(el).children("div[dir!='ltr']").addClass('collapse');
            $(el).children("div[dir!='ltr']").attr('id',$(el).attr('id')+'_reply-chain');

            //This is just a temporary fix until I find a better solution
            $(el).children(':only-child').removeClass('collapse');

            $(el).children("div[dir='ltr']").children("div[dir!='ltr']").addClass('collapse');
            $(el).children("div[dir='ltr']").children("div[dir!='ltr']").attr('id', $(el).attr('id')+'_reply-chain');
         });
         }
      });
};

