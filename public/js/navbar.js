$(document).ready(function(){
  const path = location.pathname;
  let n = null;

  switch (true) {
    case path == "/":
      n = 1;
      break;
    case path.search('/help') != -1:
        if(!$(".nav-item a[href*='/all-reservation']").length){
            n = 6;
        }
        else{
            n = 7;
        }
        break;
    case path.search("/reservation") != -1:
    case path.search("/new-reservations") != -1 || path.search("/my-reservations") != -1:
      n = 3;
      break;
    case path.search("/all-reservations") != -1:
      n = 4;
      break;
    case path.search("/calendar") != -1:
      n=1;
      break;
    case path.search("/add-reservation") != -1 || path.search('/admin-add-reservation') != -1:
      if(!$(".nav-item a[href*='/all-reservation']").length){
        n = 4;
      }
      else{
        n = 5;
      }
      break;
    case path.search('/view-account') != -1 || path.search('/view-users') != -1 || path.search('/add-user') != -1:
      if(!$(".nav-item a[href*='/all-reservation']").length){
        n = 6;
      }
      else{
        n = 7;
      }
      break;
    case path.search('/weekly-calendar') != -1:
        n = 2;
        break;
    case path.search("/notfound") != -1:
      n = -1;
      break;
    default:
      return;
  }
  $(".nav-item:nth-child(" + n + ")").addClass("active");

  $('nav [data-toggle="tooltip"]').tooltip();
});
