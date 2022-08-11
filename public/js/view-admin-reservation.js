$(document).ready(function(){
  $(".iziModal").iziModal();
  $(".req-num").map(function(){
    checkReqName($(this));
  });

  function checkReqName($element) {
    let nb = $element.text();
    let name = $element.attr('id'),
        nameArr = name.split('_'),
        id = nameArr[nameArr.length - 1],
        $parent = $element.parents('.collapse'),
        $parentPrev = $parent.prev(),
        $singlePrice = $parent.find('#requirmentSinglePrice_' + id),
        $reqTotalPrice = $parent.find('#requirmentTotalPrice_' + id),
        $reqGrandTotal = $parentPrev.find('.grand-req-total'),
        $grandTotal = $("#grandTotal"),
        discount = $("#discount").text();

    //change req total price
    $reqTotalPrice.text(parseFloat($singlePrice.text()) * nb);
    let total = 0;
    $parent.find("span[id*='requirmentTotalPrice_']").map(function(){
      total += parseFloat($(this).text());
    });
    $reqGrandTotal.text(total);
    total = 0;
    $(".grand-req-total").map(function(){
      total += parseFloat($(this).text());
    });
    $grandTotal.text(total - discount);
  }
});
