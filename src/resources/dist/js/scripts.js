(function($) {
  /** global: Craft */
  /** global: Garnish */
  var SproutVisualizations = Garnish.Base.extend(
      {
          $toggle: null,
          $target: null,

          init: function() {
            this.$toggle = $('select[name="visualizationType"]');
            this.$toggle.on("change", this.onVisualizationChange.bind(this));
            this.findTarget();
          },

          onVisualizationChange: function() {
            console.log('chage');
            console.log(this);

            this.hideTarget(this.$target);

            this.findTarget();

            this.showTarget(this.$target);

          },

          findTarget: function() {
            var targetSelector = this.$toggle.val();
            targetSelector = '#' + this.getToggleVal();
            console.log('tareget selector ' + targetSelector);
            this.$target = $(targetSelector);
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
      Craft.SproutVisualizations = new SproutVisualizations();
  });
})(jQuery);
