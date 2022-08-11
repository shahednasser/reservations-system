$(document).ready(function () {
    $(".date-picker").map(function(){
        $(this).datepicker({
            dateFormat: 'dd/mm/yy',
            onSelect: changeDate,
            changeYear: true
        });
        $(this).on('change', changeDate);
    });

    function changeDate(){
        let dateString = $(this).datepicker('getDate'),
            date = new Date(dateString),
            dateElm = $(this).prev("input[type=date]"),
            day = date.getDate().toString(),
            month = (date.getMonth() + 1).toString(),
            year = date.getFullYear().toString();
        if(day.length === 1){
            day = "0" + day;
        }
        if(month.length === 1){
            month = "0" + month;
        }
        dateElm.val(year + "-" + month + "-" + day);
    }
});