$(document).ready(function(){
  $("input[name=search]").keyup(function(){
    let $table = $(this).next(".reservations-table");
    if(!$table.length){
      return;
    }

    let query = $(this).val();
    if(query.length == 0){
      $table.find("tr.d-none").addClass("d-table-row");
    }
    else{
      $table.find("tbody tr").map(function(){
        $(this).children("td:not(:last-child)").each(function(){
          if($(this).text().search(query) !== -1){
            $(this).parent("tr").removeClass("d-none").addClass("d-table-row");
            return false;
          }
          else{
            $(this).parent("tr").addClass("d-none").removeClass("d-table-row");
          }
        })
      });
    }
  });
});
