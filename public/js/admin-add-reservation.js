$(document).ready(function(){
    $(".iziModal").iziModal();
  let timeValidated = false, fields = {}, $result = $("#result"),
      $documentTop = $([document.documentElement, document.body]),
      nbDays = $("input[name*='dates_']:checked").length,
      unchanged = true;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr('content')
    }
  });

  if($("input[name*=dates_]:checked").length){
    unchanged = false;
  }

  $(window).on('load', function(){
    $(".req-num").map(function(){
      checkReqName($(this));
    });
  });

  $("#checkReservations").click(function(){
    disableLoadingButton($(this));
    const self = this;
    $result.html("");
    nbDays = 0;
    //validate data
    if(!validateDateTime()){
      disableLoadingButton($(this));
      return;
    }
    $(".timing-table").find('tr').removeClass('bg-danger').removeClass('text-white');
    let locationArr = window.location.pathname.split("/"),
        data = {fields};
    if(locationArr.length > 2){
      data.id = locationArr[locationArr.length - 1];
    }
    $.post("/admin-check-reservations", data, function(data){
      if(data.error){
        $result.html('<div class="alert alert-danger">حدث خطأ. الرجاء اعادة المحاولة في وقت لاحق.</div>');
      }
      else{
        let reservations = data.reservations,
            keys = Object.keys(reservations),
            resultStr = "";
        for(let i = 0; i < 3; i++){
          if(keys.includes(i.toString())){
            let date = fields["date_" + i],
                from_time = fields["from_time_" + i],
                to_time = fields["to_time_" + i];
            let list = reservations[i];
            if(list.hasOwnProperty("reservations")){
              //temp or long reservation
                $("input[name='dates_" + i + "']").parents('tr').addClass('bg-danger').addClass('text-white');
                let reservations = list["reservations"];
              for (let reservation of reservations) {
                if(resultStr == ""){
                  resultStr = "النشاطات التالية تتعارض مع هذا النشاط: <br /><table class='table" +
                              " table-bordered'><thead><tr><th>الإسم</th><th>التوقيت</th><th>يتعارض مع</th></tr></thead><tbody>";
                }
                if(reservation.hasOwnProperty('long_reservation_dates')){
                  //long reservation
                  resultStr += '<tr><td rowspan="' + reservation.long_reservation_dates.length + '"><a href="/show-reservation/' +
                                reservation.reservation.id + '">' + reservation.reservation.event_name + '</a>' + "<hr />من " + format_date(reservation.from_date) +
                                " الى " + format_date(reservation.to_date) + "</td>";
                  let first = true;
                  for(let lrd of reservation.long_reservation_dates){
                    if(!first){
                      resultStr += "<tr>";
                    }
                    resultStr += '<td>' + format_time_without_seconds(lrd.from_time) + ' - ' +
                                  format_time_without_seconds(lrd.to_time) + "</td>";
                    if(first){
                      resultStr += "<td rowspan='" + reservation.long_reservation_dates.length + "'>" + format_date(date) +
                      "<hr />" + from_time + " - " + to_time +
                      "</td>";
                      first = false;
                    }
                    resultStr += "</tr>";
                  }
                }
                else{
                  //temp reservation
                  resultStr += '<tr><td rowspan="' + reservation.temporary_reservation_dates.length + '"><a href="/show-reservation/' +
                                reservation.reservation.id + '">' + reservation.reservation.event_name + '</a></td>';
                  let first = true;
                  for(let trd of reservation.temporary_reservation_dates){
                    if(!first){
                      resultStr += "<tr>";
                    }
                    resultStr += '<td>' + format_date(trd.date) + '<hr />' + format_time_without_seconds(trd.from_time) + ' - ' +
                                  format_time_without_seconds(trd.to_time) + '</td>';
                    if(first){
                      resultStr += "<td rowspan='" + reservation.temporary_reservation_dates.length + "'>" + format_date(date) +
                      "<hr />" + from_time + " - " + to_time +
                      "</td>";
                      first = false;
                    }
                    resultStr += "</tr>";
                  }
                }
              }
            }
            else{
                $("input[name='dates_" + i + "']").parents('tr').removeClass('bg-danger').removeClass('text-white');
            }
            if(list.hasOwnProperty("manual_reservations")){
              //manual reservation
                $("input[name='dates_" + i + "']").parents('tr').addClass('bg-danger').addClass('text-white');
                let manualReservations = list["manual_reservations"];
              for (let reservation of manualReservations) {
                if(resultStr == ""){
                  resultStr = "النشاطات التالية تتعارض مع هذا النشاط: <br /><table class='table" +
                              " table-bordered'><thead><tr><th>الإسم</th><th>التوقيت</th><th>يتعارض مع</th></tr></thead><tbody>";
                }
                resultStr += '<tr><td rowspan="' + reservation.manual_reservations_dates.length + '">' +
                              '<a href="/view-admin-reservation/' + reservation.id + '">' + reservation.event_type +
                              "</a></td>";
                let first = true;
                for(let mrd of reservation.manual_reservations_dates){
                  if(!first){
                    resultStr += "<tr>";
                  }
                  resultStr += '<td>' + format_date(mrd.date) + '<hr />' + format_time_without_seconds(mrd.from_time) + ' - ' +
                                format_time_without_seconds(mrd.to_time) + '</td>';
                  if(first){
                    resultStr += "<td rowspan='" + reservation.manual_reservations_dates.length + "'>" + format_date(date) +
                    "<hr />" + from_time + " - " + to_time +
                    "</td>";
                    first = false;
                  }
                  resultStr += "</tr>";
                }
              }
            }
            else if(!list.hasOwnProperty('reservations')){
                $("input[name='dates_" + i + "']").parents('tr').removeClass('bg-danger').removeClass('text-white');
            }
          }
          else{
            $("input[name='dates_" + i + "']").parents('tr').removeClass('bg-danger').removeClass('text-white');
          }
        }

        if(resultStr != ""){
          $("select[name*='place_requirment_dates_']").map(function(){
            $(this).children().remove();
          });
          resultStr += '</tbody</table>';
          $result.html(resultStr);
          timeValidated = false;
          $("button[type=submit]").prop('disabled', false);
          $("input[name*='equipment_nb_1'], input[name*='equipment_nb_2'], input[name*='equipment_nb_1']").prop('disabled', true);
        }
        else{
          $result.html('<div class="mb-3">لا يوجد أي حجوزات متعارضة مع هذه الأوقات</div>');
          timeValidated = true;
          $("button[type=submit]").prop('disabled', false);
          if(!unchanged){
            $("select[name*='place_requirment_dates_']").map(function(){
              $(this).children().remove();
            });
            $("select[name*='place_requirment_dates_']").map(function(){
              for(let j = 0; j < 3; j++){
                if(fields.hasOwnProperty('dates_' + j)){
                  $(this).append('<option value="' + j + '">' + format_date(fields["date_" + j]) + '</option>');
                }
              }
            });
          }
          $("input[name*='dates_']").map(function(){
            if($(this).is(":checked")){
              nbDays++;
            }
          });
          $("input[name*='equipment_nb_']").map(function(){
            let nameArr = $(this).attr("name").split("_"),
                nb = nameArr[2];
            if(nb > nbDays){
              $(this).prop("disabled", true);
            }
            else{
              $(this).prop("disabled", false);
            }
          });
        }
      }
      unchanged = true;
    })
    .fail(function(){
      $result.html('<div class="alert alert-danger">حدث خطأ. الرجاء اعادة المحاولة في وقت لاحق.</div>');
    })
        .always(function () {
          console.log("always");
            enableLoadingButton($(self));
        });
  });

  $("input[name*='dates_'], input[name*='date_']:not(input[name*=date_created]), input[name*='from_time_'], input[name*='to_time_']").change(function(){
    timeValidated = false;
    unchanged = false;
  });

  $("input[name*='dates_']").change(function(){
    let arr = $(this).attr("name").split("_"),
        daynb = arr[1],
        type=arr[0];
    if($(this).is(":checked")){
      setDisabledRow(daynb, type, false);
    }
    else {
      setDisabledRow(daynb, type, true);
    }
  });

  $("body").on('input', ".req-num", function(){
    checkReqName($(this));
  });

  $("form").submit(function(){
    //validate
    if(!timeValidated){
      $(this).prepend('<div class="alert alert-danger">يجب التأكد من الوقت</div>');
      scrollToTop($(this));
      return false;
    }
    let self = this, hasError = false;
    //validate equipment
    $("input[name*='equipment_nb_']").each(function(){
      let nameArr = $(this).attr("name").split("_"),
          nb = nameArr[2];
      if(nb > nbDays && $(this).val() != 0 && !$(this).prop("disabled")){
        $(this).addClass("is-invalid");
        $(self).prepend('<div class="alert alert-danger">لم تختر هذا اليوم.</div>');
        scrollTop($(this));
        hasError = true;
        return false;
      }
      else{
        $(self).removeClass('is-invalid');
      }
    });
    return !hasError;
  });

  $("select[name*='place_requirment_dates_']").change(function(){
    let name = $(this).attr('name'),
        nameArr = name.split("_"),
        id = nameArr[nameArr.length - 1],
        limit = $("input[name='place_requirment_" + id + "']").val();
    changeSelectionLimit($(this), limit);
  });

  $("input[name=discount]").on("input", function(){
    let value = $(this).val();
    if(isNaN(value)){
      $(this).val(0);
    }
    else{
      calculateDiscount(value);
    }
  });

  function calculateDiscount(value){
    let total = 0;
    $(".grand-req-total").map(function(){
      total += parseFloat($(this).text());
    });
    $("#grandTotal").text(total - value);
  }

  function checkReqName($element) {
    let nb = $element.val();
    if(nb < 0 || nb > nbDays){
      $element.val("0");
      nb = 0;
    }
    let name = $element.attr('name'),
        nameArr = name.split('_'),
        id = nameArr[nameArr.length - 1],
        $parent = $element.parents('.collapse'),
        $parentPrev = $parent.prev(),
        $singlePrice = $parent.find('#requirmentSinglePrice_' + id),
        $reqTotalPrice = $parent.find('#requirmentTotalPrice_' + id),
        $reqGrandTotal = $parentPrev.find('.grand-req-total'),
        $grandTotal = $("#grandTotal");

    //change req total price
    $reqTotalPrice.text(parseFloat($singlePrice.text() ? $singlePrice.text() : $singlePrice.val()) * nb);
    let total = 0;
    $parent.find("span[id*='requirmentTotalPrice_']").map(function(){
      total += parseFloat($(this).text());
    });
    $reqGrandTotal.text(total);
    total = 0;
    $(".grand-req-total").map(function(){
      total += parseFloat($(this).text());
    });
    $grandTotal.text(total);
    if(!unchanged){
      let $select = $("select[name='place_requirment_dates_" + id + "[]']");
      changeSelectionLimit($select, nb);
    }
    calculateDiscount($("input[name=discount]").val());
  }

  function changeSelectionLimit($element, limit){
    let valueArr = $element.val();
    if(limit == 0){
      $element.find(":selected").prop("selected", false);
    }
    if(valueArr.length > limit){
      $element.find(":selected").map(function(index, item){
        if(index > limit - 1){
          $(this).prop("selected", false);
        }
      });
    }
  }

  function scrollToTop($element){
    $documentTop.animate({
      scrollTop: $element.offset().top
    }, 1000);
  }

  function setDisabledRow(daynb, type, value){
    $("input[name=from_time_" + daynb + "]").prop("disabled", value);
    if(value){
      //add disabled class
      $("input[name=from_time_" + daynb + "]").next().addClass("disabled");
    } else {
      $("input[name=from_time_" + daynb + "]").next().removeClass("disabled");
    }
    $("input[name=to_time_" + daynb + "]").prop("disabled", value);
    if(value){
      //add disabled class
      $("input[name=to_time_" + daynb + "]").next().addClass("disabled");
    } else {
      $("input[name=to_time_" + daynb + "]").next().removeClass("disabled");
    }
    $("input[name=date_" + daynb + "]").next(".date-picker").prop("disabled", value);
    $("input[name=men_" + daynb + "]").prop("disabled", value);
    $("input[name=women_" + daynb + "]").prop("disabled", value);
  }

  function validateDateTime(){
    fields = {};
    let found = false;
    for(let i = 0; i < 3; i++){
      if($("input[name=dates_" + i + "]").is(":checked")){
        let $date = $("input[name=date_" + i + "]"),
            $from_time = $("input[name=from_time_" + i + "]"),
            $to_time = $("input[name=to_time_" + i + "]");

        //validate date
        if(!validateInput($date.val(), $date.next('.date-picker'), "يجب تحديد التاريخ")){
          return false;
        }

        //validate time
        if(!validateInput(validateTime($from_time, $to_time), $from_time, "يجب أن يكون وقت البداية قبل وقت النهاية")){
          return false;
        }

        fields["dates_" + i] = true;
        fields["date_" + i] = $date.val();
        fields["from_time_" + i] = $from_time.val();
        fields["to_time_" + i] = $to_time.val();
        found = true;
      }
    }
    if(!found){
      $result.html('<div class="alert alert-danger">يجب إختيار يوم واحد على الأقل.</div>');
      return false;
    }
    return true;
  }

  function validateTime($from_time, $to_time){
    return $from_time.val() < $to_time.val();
  }
  function format_date(date){
    let d = new Date(date.replace(/-/g, '/'));
    return d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear();
  }

  function format_time_without_seconds(time){
    return time.substring(0, time.lastIndexOf(":"));
  }

  function validateInput(condition, element, message){
      if(!condition){
          if(!element.next(".invalid-feedback").length){
              element.addClass("is-invalid");
              element.after(`<span class="invalid-feedback">${message}</span>`);
          }
          return false;
      }
      element.removeClass("is-invalid");
      element.next(".invalid-feedback").remove();
      return true;
  }
});
