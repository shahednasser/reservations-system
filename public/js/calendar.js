$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr('content')
        }
    });
    //get date from url
    let arr = location.pathname.split("/"),
        currentDate = arr[arr.length - 1],
        url = '/get-reservations-calendar';
    if (currentDate && currentDate !== "calendar") {
        url += '/' + currentDate;
    }
    let $calendar = $("#calendar"),
        calendarElm = $calendar.get(0),
        events = [];
    $.get(url, function (data) {
        if(data.error){
            //handle error
            $calendar.before('<div class="alert alert-danger">حدث خطأ، الرجاء إعادة المحاولة في وقت آخر.</div>')
        } else {
            let options = {
                schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                plugins: ["resourceTimeline", "interaction"],
                defaultView: "resourceTimelineDay",
                resources: data.resources,
                events: data.events,
                resourcesInitiallyExpanded: true,
                header: {
                    left: '',
                    center: '',
                    right: ''
                },
                nowIndicator: true,
                height: 550,
                dir: 'rtl',
                resourceLabelText: 'الأماكن',
                slotDuration: '00:15:00',
                slotLabelDuration: '00:15:00',
                slotLabelInterval: '00:15:00',
                scrollTime: getScrollTime(),
                resourceAreaWidth: "20%",
                slotLabelFormat: {
                    hour: '2-digit',
                    hour12: false,
                    minute: '2-digit',
                    omitZeroMinute: false,
                    meridiem: 0
                },
                eventRender: function (info) {
                    let element = $(info.el),
                        titleElement = element.find(".fc-title"),
                        startDate = new Date(info.event.start),
                        endDate = new Date(info.event.end),
                        endTime = getTime(endDate),
                        startTime = getTime(startDate);
                    events.push(element);
                    element.attr("data-toggle", "tooltip");
                    element.attr("data-placement", "auto");
                    element.attr("data-html", true);
                    element.attr("title", info.event.title + "<br />" + startTime + " - " + endTime);
                    titleElement.html(titleElement.html() + "<span class='d-block'>" + startTime + " - " + endTime + "</span>");
                },
                datesRender: function () {
                    $(".lds-dual-ring").addClass("d-none");
                    $calendar.removeClass('hide-calendar');
                    $('#calendar [data-toggle="tooltip"]').tooltip({
                        boundary: $(".fc-time-area.fc-widget-content").get(0)
                    });
                    for(let i = 0; i < events.length; i++){
                        let parent = events[i].parent().parent();
                        events[i].height(parent.height());
                        events[i].addClass("full-height-event");
                    }
                }
            };
            if(currentDate && currentDate !== "calendar"){
                options["now"] = create_formatted_date(currentDate);
            }
            let calendar = new FullCalendar.Calendar(calendarElm, options);
            calendar.render();
        }
    });

    $("#calendarDateSubmit").click(function(){
        let date = format_date_url($("#calendarDate").val());
        location.href = "/calendar/" + date;
    });

    function format_date_url(date){
        let d = new Date(date.replace(/-/g, '/')),
            day = d.getDate().toString(),
            month = (d.getMonth() + 1).toString(),
            year = d.getFullYear();
        if(day.length === 1){
            day = "0" + day;
        }
        if(month.length === 1){
            month = "0" + month;
        }
        return day + "-" + month + "-" + year;
    }

    function create_formatted_date(date){
        let arr = date.split("-"),
            day = arr[0],
            month = arr[1] - 1,
            year = arr[2];
        return new Date(year, month, day);
    }

    function getScrollTime(){
        if(currentDate.length === 0){
            let date = new Date();
            return getTime(date);
        }
        return "00:00:00";
    }

    function getTime(date) {
        let hour = date.getHours().toString(),
            minute = date.getMinutes().toString();
        if(hour.length === 1){
            hour = "0" + hour;
        }
        if(minute.length === 1){
            minute = "0" + minute;
        }
        return hour + ":" + minute;
    }
});