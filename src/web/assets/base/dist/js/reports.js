
var SproutReportDataTables = {
    defaultPageLength: 50,
    $sproutResultsTable: $('#sprout-results'),
    $contentSection: $('#main-content'),
    allowHtml: false,

    init: function(settings) {
        this.allowHtml = settings.allowHtml;
        console.log(this.allowHtml);
        this.initializeDataTable();
    },

    initializeDataTable: function() {
        var self = this;
        var $sproutResultsTable = this.$sproutResultsTable;
        var $contentSection = this.$contentSection;

        var headerFooterHeight = 122;

        $sproutResultsTable.DataTable({
            dom: '<"sprout-results-header"lf>t<"sprout-results-footer"pi>',
            responsive: true,
            scrollX: "100vw",
            scrollY: ($contentSection.height() - headerFooterHeight) + 'px',
            pageLength: self.defaultPageLength,
            lengthMenu: [
                [50, 100, 250, 500, -1],
                [50, 100, 250, 500, "All"]
            ],
            pagingType: "simple_numbers",
            language: {
                emptyTable: Craft.t('sprout-base-reports', 'No results found.'),
                info: Craft.t('sprout-base-reports', 'Showing _START_ to _END_ (Total rows: _MAX_)'),
                infoEmpty: Craft.t('sprout-base-reports', 'No results found.'),
                infoFiltered: "",
                lengthMenu: Craft.t('sprout-base-reports', 'Show rows _MENU_'),
                loadingRecords: Craft.t('sprout-base-reports', 'Loading...'),
                processing: Craft.t('sprout-base-reports', 'Processing...'),
                search: "",
                zeroRecords: Craft.t('sprout-base-reports', 'No matching records found'),
            },
            columnDefs: [
                {
                    targets: "_all",
                    render: function(data, type, row, meta) {

                        if (type === 'display' && data.length > 65 && self.allowHtml === false) {
                            // return data;
                            return data.substr(0, 65) + '… <span class="info" style="margin-right:10px;">' + data + '</span>';
                        }

                        return data;
                    }
                }
            ],
            initComplete: function(settings, json) {

                var $searchInput = $('#sprout-results_filter input');

                // Style Search Box
                $searchInput.attr('placeholder', Craft.t('sprout-base-reports', 'Search'));
                $searchInput.addClass('text fullwidth');
                $('#sprout-results_filter').addClass('texticon search icon clearable');

                // Style Results per Page Dropdown
                $('#sprout-results_length select').wrap('<div class="select"></div>');

                // Style Filter Results message
                $('#sprout-results_info').addClass('light');

                // init info bubbles on page load
                Craft.initUiElements($sproutResultsTable);

                // init info bubbles after search, sort, filter, etc.
                $sproutResultsTable.on('draw.dt', function() {
                    Craft.initUiElements($sproutResultsTable);
                });

                $('.dataTables_scroll table').removeClass('hidden');

                $(window).on('resize', function() {
                    self.resizeTable();
                });

                self.resizeTable();
            }
        });
    },

    resizeTable: function() {
        var self = this;
        $('.dataTables_scrollBody').css({
            maxHeight: (self.$contentSection.height() - self.getTableAdjustmentHeight()) + 'px'
        });
    },

    getTableAdjustmentHeight: function() {
        var tableViewMargin = 48; // .tableview .sproutreports | margin-bottom
        var tableContentMargin = 40; // .tablecontent | margin-top|margin-bottom
        var tableHeaderHeight = $('.sprout-results-header').height();
        var tableFooterHeight = $('.sprout-results-footer').height();

        return tableViewMargin + tableContentMargin + tableHeaderHeight + tableFooterHeight;
    }
};

var SproutReport = {
    button: $('#dateRange'),
    init: function() {
        this.button.change(function() {
            SproutReport.selectDateRange();
        });

        SproutReport.selectDateRange();
    },

    selectDateRange: function() {
        var $customDateRange = $('#custom-date-range');
        $customDateRange.hide();
        var dateVal = this.button.val();

        if (dateVal != undefined && dateVal == 'customRange') {
            $customDateRange.show();
        }
    }
};
