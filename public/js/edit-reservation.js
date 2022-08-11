$(document).ready(function(){
  $("input[name=pledge]").change(function(){
    $("button[type=submit]").prop("disabled", !$(this).is(":checked"));
  });

  $("form").submit(function(){
    $(".alert-danger").remove();
    if(!$("input[name=pledge]").is(":checked")){
      return false;
    }
    if($("input[name*=day_]").length){
      //long reservation
      let checked_count = 0, error = "";
      let $from_date = $("input[name=from_date]"),
          $to_date = $("input[name=to_date]");
      if($from_date.val() >= $to_date.val()){
        $from_date.addClass("is-invalid");
        error = 'لا يمكن أن يكون تاريخ البداية بعد أو نفس تاريخ النهاية.';
      }
      $("input[name*=day_]").map(function(){
        let nb = ($(this).attr("name").split("_"))[1]
            $from_time = $("input[name=from_time_" + nb + "]"),
            $to_time = $("input[name=to_time_" + nb + "]");
        if($(this).is(":checked")){
          if($from_time.val() >= $to_time.val()){
            if(error != ""){
              error += "<br>";
            }
            $from_time.addClass("is-invalid");
            error += "لا يمكن أن يكون وقت بداية النشاط في نفس وقت أو بعد وقت نهاية النشاط";
          }
          else{
            checked_count++;
            $from_time.removeClass("is-invalid");
          }
        }
        else{
          $from_time.removeClass("is-invalid");
        }
      });
      if(error != ""){
        error = '<div class="alert alert-danger">' + error + "</div>";
        $(this).before(error);
        scrollTo($(".alert-danger"));
        return false;
      }
      if(!checked_count){
        $(this).before('<div class="alert alert-danger">يجب على الأقل إختيار يوم واحد من الإسبوع</div>');
        scrollTo($(".alert-danger"));
        return false;
      }
    }
    else{
      //temporary reservation
      let checked_count = 0, error = "";
      $("input[name*=dates_]").map(function(){
        let nb = ($(this).attr("name").split("_"))[1];
        let $from_time = $("input[name=from_time_" + nb + "]"),
            $to_time = $("input[name=to_time_" + nb + "]"),
            $date = $("input[name=date_" + nb + "]");
        if($(this).is(":checked")){
          if(!$date.val()){
            if(error != ""){
              error += "<br>";
            }
            error += "يجب تحديد اليوم";
            $date.addClass("is-invalid");
          }
          else{
            $date.removeClass("is-invalid");
          }
          if($from_time.val() >= $to_time.val()){
            if(error != ""){
              error += "<br>";
            }
            $from_time.addClass("is-invalid");
            error += "لا يمكن أن يكون وقت بداية النشاط في نفس وقت أو بعد وقت نهاية النشاط";
          }
          else{
            checked_count++;
            $from_time.removeClass("is-invalid");
          }
        }
        else{
          $date.removeClass("is-invalid");
          $from_time.removeClass("is-invalid");
        }
      });
      if(error != ""){
        error = '<div class="alert alert-danger">' + error + "</div>";
        $(this).before(error);
        scrollTo($(".alert-danger"));
        return false;
      }
      if(!checked_count){
        $(this).before('<div class="alert alert-danger">يجب إختيار توقيت واحد على الأقل.</div>');
        scrollTo($(".alert-danger"));
        return false;
      }
      checked_count = 0;
      if(!$("select[name='places[]']").val().length){
        $(this).before('<div class="alert alert-danger">يجب إختيار مكان واحد على الأقل.</div>');
        scrollTo($(".alert-danger"));
        return false;
      }
    }
    return true;
  });

  function scrollTo($element){
    $([document.documentElement, document.body]).animate({
        scrollTop: $element.offset().top - $(".navbar").height()
    }, 1000);
  }
});
