$(document).ready(function(){
  $(".iziModal").iziModal();
  let deleteId = null, deleteType = null;
  $(".delete-btn").click(function(){
    if($(this).hasClass("delete-floor-btn")){
      deleteType = 'floor';
    }
    else if($(this).hasClass("delete-room-btn")){
      deleteType = 'room';
    }
    deleteId = $(this).attr('id');
  });

  $("#deleteModal .btn-danger").click(function(){
    if(deleteType == "floor"){
      window.location.href = '/delete-floor/' + deleteId;
    }
    else if(deleteType == "room"){
      window.location.href = '/delete-room/' + deleteId;
    }
  })
});
