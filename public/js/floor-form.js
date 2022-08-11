$(document).ready(function(){
  let i = $(".room-row").length + 1;
  $("#addRoomsBtn").click(function(){
    $("#roomsDiv").append('<div class="row">' +
      '<div class="col">' +
        '<div class="form-group">' +
          '<label for="room_name_' + i + '">الإسم</label>' +
          '<input name="room_name_' + i + '" type="text" class="form-control" />' +
        '</div>' +
      '</div>' +
      '<div class="col">' +
        '<div class="form-group">' +
          '<label for="room_number_' + i + '">رقم الغرفة</label>' +
          '<input name="room_number_' + i + '" type="number" class="form-control" />' +
        '</div>' +
      '</div>' +
    '</div>');
    i++;
  });

  $(".delete-icon").click(function(){
    $(this).parents(".room-row").remove();
    i--;
  });
});
