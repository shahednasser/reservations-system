$(document).ready(function(){
  $("#printBtn").click(function(){
    $(this).prop("disabled", true);
    $(this).find(".loading").removeClass("d-none");
    let id = "";
    if($("#resId").length){
      id = "/" + $("#resId").text();
    }
    console.log("");
    let url = ($(this).hasClass("long-res") ? "/generate-long-reservation-pdf" : ($(this).hasClass("temp-res") ?
                "/generate-temp-reservation-pdf" : '/generate-manual-reservation-pdf'));
    if(location.pathname.indexOf("view") !== -1 || location.pathname.indexOf("show") !== -1){
      url += id;
    }
    let self = this;
    printJS({printable: url, type: 'pdf', onLoadingEnd: function(){
      $(self).prop("disabled", false);
      $(self).find(".loading").addClass("d-none");
    }});

  });
});
