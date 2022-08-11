$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $("meta[name=csrf-token]").attr('content')
        }
    });
    let $calendar = $("#calendar"),
        calendarElm = $calendar.get(0),
        loadingElm = $(".lds-dual-ring");
    var calendar = new FullCalendar.Calendar(calendarElm, {
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        plugins: ["resourceTimeline", "interaction"],
        defaultView: "resourceTimelineWeek",
        resourcesInitiallyExpanded: true,
        height: 650,
        dir: 'rtl',
        aspectRatio: 1.8,
        resourceAreaWidth: "20%",
        resourceLabelText: 'الأماكن',
        firstDay: 1,
        locale: 'ar',
        titleFormat: {
            month: 'long',
            day: 'numeric'
        },
        slotLabelFormat: [
            {weekday: 'long', day: 'numeric'},
            {
                hour: '2-digit',
                hour12: false,
                minute: '2-digit',
                omitZeroMinute: false,
                meridiem: 0
            }
        ],
        resources: function (info, successCallback, failureCallback) {
            $.get('/get-calendar-resources')
                .done(function (data) {
                    successCallback(data.resources);
                }).fail(function (error) {
                    failureCallback(error);
            });
        },
        datesDestroy: function() {
            $calendar.addClass("hide-calendar");
            loadingElm.removeClass("d-none");
        },
        datesRender: function({view}) {
            let startDate = view.activeStart;
            let date = moment(startDate);
            $.get('/get-week-reservations-calendar/' + date.date() + '-' + (date.month() + 1) + '-' + date.year())
                .done(function (data) {
                    calendar.addEventSource(data.events);
                }).fail(function (error) {
            }).always(function () {
                $('#calendar [data-toggle="tooltip"]').tooltip({
                    boundary: $(".fc-time-area.fc-widget-content").get(0)
                });
                $calendar.removeClass("hide-calendar");
                loadingElm.addClass("d-none");
            });
        },
        eventRender: function(info) {
            let element = $(info.el),
                titleElement = element.find(".fc-title"),
                startDate = new Date(info.event.start),
                endDate = new Date(info.event.end),
                endTime = getTime(endDate),
                startTime = getTime(startDate);
            element.attr("data-toggle", "tooltip");
            element.attr("data-placement", "auto");
            element.attr("data-html", true);
            element.attr("title", info.event.title.replace('\n', '<br />') + "<br />" + startTime + " - " + endTime);
            titleElement.html(titleElement.html() + "<span class='d-block'>" + startTime + " - " + endTime + "</span>");
            element.addClass("full-height-event");
        },
        header: {
            right:   'title',
            center: '',
            left:  'next,prev'
        }
    });
    calendar.render();

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