$(document).ready(function () {
    $("#rejectModal").iziModal();
    $("#deleteModal").iziModal();
    $("#pauseModal").iziModal();
    $("#deletePauseModal").iziModal();

    let form = $("#reservationsForm");

    $("#massReject").on('click', function () {
        form.attr('action', '/mass-reject');
        form.submit();
    });

    $("#massDelete").on('click', function () {
        form.attr('action', '/mass-delete');
        form.submit();
    });

    $("#massDeletePaused").on('click', function () {
        form.attr('action', '/mass-delete-paused-reservations');
        form.submit();
    });

    $("#massPause").on('click', function () {
        //validate dates
        let from_date_element = $("input[name=pause_from_date]"),
            to_date_element = $("input[name=pause_to_date]"),
            from_date = from_date_element.val(),
            to_date = to_date_element.val();
        if(!validatePausedDates(from_date, to_date, from_date_element, to_date_element)){
            return;
        }

        //validate places
        let places = $("select[name='pausedReservationPlaces[]']");
        if(places.length && !validatePlaces(places)){
            return;
        }

        //append from_date and to_date to form
        form.append('<input type="hidden" name="pause_from_date" value="' + from_date + '" />');
        form.append('<input type="hidden" name="pause_to_date" value="' + to_date + '" />');
        if(places.length){
            appendPlacesToForm(form, places);
        }
        form.attr('action', '/mass-pause');
        form.submit();
    });

    $(".pause-reservation-button").click(function(){
        //validate dates 
        let from_date_element = $("#pauseModal input[name=pause_from_date]"),
            to_date_element = $("#pauseModal input[name=pause_to_date]"),
            from_date = from_date_element.val(),
            to_date = to_date_element.val();
        if(!validatePausedDates(from_date, to_date, from_date_element, to_date_element)){
            return;
        }

        //validate places
        let places = $("select[name='pausedReservationPlaces[]']");
        if(places.length && !validatePlaces(places)){
            return;
        }

        //get reservation id from button id 
        let reservationId = getReservationIdFromButton($(this));

        //append form to body and submit
        let form = $('<form method="POST" action="/pause-reservation" class="d-none">');
        form.appendTo("body");
        form.append('<input type="hidden" name="_token" value="' + $("meta[name=csrf-token]").attr('content') + '">');
        form.append('<input type="hidden" name="reservation_id" value="' + reservationId + '">');
        form.append('<input type="date" class="d-none" name="from_date" value="' + from_date + '">');
        form.append('<input type="date" class="d-none" name="to_date" value="' + to_date + '">');
        if(places.length){
            appendPlacesToForm(form, places);
        }
        form.submit();
    });

    $(".edit-pause-btn").click(function(){
        //validate dates 
        let from_date_element = $("#pauseModal input[name=pause_from_date]"),
            to_date_element = $("#pauseModal input[name=pause_to_date]"),
            from_date = from_date_element.val(),
            to_date = to_date_element.val();
        if(!validatePausedDates(from_date, to_date, from_date_element, to_date_element)){
            return;
        }

        //validate places
        let places = $("select[name='pausedReservationPlaces[]']");
        if(places.length && !validatePlaces(places)){
            return;
        }

        //get reservation id from button id 
        let reservationId = getReservationIdFromButton($(this));

        //append form to body and submit
        let form = $('<form method="POST" action="/edit-paused-reservation" class="d-none">');
        form.appendTo("body");
        form.append('<input type="hidden" name="_token" value="' + $("meta[name=csrf-token]").attr('content') + '">');
        form.append('<input type="hidden" name="reservation_id" value="' + reservationId + '">');
        form.append('<input type="date" class="d-none" name="from_date" value="' + from_date + '">');
        form.append('<input type="date" class="d-none" name="to_date" value="' + to_date + '">');
        if(places.length){
            appendPlacesToForm(form, places);
        }
        form.submit();
    });

    $(".delete-pause-reservation").click(function(){
        let reservationId = getReservationIdFromButton($(this)),
            form = $('<form method="POST" action="/delete-pause-reservation" class="d-none">');
        form.appendTo("body");
        form.append('<input type="hidden" name="_token" value="' + $("meta[name=csrf-token]").attr('content') + '">');
        form.append('<input type="hidden" name="reservation_id" value="' + reservationId + '">');
        form.submit();
    });

    $("#checkAll").change(function(){
        let checked = $(this).is(":checked");
        $("tr:not(.d-none) input[type=checkbox]").each(function(){
            $(this).prop('checked', checked);
        });
    });

    function appendPlacesToForm(form, places){
        let placesValue = places.val();
        for(let i = 0; i < placesValue.length; i++){
            form.append('<input type="hidden" name="pausedReservationPlaces[]" value="' + placesValue[i] + '">');
        }
    }

    function validatePlaces(places){
        if(!places.val().length){
            places.addClass('is-invalid');
            if(!places.next('.invalid-feedback').length){
                places.after('<span class="invalid-feedback">يجب إختيار مكان واحد على الأقل</span>');
            }
            return false;
        } else {
            places.removeClass('is-invalid');
            places.next('.invalid-feedback').remove();
        }
        return true;
    }

    function validatePausedDates(from_date, to_date, from_date_element, to_date_element){
        if(!validateInput(from_date, from_date_element.next('.date-picker'), "تاريخ البداية مطلوب")){
            return false;
        }

        if(!validateInput(to_date, to_date_element.next('.date-picker'), "تاريخ النهاية مطلوب")){
            return false;
        }

        let from_date_moment = moment(from_date),
            to_date_moment = moment(to_date);

        if(!validateInput(from_date_moment.isValid(), from_date_element.next('.date-picker'),
            "التاريخ غير صحيح")){
            return false;
        }

        if(!validateInput(to_date_moment.isValid(), to_date_element.next('.date-picker'),
            "التاريخ غير صحيح")){
            return false;
        }

        if(!validateInput(!(from_date_moment.diff(to_date_moment) > 0), from_date_element.next('.date-picker'),
            "لا يمكن أن يكون تاريخ البداية بعد تاريخ النهاية")){
            return false;
        }

        //check if from date is same or after current date
        let now = moment();
        //passing day to isSame will compare day, month and year
        if(!validateInput(!(!now.isBefore(from_date) && !now.isSame(from_date, 'day')),
            from_date_element.next(".date-picker"), "يجب أن يكون وقت إيقاف الحجز يساوي أو بعد تاريخ اليوم")){
            return false;
        }

        return true; //all valid
    }

    function getReservationIdFromButton(button){
        //get reservation id from button id 
        let buttonId = button.attr('id'),
            idArr = buttonId.split("_");
        return idArr[1];
    }

    function validateInput(condition, element, message){
        if(!condition){
            element.addClass('is-invalid');
            if(!element.next('.invalid-feedback').length){
                element.after('<span class="invalid-feedback">' + message + '</span>');
            }
            return false;
        }
        element.removeClass('is-invalid');
        element.next('.invalid-feedback').remove();
        return true;
    }
});