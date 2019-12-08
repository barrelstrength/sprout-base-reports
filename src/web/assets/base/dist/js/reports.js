var SproutReportDataTables = {
  defaultPageLength: 10,
  $sproutResultsTable: $('#sprout-results'),
  $contentSection: $('#main-content'),
  allowHtml: false,

  init: function(settings) {
    this.allowHtml = settings.allowHtml;
    this.defaultPageLength = settings.defaultPageLength;
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
        [10, 25, 50, 100, 250, -1],
        [10, 25, 50, 100, 250, "All"]
      ],
      pagingType: "simple",
      language: {
        emptyTable: Craft.t('sprout-base-reports', 'No results found.'),
        info: Craft.t('sprout-base-reports', '_START_-_END_ of _MAX_ results'),
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

        // Style Pagination
        self.stylePagination();

        // init info bubbles on page load
        Craft.initUiElements($sproutResultsTable);

        // init info bubbles after search, sort, filter, etc.
        $sproutResultsTable.on('draw.dt', function() {
          // Style Pagination (again)
          self.stylePagination();

          Craft.initUiElements($sproutResultsTable);
        });

        $('.dataTables_scroll table').css('opacity', 1);

        $(window).on('resize', function() {
          self.resizeTable();
        });

        self.resizeTable();
      }
    });
  },

  stylePagination: function() {
    $('#sprout-results_paginate').addClass('pagination');
    $('.paginate_button').addClass('page-link');
    $('.paginate_button.previous, .paginate_button.next').html('');
    $('.paginate_button.previous').attr('data-icon', 'leftangle');
    $('.paginate_button.next').attr('data-icon', 'rightangle');
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
