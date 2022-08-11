$(document).ready(function() {
    $(".iziModal").iziModal();
    const reservation_id = location.href.substring(location.href.lastIndexOf("/") + 1),
            navbarHeight = $(".navbar").outerHeight();
    let validated = $("button[name=checkReservations]").length == 0 || $("input[name=pledge]").length == 0,
        pledged = $("input[name=pledge]").length == 0;

    $("input[type=checkbox]").change(function() {
        let arr = $(this).attr("name").split("_"),
            daynb = arr[1],
            type = arr[0];
        setDisabledRow(daynb, type, !$(this).is(":checked"));
    });

    $("button[name=checkReservations]").click(function() {
        disableLoadingButton($(this));
        const id = $(this).attr("id"),
            self = this,
            $submitBtn = $("#acceptButtonModal, button[name=submit], button[type=submit]");
        $(".reservations-table .bg-danger").removeClass("bg-danger");
        $(".reservations-table input.is-invalid").removeClass('is-invalid');
        $(".reservations-table .invalid-feedback").remove();
        let url = "";
        let $result = $("#result");
        $result.text("");
        if ($(this).parents(".iziModal").length || $(this).parents(".edit-form").length) {
            url = "/checkReservations/" + reservation_id;
        } else {
            url = "/checkNewReservation/" + reservation_id;
        }
        $(".alert-danger").remove();
        if (id === "tempRes") {
            const fields = {};
            if(!validateConditionGeneral(!($("input[name*=dates_]:checked").length == 0), $(this),
                "يجب أن تختار توقيت واحد على الأقل.", $submitBtn)){
                enableLoadingButton($(this));
                return;
            }
            let places = $("select[name='places[]']").val();
            if(!validateConditionGeneral(places.length, $(this),
                "يجب أن تختار مكان واحد على الأقل.", $submitBtn)){
                enableLoadingButton($(this));
                return;
            }
            for (let i = 0; i < 3; i++) {
                if ($("input[name=dates_" + i + "]").is(":checked")) {
                    //validation
                    let $from_timeElm = $("input[name=from_time_" + i + "]"),
                        $to_timeElm = $("input[name=to_time_" + i + "]");
                    if(!validateConditionInput(!($from_timeElm.val() >= $to_timeElm.val()),
                        $from_timeElm, "لا يمكن ان يكون وقت بداية النشاط بعد وقت نهايته",
                        $submitBtn)){
                        enableLoadingButton($(this));
                        return;
                    }
                    let dateElm = $("input[name=date_" + i + "]"),
                        date = dateElm.val();
                    if(!validateConditionInput(date, dateElm.next('.date-picker'),"يجب تحديد التاريخ",
                        $submitBtn)){
                        enableLoadingButton($(this));
                        return;
                    }
                    fields["dates_" + i] = true;
                    fields["date_" + i] = $("input[name=date_" + i + "]").val();
                    fields["from_time_" + i] = $from_timeElm.val();
                    fields["to_time_" + i] = $to_timeElm.val();
                }
            }

            fields["places"] = places;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr('content'),
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                }
            });

            $.post(url, {
                        from_date: null,
                        to_date: null,
                        fields
                    },
                    function(data) {
                        if(!validateConditionGeneral(!data.error, $(self), data.error, $submitBtn)){
                            enableLoadingButton($(self));
                            return;
                        }
                        let reservations = data.reservations,
                            keys = Object.keys(reservations),
                            first = true,
                            str = "";
                        if (keys.length > 0) {
                            const days = ["الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"];
                            let placesArr = [];
                            for(let place of places){
                                placesArr.push({
                                    places: place.split("_")
                                })
                            }
                            let str = "";
                            for (let i = 0; i < 3; i++) {
                                let keys_include = keys.includes(i.toString());
                                if (keys_include) {
                                    let res = reservations[i].reservations;
                                    let $tr = $(`input[name=dates_${i}]`).parents("tr");
                                    let cond1 = res.length !== 0;
                                    if (cond1) {
                                        for (let reservation of res) {
                                            const date = $("input[name=date_" + i + "]").val(),
                                                from_time = $("input[name=from_time_" + i + "]").val(),
                                                to_time = $("input[name=to_time_" + i + "]").val(),
                                                day = getDate(date);
                                            if (reservation.hasOwnProperty("temporary_reservation_dates")) {
                                                //temporary reservation
                                                for (let temp of reservation.temporary_reservation_dates) {
                                                    let temp_from_time = format_time_without_seconds(temp["from_time"]),
                                                        temp_to_time = format_time_without_seconds(temp["to_time"]);
                                                    if (temp["date"] === date && between(temp_from_time, from_time,
                                                        temp_to_time, to_time)) {
                                                        let placeFound = false;
                                                        for (let temp_place of reservation.temporary_reservation_places) {
                                                            for (let placeObj of placesArr) {
                                                                let place = placeObj["places"];
                                                                if (temp_place["floor_id"] == place[0] &&
                                                                    ((place.length > 1 && temp_place["room_id"] == place[1]) ||
                                                                        (temp_place["room_id"] == null))) {
                                                                    $("select[name='places[]']").parents("tr")
                                                                        .addClass("bg-danger").addClass("text-white");
                                                                    placeFound = true;
                                                                }
                                                            }
                                                        }
                                                        if (placeFound) {
                                                            if (first) {
                                                                $result.text("الحجوزات التالية تتعارض مع الاوقات الجديدة");
                                                                str = '<table class="table table-hover table-bordered">' +
                                                                    '<thead>' +
                                                                    '<tr><th>الاسم</th><th>اليوم</th><th>يتعارض مع</th></thead><tbody>';
                                                                first = false;
                                                            }
                                                            $tr.addClass("bg-danger").addClass("text-white");
                                                            str += "<tr><td><a href='/show-reservation/" +
                                                                reservation.reservation.id +"'>" + reservation.reservation.event_name
                                                                + "</a></td>";
                                                            str += "<td>" + days[day == 0 ? days.length - 1 : day - 1] + " " + temp_from_time + " - " +
                                                                temp_to_time + "</td><td>" + days[day == 0 ? days.length - 1 : day - 1] + " " +
                                                                from_time + " - " + to_time + "</td>";
                                                        }
                                                    }
                                                }
                                            }
                                            else {
                                                //long reservation
                                                for (let long of reservation.long_reservation_dates) {
                                                    const long_from_time = format_time_without_seconds(long.from_time),
                                                        long_to_time = format_time_without_seconds(long.to_time);
                                                    if (long.day_of_week != day || !between(from_time, long_from_time,
                                                        to_time, long_to_time)) {
                                                        continue;
                                                    }
                                                    let placeFound = false;
                                                    for(let longPlace of long.long_reservation_places){
                                                        for (let placeObj of placesArr) {
                                                            let place = placeObj["places"];
                                                            if (place[0] == longPlace.floor_id && ((place.length > 1 &&
                                                                place[1] == longPlace.room_id) || longPlace.room_id == null)) {
                                                                $("select[name='places[]']").addClass("is-invalid");
                                                                placeFound = true;
                                                            }
                                                        }
                                                    }

                                                    if (placeFound) {
                                                        if (first) {
                                                            $result.text("الحجوزات التالية تتعارض مع الاوقات الجديدة");
                                                            str = '<table class="table table-hover table-bordered">' +
                                                                '<thead>' +
                                                                '<tr><th>الاسم</th><th>اليوم</th><th>يتعارض مع</th></thead><tbody>';
                                                            first = false;
                                                        }
                                                        $tr.addClass("bg-danger").addClass("text-white");
                                                        str += "<tr><td><a href='/show-reservation/" + reservation.reservation.id
                                                            + "'>" + reservation.reservation.event_name + "</a></td>";
                                                        str += "<td>" + days[day == 0 ? days.length - 1 : day - 1] + " " + long_from_time + " - " +
                                                            long_to_time + "</td><td>" + days[day == 0 ? days.length - 1 : day - 1] + " " +
                                                            from_time + " - " + to_time + "</td>";
                                                    }
                                                }
                                            }

                                            str += "</tr>";
                                        }
                                    }
                                        res = reservations[i].manual_reservations;
                                        $tr = $("input[name=dates_" + i + "]").parents("tr");
                                        if (res && res.length > 0) {
                                            for (let reservation of res) {
                                                const date = $("input[name=date_" + i + "]").val(),
                                                    from_time = $("input[name=from_time_" + i + "]").val(),
                                                    to_time = $("input[name=to_time_" + i + "]").val(),
                                                    day = getDate(date);
                                                for (let mrd of reservation.manual_reservations_dates) {
                                                    let manual_from_time = format_time_without_seconds(mrd["from_time"]),
                                                        manual_to_time = format_time_without_seconds(mrd["to_time"]);
                                                    if (mrd["date"] == date && between(from_time, manual_from_time, to_time,
                                                        manual_to_time)) {
                                                        if (first) {
                                                            $result.text("الحجوزات التالية تتعارض مع الاوقات الجديدة");
                                                            str = '<table class="table table-hover table-bordered">' +
                                                                '<thead>' +
                                                                '<tr><th>الاسم</th><th>اليوم</th><th>يتعارض مع</th></thead><tbody>';
                                                            first = false;
                                                        }
                                                        $tr.addClass("bg-danger").addClass("text-white");
                                                        str += "<tr><td><a href='/view-admin-reservation/" +
                                                            reservation.id + "'>" + reservation.event_type +
                                                            "</a></td>";
                                                        str += "<td>" + days[day == 0 ? days.length - 1 : day - 1] + " " + manual_from_time + " - " +
                                                            manual_to_time + "</td><td>" + days[day == 0 ? days.length - 1 : day - 1] + " " +
                                                            from_time + " - " + to_time + "</td>";
                                                    }
                                                }
                                            }
                                        }
                                        else if(!cond1){
                                            $("input[name=from_time_" + i + "]").parents("tr").removeClass("bg-danger").removeClass("text-white");
                                        }
                                }
                                else {
                                    $("input[name=from_time_" + i + "]").parents("tr").removeClass("bg-danger").removeClass("text-white");
                                }
                            }
                            if (first) {
                                $("#result").text("لا يوجد اي حجوزات تتعارض مع الاوقات الجديدة");
                                $("tr").removeClass("bg-danger").removeClass("text-white");
                                $("select").removeClass("is-invalid");
                                if (pledged) {
                                    $submitBtn.prop("disabled", false);
                                }
                                validated = true;
                            } else {
                                str += "</tbody></table>";
                                $result.append(str);
                                $submitBtn.prop("disabled", true);
                            }
                        } else {
                            $("#result").text("لا يوجد اي حجوزات تتعارض مع الاوقات الجديدة");
                            $("tr").removeClass("bg-danger").removeClass("text-white");
                            $("select").removeClass("is-invalid");
                            if (pledged) {
                                $submitBtn.prop("disabled", false);
                            }
                            validated = true;
                        }
                    })
                .fail(function() {
                    $("#result").text("");
                    $("#result").append('<div class="alert alert-danger">حدث خطأ. الرجاء اعادة المحاولة في وقت لاحق.</div>');
                }).always(function () {
                    enableLoadingButton($(self));
            });
        } else {
            const $from_dateElm = $("input[name=from_date]"),
                $to_dateElm = $("input[name=to_date]"),
                from_date = $from_dateElm.val(),
                to_date = $to_dateElm.val(),
                fields = {};
            if(!validateConditionInput(from_date.length, $from_dateElm.next('.date-picker'),
                'يجب إختيار تاريخ البداية', $submitBtn)){
                enableLoadingButton($(this));
                return;
            }
            if(!validateConditionInput(to_date.length, $to_dateElm.next('.date-picker'),
                'يجب إختيار تاريخ النهاية', $submitBtn)){
                enableLoadingButton($(this));
                return;
            }
            if (!validateConditionInput(!(from_date >= to_date), $from_dateElm.next('.date-picker'),
                'تاريخ البداية يجب أن يكون قبل وقت تاريخ النهاية.', $submitBtn)) {
                enableLoadingButton($(this));
                return;
            }
            if(!validateConditionGeneral($("input[name*=day_]:checked").length, $(this),
                "يجب إختيار يوم واحد على الأقل.", $submitBtn)){
                enableLoadingButton($(this));
                return;
            }
            for (let day = 0; day < 7; day++) {
                if ($("input[name=day_" + day + "]").is(":checked")) {
                    let $from_timeElm = $("input[name=from_time_" + day + "]"),
                        to_time = $("input[name=to_time_" + day + "]").val();

                    if(!validateConditionInput(!($from_timeElm.val() >= to_time), $from_timeElm,
                        "لا يمكن ان يكون وقت بداية النشاط بعد أو في نفس وقت نهايته", $submitBtn)){
                        enableLoadingButton($(this));
                        return;
                    }
                    let places = $("select[name='place_" + day + "[]']");
                    if(!validateConditionInput(places.val().length, places, "يجب إختيار مكان واحد على الأقل",
                        $submitBtn)){
                        enableLoadingButton($(this));
                        return;
                    }
                    fields["day_" + day] = true;
                    fields["from_time_" + day] = $from_timeElm.val();
                    fields["to_time_" + day] = to_time;
                    fields["event_" + day] = $("input[name=event_" + day + "]").val();
                    fields["place_" + day] = places.val();
                }
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr('content'),
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                }
            });

            $.post(url, {
                    from_date,
                    to_date,
                    fields
                }, function(data) {
                    if(!validateConditionGeneral(!data.error, $(self), data.error, $submitBtn)){
                        enableLoadingButton($(self));
                        return;
                    }
                    let reservations = data.reservations,
                        keys = Object.keys(reservations);
                    let first = true,
                        str = "";
                    if (keys.length > 0) {
                        const days = ["الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"];
                        for (let i = 0; i < 7; i++) {
                            let keys_include = keys.includes(i.toString());
                            let day = i == 6 ? 0 : i + 1;
                            if (keys_include) {
                                let res = reservations[i].reservations;
                                let $tr = $("input[name=from_time_" + i + "]").parents("tr");
                                let cond1 = res.length != 0;
                                if (cond1) {
                                    for (let reservation of res) {
                                        const from_time = $("input[name=from_time_" + i + "]").val(),
                                            to_time = $("input[name=to_time_" + i + "]").val();
                                        if (reservation.hasOwnProperty("temporary_reservation_dates")) {
                                            //temporary reservation
                                            for (let temp of reservation.temporary_reservation_dates) {
                                                const day_of_week = (new Date(temp.date.replace(/-/g, '/'))).getDay(),
                                                    temp_from_time = format_time_without_seconds(temp.from_time),
                                                    temp_to_time = format_time_without_seconds(temp.to_time);
                                                if (day_of_week != day || temp.date < from_date || temp.date > to_date ||
                                                    !between(from_time, temp_from_time, to_time, temp_to_time)) {
                                                    continue;
                                                }
                                                if (first) {
                                                    $result.text("الحجوزات التالية تتعارض مع الاوقات الجديدة");
                                                    str = '<table class="table table-hover table-bordered">' +
                                                        '<thead>' +
                                                        '<tr><th>الاسم</th><th>اليوم</th><th>يتعارض مع</th></thead><tbody>';
                                                    first = false;
                                                }
                                                $tr.addClass("bg-danger").addClass("text-white");
                                                str += "<tr><td><a href='/show-reservation/" + reservation.reservation.id + "'>" +
                                                    reservation.reservation.event_name + "</a></td>";
                                                str += "<td>" + days[i] + " " + temp_from_time + " - " +
                                                    temp_to_time + "</td><td>" + days[i] + " " +
                                                    from_time + " - " + to_time + "</td>";
                                            }
                                        } else {
                                            //long reservation
                                            for (let long of reservation.long_reservation_dates) {
                                                const long_from_time = format_time_without_seconds(long.from_time),
                                                    long_to_time = format_time_without_seconds(long.to_time);
                                                if (long.day_of_week != day || !between(from_time, long_from_time, to_time,
                                                    long_to_time)) {
                                                    continue;
                                                }
                                                if (first) {
                                                    $result.text("الحجوزات التالية تتعارض مع الاوقات الجديدة");
                                                    str = '<table class="table table-hover table-bordered">' +
                                                        '<thead>' +
                                                        '<tr><th>الاسم</th><th>اليوم</th><th>يتعارض مع</th></thead><tbody>';
                                                    first = false;
                                                }
                                                $tr.addClass("bg-danger").addClass("text-white");
                                                str += "<tr><td><a href='/show-reservation/" + reservation.reservation.id + "'>" +
                                                    reservation.reservation.event_name + "</a></td>";
                                                str += "<td>من: " + format_date(reservation.from_date) + " الى " + format_date(reservation.to_date) + "<br><hr>";
                                                str += days[i] + " " + long_from_time + " - " +
                                                    long_to_time;
                                                str += "</td><td>" + days[i] + " " + from_time +
                                                    " - " + to_time + "</td>";

                                            }
                                        }
                                        str += "</tr>";
                                    }
                                }
                                    res = reservations[i].manual_reservations;
                                    if(res && res.length){
                                        const from_time = $("input[name=from_time_" + i + "]").val(),
                                            to_time = $("input[name=to_time_" + i + "]").val();
                                        let $tr = $("input[name=from_time_" + i + "]").parents("tr");
                                        if (res.length != 0) {
                                            for (let reservation of res) {
                                                for (let mrd of reservation.manual_reservations_dates) {
                                                    const day_of_week = (new Date(mrd.date.replace(/-/g, '/'))).getDay(),
                                                        mrd_from_time = format_time_without_seconds(mrd.from_time),
                                                        mrd_to_time = format_time_without_seconds(mrd.to_time);
                                                    if (day_of_week != day || mrd.date < from_date || mrd.date > to_date ||
                                                        !between(from_time, mrd_from_time, to_time, mrd_to_time)) {
                                                        continue;
                                                    }
                                                    if (first) {
                                                        $result.text("الحجوزات التالية تتعارض مع الاوقات الجديدة");
                                                        str = '<table class="table table-hover table-bordered">' +
                                                            '<thead>' +
                                                            '<tr><th>الاسم</th><th>اليوم</th><th>يتعارض مع</th></thead><tbody>';
                                                        first = false;
                                                    }
                                                    $tr.addClass("bg-danger").addClass("text-white");
                                                    str += "<tr><td><a href='/view-admin-reservation/" + reservation.id + "'>" +
                                                        reservation.event_type + "</a></td>";
                                                    str += "<td>" + days[i] + " " + mrd_from_time + " - " +
                                                        mrd_to_time + "</td><td>" + days[i] + " " +
                                                        from_time + " - " + to_time + "</td>";
                                                }
                                            }
                                        }
                                    }
                                    else if(!cond1){
                                        $("input[name=from_time_" + i + "]").parents("tr").removeClass("bg-danger").removeClass("text-white");
                                    }
                            }
                            else {
                                $("input[name=from_time_" + i + "]").parents("tr").removeClass("bg-danger").removeClass("text-white");
                            }
                        }
                        if (first) {
                            $("#result").text("لا يوجد اي حجوزات تتعارض مع الاوقات الجديدة");
                            $("tr").removeClass("bg-danger").removeClass("text-white");
                            if (pledged) {
                                $submitBtn.prop("disabled", false);
                            }
                            validated = true;
                        } else {
                            str += "</tbody></table>";
                            $result.append(str);
                            $submitBtn.prop("disabled", true);
                            validated = false;
                        }
                    } else {
                        $("#result").text("لا يوجد اي حجوزات تتعارض مع الاوقات الجديدة");
                        $("tr").removeClass("bg-danger").removeClass("text-white");
                        if (pledged) {
                            $submitBtn.prop("disabled", false);
                        }
                        validated = true;
                    }
                })
                .fail(function() {
                    $("#result").text("");
                    $("#result").append('<div class="alert alert-danger">حدث خطأ. الرجاء اعادة المحاولة في وقت لاحق.</div>');
                })
                .always(function() {
                    enableLoadingButton($(self));
                });
        }
    });

    $("#acceptButtonModal").click(function() {
        if ($("#result").children().length > 0) {
            return;
        }
        $("#editModal .alert-danger").remove();
        let fields = {},
            from_date = null,
            to_date = null;
        if ($(this).hasClass("temp-accept")) {
            //temp reservation
            for (let i = 0; i < 3; i++) {
                if ($("input[name=dates_" + i + "]").is(":checked")) {
                    //validation
                    let $from_timeElm = $("input[name=from_time_" + i + "]"),
                        $to_timeElm = $("input[name=to_time_" + i + "]");
                    if(!validateConditionInput(!($from_timeElm.val() >= $to_timeElm.val()), $from_timeElm,
                        "لا يمكن ان يكون وقت بداية النشاط بعد وقت نهايته")){
                        return;
                    }
                    let dateElm = $("input[name=date_" + i + "]"),
                        date = dateElm.val();
                    if(!validateConditionInput(date, dateElm.next('.date-picker'),"يجب تحديد التاريخ")){
                        return;
                    }
                    fields["dates_" + i] = true;
                    fields["date_" + i] = date;
                    fields["from_time_" + i] = $from_timeElm.val();
                    fields["to_time_" + i] = $to_timeElm.val();
                }
            }

            let placesSelect = $("select[name='places[]']"),
                places = placesSelect.val();
            if(!places.length){
                placesSelect.addClass("is-invalid");
                if (placesSelect.next(".invalid-feedback").length) {
                    placesSelect.after('<span class="invalid-feedback">يجب إختيار مكان واحد على الأقل.</span>');
                }
                $([document.documentElement, document.body]).animate({
                    scrollTop: placesSelect.offset().top - navbarHeight
                }, 1000);
                return;
            }
            fields["places"] = places;
        }
        else {
            //long reservation
            const $from_dateElm = $("input[name=from_date]"),
            $to_dateElm = $("input[name=to_date]");
            from_date = $from_dateElm.val();
            to_date = $to_dateElm.val();
            if(!validateConditionInput(from_date.length, $from_dateElm.next('.date-picker'),
                'يجب إختيار تاريخ البداية')){
                return;
            }
            if(!validateConditionInput(to_date.length, $to_dateElm.next('.date-picker'),
                'يجب إختيار تاريخ النهاية')){
                return;
            }
            if(!validateConditionInput(!(from_date >= to_date), $from_dateElm.next('.date-picker'),
                "لا يمكن أن يكون تاريخ البداية بعد تاريخ النهاية")){
                return;
            }
            for (let day = 0; day < 7; day++) {
                if ($("input[name=day_" + day + "]").is(":checked")) {
                    let $from_timeElm = $("input[name=from_time_" + day + "]"),
                        to_time = $("input[name=to_time_" + day + "]").val();

                    if(!validateConditionInput(!($from_timeElm.val() >= to_time), $from_timeElm,
                        "لا يمكن ان يكون وقت بداية النشاط بعد وقت نهايته")){
                        return;
                    }
                    fields["day_" + day] = true;
                    fields["from_time_" + day] = $from_timeElm.val();
                    fields["to_time_" + day] = to_time;
                    fields["event_" + day] = $("input[name=event_" + day + "]").val();
                    fields["place_" + day] = $("select[name='place_" + day + "[]'").val();
                }
            }
        }

        const $self = $(this);
        $self.prop("disabled", true);
        $.post("/send-edit-reservation/" + reservation_id, {
                fields,
                from_date,
                to_date
            }, function(data) {
                if (!validateConditionGeneral(!data.error, $self, data.error)) {
                    $self.prop("disabled", true);
                } else if (data.success) {
                    window.location.href = "/view-reservation/" + data.success;
                }
            })
            .fail(function() {
                validateConditionGeneral(false, $self, "حدث خطأ. الرجاء اعادة المحاولة في وقت لاحق.");
                $self.prop("disabled", false);
            });
    });

    $("table input, input[type=date], select").change(function() {
        $("#acceptButtonModal, button[name=submit]").prop("disabled", true);
        $("#result").text("");
    });

    $("input[name=pledge]").change(function() {
        if (validated && $(this).is(":checked")) {
            $("button[type=submit]").prop("disabled", false);
        } else {
            $("button[type=submit]").prop("disabled", true);
        }
        pledged = $(this).is(":checked");
    });

    function setDisabledRow(daynb, type, value) {
        switch (true) {
            case type === "day":
                $(`input[name=from_time_${daynb}]`).prop("disabled", value);
                if(value){
                  //add disabled class
                  $(`input[name=from_time_${daynb}]`).next().addClass("disabled");
                } else {
                  $(`input[name=from_time_${daynb}]`).next().removeClass("disabled");
                }
                $(`input[name=to_time_${daynb}]`).prop("disabled", value);
                if(value){
                  //add disabled class
                  $(`input[name=to_time_${daynb}]`).next().addClass("disabled");
                } else {
                  $(`input[name=to_time_${daynb}]`).next().removeClass("disabled");
                }
                $("input[name=event_" + daynb + "]").prop("disabled", value);
                $(`select[name='place_${daynb}[]']`).prop("disabled", value);
                break;
            case type === "dates":
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
                $("input[name=date_" + daynb + "]").next('.date-picker').prop("disabled", value);
                if(value){
                    //add disabled class
                    $("input[name=date_" + daynb + "]").next().addClass("disabled");
                } else {
                    $("input[name=date_" + daynb + "]").next().removeClass("disabled");
                }
                break;
            case type === "places":
                $("select[name='place_" + daynb + "[]']").prop("disabled", value);
                break;
        }
    }

    function format_time_without_seconds(time) {
        return time.substring(0, time.lastIndexOf(":"));
    }

    function format_date(date) {
        let d = new Date(date.replace(/-/g, '/'));
        return d.getDate() + "/" + (d.getMonth() + 1) + "/" + d.getFullYear();
    }

    function getDate(date) {
        let d = new Date(date.replace(/-/g, '/'));
        return d.getDay();
    }

    function scrollToModal() {
        if ($(".iziModal").length) {
            $(".iziModal-wrap").animate({
                scrollTop: 0
            }, 1000);
        } else {
            if($(".alert-danger").length){
                $([document.documentElement, document.body]).animate({
                    scrollTop: $(".alert-danger").offset().top - navbarHeight
                }, 1000);
            }
            else if($("tr.bg-danger").length){
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("tr.bg-danger").offset().top - navbarHeight
                }, 1000);
            }
            else{
                $([document.documentElement, document.body]).animate({
                    scrollTop: $("input.is-invalid").offset().top - navbarHeight
                }, 1000);
            }
        }
    }

    function validateConditionInput(condition, element, message, $submitBtn = null){
        if(!condition){
            element.addClass("is-invalid");
            if (!element.next(".invalid-feedback").length) {
                element.after(`<span class="invalid-feedback">${message}</span>`)
            }
            $([document.documentElement, document.body]).animate({
                scrollTop: element.offset().top - navbarHeight
            });
            if($submitBtn){
                $submitBtn.prop("disabled", true);
                validated = false;
            }
            return false;
        }
        element.removeClass("is-invalid");
        if (element.next(".invalid-feedback").length) {
            element.next().remove();
        }
        return true;
    }

    function validateConditionGeneral(condition, element, message, $submitBtn){
        if(!condition){
            if (element.parents(".iziModal").length) {
                $("#editModal .modal-header").after('<div class="alert alert-danger">' + message + '</div>');
            } else {
                $("table").before('<div class="alert alert-danger">' + message + '</div>');
            }
            scrollToModal();
            if($submitBtn){
                $submitBtn.prop("disabled", true);
                validated = false;
            }
            return false;
        }
        return true;
    }
});
