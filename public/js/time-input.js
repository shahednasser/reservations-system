$(document).ready(function(){
  let timeOptions = {
    format: 'HH:mm',
    text: {
      title: 'إختر الوقت',
      cancel: 'إلغاء',
      confirm: 'تأكيد',
    },
    controls: true
  },
  dateOptions = {
    format: 'DD-MM-YYYY',
    text: {
      title: 'إختر التاريخ',
      cancel: 'إلغاء',
      confirm: 'تأكيد',
    },
    controls: true
  },
  pickers = [],
  isSmallScreen = $(window).width() <= 768;
  if(!isSmallScreen){
    timeOptions["inline"] = true;
    dateOptions["inline"] = true;
  }
  $("input.input-time, input.input-date").map(function(){
    let element = $(this),
        extraOptions = {date: element.val()},
        options = {};
    if($(this).hasClass("input-time")){
      options = timeOptions;
    } else {
      options = dateOptions;
    }
    if($(window).width() > 768){
      let inlineContainer = $(this).next();
      inlineContainer.removeClass("d-none");
      element.addClass("d-none");
      extraOptions["container"] = inlineContainer.get(0);
    }
    pickers.push({picker: new Picker(element.get(0), Object.assign({}, options, extraOptions)), element});
  });

  $(window).on('resize', function(){
    let smallScreen = $(this).width() <= 768;
    if(isSmallScreen === smallScreen){
      return;
    }
    isSmallScreen = !isSmallScreen;
    for(let i = 0; i < pickers.length; i++){
      let date = pickers[i].picker.getDate(),
          options = {};
          if(pickers[i].element.hasClass('input-time')){
            options = timeOptions;
          } else {
            options = dateOptions;
          }
      pickers[i].picker.destroy();
      if(smallScreen){
        pickers[i].element.removeClass("d-none");
        pickers[i].element.next().addClass("d-none");
        pickers[i].picker = new Picker(pickers[i].element.get(0), Object.assign({}, options, {date}));
      } else {
        pickers[i].element.addClass("d-none");
        pickers[i].element.next().removeClass("d-none");
        pickers[i].picker = new Picker(pickers[i].element.get(0),
          Object.assign({}, options, {date, inline: true, container: pickers[i].element.next().get(0)}));
      }
    }
  })
});
