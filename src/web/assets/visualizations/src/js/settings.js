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
            $('button.js-add-data-series').on("click", this.addDataSeries.bind(this));
            this.findTarget();
          },

          addDataSeries: function(event) {
            event.preventDefault();
            console.log('add data series');
            let $field = this.$target.find('[id$="visualizationDataColumn-field"]').first();
            console.log($field);
            let $clone = $field.clone();
            $clone.find('input').val('');
            $field.after($clone);

            return false;
          },

          onVisualizationChange: function() {
            this.hideTarget(this.$target);
            this.findTarget();
            this.showTarget(this.$target);
          },

          findTarget: function() {
            var targetSelector = this.$toggle.val();
            if (targetSelector.length){
              targetSelector = '#' + this.getToggleVal();
              this.$target = $(targetSelector);
            }
          },

          hideTarget: function($target){
            if ($target && $target.length){
              $target.addClass('hidden');
            }
          },

          showTarget: function($target){

            if ($target && $target.length){
              $target.removeClass('hidden');
            }
          },

          getToggleVal: function() {
            if (this.type === 'lightswitch') {
                return this.$toggle.children('input').val();
            }
            else {
                var postVal = Garnish.getInputPostVal(this.$toggle);
                return postVal === null ? null : postVal.replace(/[\[\]\\\/]+/g, '-');
            }
          }
      });


  Garnish.$doc.ready(function() {
      Craft.SproutVisualizationSettings = new SproutVisualizationSettings();
  });
})(jQuery);

