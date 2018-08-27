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
    _classCallCheck(this, WordCount);

    this.elementId = elementId;
    this.$targetElement = $('#' + elementId);
    this.$counterLabel = null;

    this.createCounterLabel();

    this.updateWordCount();

    this.$targetElement
      .blur(this.updateWordCount.bind(this))
      .keyup(this.updateWordCount.bind(this));
  }


  /**
   * Creates counter span field
   */
  WordCount.prototype.createCounterLabel = function createCounterLabel() {
    var $counterLabel = $('<span/>').attr('class', 'word-count');
    this.$targetElement.after($counterLabel);
    this.$counterLabel = $counterLabel;
  };

  /**
   * Update Word Count
   */
  WordCount.prototype.updateWordCount = function updateCharacterCount() {
    var words = this.$targetElement.val();

    words = words.replace(/(^\s*)|(\s*$)/gi,"");//exclude  start and end white-space
    words = words.replace(/[ ]{2,}/gi," ");//2 or more space to 1
    words = words.replace(/\n /,"\n"); // exclude newline with a start spacing

    var count = words.split(' ').filter(function(str){return str!="";}).length;

    this.$counterLabel.html(count + (count > 1 ? ' words' : ' word'));
  };

  return WordCount;
})();

