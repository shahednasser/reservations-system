$(document).ready(function(){
  let $sidebar = $(".sidebar"),
      $refreshBtn = $(".refresh"),
      $toolbar = $(".sidebar .toolbar"),
      seconds = 900000,
      intervalId = setInterval(getNewNotifications, seconds),
      $notificationsBtn = $("#notificationsBtn"),
      $redDot = $(".red-dot"),
      $notifContainer = $(".notification-container");
  let sound = new Howl({
    src: ['/assets/notification.mp3', '/assets/notification.ogg']
  });
  $refreshBtn.addClass("spinAnimation");
  let timestamp = parseInt((new Date()).getTime()/1000);
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr('content'),
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    }
  });
  $.get('/notifications', function(response){
    appendNotifications(response);
  }).always(function(){
    $refreshBtn.removeClass("spinAnimation");
  });

  $notificationsBtn.click(function(){
    $notificationsBtn.data("clicked", true);
    $notifContainer.removeClass("animated swing");
    if($sidebar.hasClass("d-none")){
      $sidebar.removeClass("d-none");
    }
    let hasClass = $sidebar.hasClass("slideInLeft");
    $sidebar.toggleClass("slideOutLeft").toggleClass("slideInLeft");
    if(hasClass && $(".unread-notification").length){
      let notifications = [];
      $(".unread-notification").map(function(item){
        notifications.push($(this).attr('id'));
      });

      $.post('/read-notifications', {notifications}, function(response){
        $(".unread-notification").removeClass("unread-notification");
        $redDot.addClass("d-none");
      })
        .fail(function(err){
          console.log("err", err);
        });
    }
    else if(!hasClass){
      $refreshBtn.trigger("click");
    }

  });

  $sidebar.click(function(){
    $sidebar.data('clicked', true);
  });

  $(document).click(function(){
    if(!$sidebar.data('clicked')){
      if($sidebar.hasClass('slideInLeft')){
        if(!$notificationsBtn.data("clicked")){
          $notificationsBtn.trigger("click");
        }
        else{
          $notificationsBtn.data("clicked", false);
        }
      }
    }
    else{
      $sidebar.data('clicked', false);
    }
  });

  $refreshBtn.click(function(){
    if(!$(this).data("clicked")){
      $(this).addClass("spinAnimation");
      $(this).data("clicked", true);
      clearInterval(intervalId);
      let self = this;
      $.get('/newNotifications/' + timestamp, function(response){
        prependNotifications(response);
        timestamp = parseInt((new Date()).getTime()/1000);
        intervalId = setInterval(getNewNotifications, seconds);
        $(self).removeClass("spinAnimation");
      }).fail(function(err){
        console.log(err);
      }).always(function(){
        $(self).data("clicked", false);
      });
    }
  });

  function appendNotifications(notifications){
    for (notification of notifications) {
      if(!notification.read_at){
        if(!$notifContainer.hasClass("swing")){
          $redDot.removeClass("d-none");
          $notifContainer.addClass("animated swing");
          sound.play();
        }
      }
      $sidebar.append('<div class="border-bottom p-2' + (notification.read_at ? '' : ' unread-notification') + '"' +
                          'id=' + notification.id + '><a href="' + notification.data.url + '">' + notification.data.text + '</a>' +
                          '<div class="text-left" style="direction: ltr;">' +
                          '<small>' + format_datetime(notification.created_at) + '</small>' +
                          '</div>' +
                          '</div>');
    }
  }

  function prependNotifications(notifications){
    for (notification of notifications) {
      if(!notification.read_at){
        if(!$notifContainer.hasClass("swing")){
          $redDot.removeClass("d-none");
          $notifContainer.addClass("animated swing");
          sound.play();
        }
      }
      $toolbar.after('<div class="border-bottom p-2' + (notification.read_at ? '' : ' unread-notification') + '"' +
                          'id=' + notification.id + '><a href="' + notification.data.url + '">' + notification.data.text + '</a>' +
                          '<div class="text-left" style="direction: ltr;">' +
                          '<small>' + format_datetime(notification.created_at) + '</small>' +
                          '</div>' +
                          '</div>');
    }
  }

  function format_datetime(date){
    var d = new Date(date.replace(/-/g, '/'));
    return d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear() + " " + d.getHours() + ":" + d.getMinutes();
  }

  function getNewNotifications(){
    $.get("/newNotifications/" + timestamp, function(response){
      prependNotifications(response);
      timestamp = parseInt((new Date()).getTime()/1000);
    }).fail(function(err){
      console.log(err);
    });
  }
});
