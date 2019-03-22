$(document).ready(function() {
    SproutReport.init();
});

var SproutReport = {
    button: $('#dateRange'),
    init: function() {
        this.button.change(function() {
            SproutReport.selectDateRange();
        });

        SproutReport.selectDateRange();
    },

    selectDateRange: function() {
        $('#custom-date-range').hide();
        var dateVal = this.button.val();

        if (dateVal != undefined && dateVal == 'customRange') {
            $('#custom-date-range').show();
        }
    }
};
