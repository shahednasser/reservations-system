$(document).ready(function(){
  let maxHeight = $(".card:not(.full-card)").css("max-height");
  $(".card").map(function(){
    if($(this)[0].scrollHeight <= parseFloat(maxHeight)){
      $(this).find(".show-more").remove();
      $(this).find(".card-body").css("margin-bottom", 0);
    }
  });
  $(".show-more").click(function(){
    const $parent = $(this).parent();
    if(parseFloat($parent.css("max-height")) != $parent[0].scrollHeight){
      $parent.animate({maxHeight: $(this).parent()[0].scrollHeight + "px"});
      $parent.addClass("expanded");
      $(this).text("أظهر أقل");
    }
    else{
      $(this).text("أظهر المزيد");
      $parent.removeClass("expanded");
      $parent.animate({maxHeight: maxHeight});
    }
  });

  $(".time-input").keyup(function(e){
    const val = $(this).val();
    let startInd = 0, endInd = 0;
    let code = e.keyCode ? e.keyCode : e.which;
    if(code == 39 && $(this)[0].selectionStart == 3){
      $(this)[0].setSelectionRange(3,5);
      return;
    }
    if(!/^([01][0-9]|2[0-3]):([0-5][0-9])$/.test(val)){
      let arr = val.split(":");
      if(arr.length != 2){
        $(this).val("00:00");
        endInd = 2;
      }
      else {
        let str = "";
        if(isNaN(arr[0])){
          str += "00:";
          endInd = 2;
        }
        else if(arr[0].length > 2 || arr[0].length == 0) {
          str += "00:";
          endInd = 2;
        }
        else{
          let hours = parseInt(arr[0]);
          if(hours < 0 || hours > 23){
            str += "00:";
            endInd = 2;
          }
        }
        if(isNaN(arr[1])){
          str += str == "" ? arr[0] + ":00" : "00";
          startInd = 3;
          endInd = 5;
        }
        else if(arr[1].length > 2 || arr[1].length == 0){
          str += str == "" ? arr[0] + ":00" : "00";
          startInd = 3;
          endInd = 5;
        }
        else{
          let minutes = parseInt(arr[1]);
          if(minutes < 0 || minutes > 59){
            str += str == "" ? arr[0] + ":00" : "00";
            startInd = 3;
            endInd = 5;
          }
          else if(str != ""){
            str += arr[1];
          }
        }
        if(str != ""){
          $(this).val(str);
          $(this)[0].setSelectionRange(startInd, endInd);
        }
      }
    }
  });

  $(".time-input").blur(function(){
    let arr = $(this).val().split(":");
    let str = arr[0].length == 1 ? "0" + arr[0] : arr[0];
    str += ":";
    str += arr[1].length == 1 ? "0" + arr[1] : arr[1];
    $(this).val(str);
  });

  $(window).keyup(function(e){
    let code = e.keyCode ? e.keyCode : e.which;
    if (code == 9 && $(".time-input:focus").length) {
      $(".time-input:focus")[0].setSelectionRange(0,2);
    }
  });

  $(".search-form input[type=search], .search-form button[type=submit]").focus(function(){
    $(".search-form").animate({
      opacity: 1
    }, 200);
  });

  $(".search-form input[type=search], .search-form button[type=submit]").blur(function(){
    if(!$(".search-form input[type=search]").is(":focus") && !$(".search-form button[type=submit]").is(":focus")){
      $(".search-form").animate({
        opacity: .5
      }, 200);
    }
  });

  $(window).resize(function(){
    if($(this).width() <= $(".sidebar").width()){
      $(".sidebar").width($(this).width());
    }
    else{
      $(".sidebar").width(getDefaultFontSize()[1] * 20);
    }
  });

  function getDefaultFontSize(pa){
     pa= pa || document.body;
     var who= document.createElement('div');

     who.style.cssText='display:inline-block; padding:0; line-height:1; position:absolute; visibility:hidden; font-size:1em';

     who.appendChild(document.createTextNode('M'));
     pa.appendChild(who);
     var fs= [who.offsetWidth, who.offsetHeight];
     pa.removeChild(who);
     return fs;
    }

    window.between = function(from_time_1, from_time_2, to_time_1, to_time_2){
        let moment_tf1 = moment(from_time_1, "HH:mm"),
            moment_tt1 = moment(to_time_1, "HH:mm"),
            moment_tf2 = moment(from_time_2, "HH:mm"),
            moment_tt2 = moment(to_time_2, "HH:mm");
        if(to_time_1 === "00:00") {
            moment_tt1 = moment("23:59", "HH:mm");
        }
        if(to_time_2 === "00:00") {
            moment_tt2 = moment("23:59", "HH:mm");
        }
        return moment_tf1.isBetween(moment_tf2, moment_tt2, null,
            "[)") || moment_tt1.isBetween(moment_tf2, moment_tt2, null, "(]") ||
            moment_tf2.isBetween(moment_tf1, moment_tt1, null, "[)") ||
            moment_tt2.isBetween(moment_tf1, moment_tt1, null, "(]")
    };

    window.disableLoadingButton = function ($button) {
        $button.prop('disabled', true);
        $button.find(".loading").removeClass("d-none");
    };

    window.enableLoadingButton = function ($button) {
        $button.prop('disabled', false);
        $button.find(".loading").addClass("d-none");
    }
});
