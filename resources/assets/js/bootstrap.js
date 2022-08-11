import Echo from "laravel-echo"
let $ = require('jquery');
window.Pusher = require('pusher-js');

$(document).ready(function(){
  window.Echo = new Echo({
      broadcaster: 'pusher',
      key: process.env.MIX_PUSHER_APP_KEY,
      encrypted: true,
      cluster: process.env.MIX_PUSHER_APP_CLUSTER
  });
  let $sidebar = $(".sidebar"),
      $notificationsBtn = $("#notificationsBtn"),
      $redDot = $(".red-dot"),
      $notifContainer = $(".notification-container");
  let sound = new Howl({
    src: ['/assets/notification.mp3', '/assets/notification.ogg']
  });

  let userId = document.getElementById("id").innerHTML;
  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr('content'),
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
    }
  });
  $.get('/notifications', function(response){
    appendNotifications(response);
  }).fail(function(error){
    console.log("error", error);
  })
  window.Echo.private('App.User.' + userId)
      .notification((e) => {
          prependNotifications([e]);
      });

  $notificationsBtn.click(function(){
    $notificationsBtn.data("clicked", true);
    $notifContainer.removeClass("animated swing");
    if(!$redDot.hasClass("d-none")){
      $redDot.removeClass("d-none");
    }
    if($sidebar.hasClass("d-none")){
      $sidebar.removeClass("d-none");
    }
    $sidebar.toggleClass("slideOutLeft").toggleClass("slideInLeft");
    if($(".unread-notification").length){
      setTimeout(function(){
          $.get('/read-notifications', function(response){
              $(".unread-notification").removeClass("unread-notification");
              $redDot.addClass("d-none");
          })
              .fail(function(err){
                  console.log("err", err);
              });
      }, 1000);
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

  function appendNotifications(notifications){
    for (let notification of notifications) {
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
    for (let notification of notifications) {
      if(!$notifContainer.hasClass("swing")){
        $redDot.removeClass("d-none");
        $notifContainer.addClass("animated swing");
      }
      sound.play();
      $sidebar.prepend('<div class="border-bottom p-2 unread-notification">' +
                          '<a href="' + notification.url + '">' + notification.text + '</a>' +
                          '<div class="text-left" style="direction: ltr;">' +
                          '<small>' + format_datetime(notification.date) + '</small>' +
                          '</div>' +
                          '</div>');
      let data = {title: 'إشعار جديد', message: notification.text, url: notification.url};
      OneSignal.sendSelfNotification(
        /* Title (defaults if unset) */
        data.title,
        /* Message (defaults if unset) */
        data.message,
         /* URL (defaults if unset) */
        data.url
      );
    }
  }

  function format_datetime(date){
    var d = new Date(date.replace(/-/g, '/'));
    return d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear() + " " + d.getHours() + ":" + d.getMinutes();
  }

});
