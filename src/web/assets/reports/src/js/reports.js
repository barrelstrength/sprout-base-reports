/* global Craft */

/**
 * Initialize and style DataTables on the Sprout Reports results page
 */
class SproutReportDataTables {

  constructor(settings) {
    this.allowHtml = settings.allowHtml ?? false;
    this.defaultPageLength = settings.defaultPageLength ?? 10;
    this.sproutResultsTable = $('#sprout-results');
    this.contentSection = document.getElementById('main-content');
    this.headerFooterHeight = 122;

    this.initializeDataTable();
  }

  initializeDataTable() {
    let self = this;

    this.sproutResultsTable.DataTable({
      dom: '<"sprout-results-header"lf>t<"sprout-results-footer"pi>',
      responsive: true,
      scrollX: "100vw",
      scrollY: (self.contentSection.offsetHeight - self.headerFooterHeight) + 'px',
      pageLength: self.defaultPageLength,
      lengthMenu: [
        [10, 25, 50, 100, 250, -1],
        [10, 25, 50, 100, 250, 'All']
      ],
      pagingType: 'simple',
      language: {
        emptyTable: Craft.t('sprout-base-reports', 'No results found.'),
        info: Craft.t('sprout-base-reports', '_START_-_END_ of _MAX_ results'),
        infoEmpty: Craft.t('sprout-base-reports', 'No results found.'),
        infoFiltered: '',
        lengthMenu: Craft.t('sprout-base-reports', 'Show rows _MENU_'),
        loadingRecords: Craft.t('sprout-base-reports', 'Loading...'),
        processing: Craft.t('sprout-base-reports', 'Processing...'),
        search: '',
        zeroRecords: Craft.t('sprout-base-reports', 'No matching records found'),
      },
      columnDefs: [
        {
          targets: '_all',
          render: function(data, type, row, meta) {

            if (type === 'display' && data.length > 65 && self.allowHtml === false) {
              return data.substr(0, 65) + '… <span class="info" style="margin-right:10px;">' + data + '</span>';
            }

            return data;
          }
        }
      ],
      initComplete: function(settings, json) {

        let searchInput = document.querySelector('#sprout-results_filter input');
        let sproutResultsFilter = document.getElementById('sprout-results_filter');
        let sproutResultsInfo = document.getElementById('sprout-results_info');

        // Style Search Box
        searchInput.setAttribute('placeholder', Craft.t('sprout-base-reports', 'Search'));
        searchInput.classList.add('text', 'fullwidth');

        sproutResultsFilter.classList.add('texticon', 'search', 'icon', 'clearable');

        // // Style Results per Page Dropdown
        let resultsLengthDropdown = document.querySelector('#sprout-results_length select');
        let selectWrapper = document.createElement('dig');
        selectWrapper.classList.add('select');
        // Place new element in DOM
        resultsLengthDropdown.parentNode.insertBefore(selectWrapper, resultsLengthDropdown);
        // Move resultsLengthDropdown into wrapper
        selectWrapper.appendChild(resultsLengthDropdown);

        // Style Filter Results message
        sproutResultsInfo.classList.add('light');

        // Style Pagination
        self.stylePagination();

        // init info bubbles on page load
        Craft.initUiElements(self.sproutResultsTable);

        // init info bubbles after search, sort, filter, etc.
        self.sproutResultsTable.on('draw.dt', function() {
          // Style Pagination (again)
          self.stylePagination();

          Craft.initUiElements(self.sproutResultsTable);
        });

        let dataTablesScrollTable = document.querySelector('.dataTables_scroll table');
        dataTablesScrollTable.style.opacity = '1';
        let resultsTable = document.getElementById('sprout-results');

        resultsTable.style.opacity = '1';

        window.addEventListener('resize', self.resizeTable);

        self.resizeTable();
      }
    });
  }

  stylePagination() {
    document.querySelector('#sprout-results_paginate').classList.add('pagination');
    document.querySelector('.paginate_button').classList.add('page-link');
    document.querySelector('.paginate_button.previous').innerHTML = '';
    document.querySelector('.paginate_button.next').innerHTML = '';
    document.querySelector('.paginate_button.previous').setAttribute('data-icon', 'leftangle');
    document.querySelector('.paginate_button.next').setAttribute('data-icon', 'rightangle');
  }

  resizeTable() {
    let self = this;

    let dataTablesScrollBody = document.querySelector('.dataTables_scrollBody');
    dataTablesScrollBody.style.maxHeight = (self.contentSection.offsetHeight - self.getTableAdjustmentHeight()) + 'px';
  }

  getTableAdjustmentHeight() {
    let tableViewMargin = 48; // .tableview .sproutreports | margin-bottom
    let tableContentMargin = 40; // .tablecontent | margin-top|margin-bottom
    let tableHeaderHeight = document.querySelector('.sprout-results-header').offsetHeight;
    let tableFooterHeight = document.querySelector('.sprout-results-footer').offsetHeight;

    return tableViewMargin + tableContentMargin + tableHeaderHeight + tableFooterHeight;
  }
}

window.SproutReportDataTables = SproutReportDataTables;