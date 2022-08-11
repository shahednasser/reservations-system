$(document).ready(function(){
   $("select[name=section]").change(function(){
      let section = $(this).val();
      location.href = location.origin + '/help/' + section;
   });
});