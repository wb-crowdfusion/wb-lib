'use strict';

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError('Cannot call a class as a function'); } }

/**
 * Class Word Count
 */
var WordCount = (function () {

  /**
   * @param {String} elementId
   * @param {Number} limit
   * @constructor
   */
  function WordCount(elementId) {
    var limit = arguments.length <= 1 || arguments[1] === undefined ? 20 : arguments[1];

    _classCallCheck(this, WordCount);

    this.elementId = elementId;
    this.limit = limit;
    this.$targetElement = $('#' + elementId);
    this.$counterLabel = null;

    this.createCounterLabel();

    this.updateCharacterCount();

    this.$targetElement
      .blur(this.updateCharacterCount.bind(this))
      .keyup(this.updateCharacterCount.bind(this));
  }


  /**
   * Creates counter span field
   */
  WordCount.prototype.createCounterLabel = function createCounterLabel() {
    var id = this.elementId + '-counter';
    var $counterLabel = $('<span/>').attr('class', 'word-count');
    this.$targetElement.after($counterLabel);
    this.$counterLabel = $counterLabel;
  };


  /**
   * Update Character Count
   */
  WordCount.prototype.updateCharacterCount = function updateCharacterCount() {
    var currentLength = this.$targetElement.val().length;
    var remaining = this.limit - currentLength;
    var warningCss = 'warning';

    this.$counterLabel.html(remaining + ' characters remaining');

    if (remaining > -1) {
      this.$counterLabel.removeClass(warningCss);
    } else {
      this.$counterLabel.addClass(warningCss);
    }
  };

  return WordCount;
})();

