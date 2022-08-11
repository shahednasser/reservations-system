$(document).ready(function(){
  $("select[name=filter]").change(function(){
    const value = $(this).val();
    switch (value) {
      case 'all':
        $(".reservations-table tr").addClass("d-table-row").removeClass("d-none");
        break;
      case 'long':
        $(".reservations-table .long").addClass("d-table-row").removeClass("d-none");
        $(".reservations-table .temp").addClass("d-none").removeClass("d-table-row");
        $(".reservations-table .manual").addClass("d-none").removeClass("d-table-row");
        break;
      case 'temp':
        $(".reservations-table .temp").addClass("d-table-row").removeClass("d-none");
        $(".reservations-table .long").addClass("d-none").removeClass("d-table-row");
        $(".reservations-table .manual").addClass("d-none").removeClass("d-table-row");
        break;
      case 'manual':
        $(".reservations-table .manual").addClass("d-table-row").removeClass("d-none");
        $(".reservations-table .long").addClass("d-none").removeClass("d-table-row");
        $(".reservations-table .temp").addClass("d-none").removeClass("d-table-row");
        break;
    }
  });
});
