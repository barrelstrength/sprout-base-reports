(function($) {
  /** global: Craft */
  /** global: Garnish */
  var SproutVisualizationSettings = Garnish.Base.extend(
    {
      $toggle: null,
      $target: null,
      $addSeries: null,

      init: function() {
        this.$toggle = $('select[name="visualizationType"]');
        this.$toggle.on("change", this.onVisualizationChange.bind(this));
        $('.js-add-data-series').on("click", this.addDataSeries.bind(this));
        $('table').on('click', '.js-remove-data-series', this.removeDataSeries.bind(this));

        this.findTarget();
      },

      addDataSeries: function(event) {
        event.preventDefault();
        let $row = $(event.currentTarget).parent().find('[name*="dataColumn"]').last().closest('tr');
        let $field = this.$target.find('[id$="visualizationDataColumn-field"]').first();

        let $clone = $row.clone();
        $clone.find('textarea').val('');
        $row.after($clone);

        return false;
      },

      removeDataSeries: function(event) {
        event.preventDefault();
        let $row = $(event.currentTarget).closest('tr');

        if ($row.siblings().length == 0) {
          alert('You must defined at least one data column');
        } else {
          $row.remove();
        }
        return false;

      },

      onVisualizationChange: function() {
        this.hideTarget(this.$target);
        this.findTarget();
        this.showTarget(this.$target);
      },

      findTarget: function() {
        var targetSelector = this.$toggle.val();
        if (targetSelector.length) {
          targetSelector = '#' + this.getToggleVal();
          this.$target = $(targetSelector);
        } else {
          this.$target = false;
        }
      },

      hideTarget: function($target) {
        if ($target && $target.length) {
          $target.addClass('hidden');
        }
      },

      showTarget: function($target) {

        if ($target && $target.length) {
          $target.removeClass('hidden');
        }
      },

      getToggleVal: function() {
        if (this.type === 'lightswitch') {
          return this.$toggle.children('input').val();
        } else {
          var postVal = Garnish.getInputPostVal(this.$toggle);
          return postVal === null ? null : postVal.replace(/[\[\]\\\/]+/g, '-');
        }
      }
    });


  Garnish.$doc.ready(function() {
    Craft.SproutVisualizationSettings = new SproutVisualizationSettings();
  });
})(jQuery);

