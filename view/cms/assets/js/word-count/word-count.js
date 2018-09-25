'use strict';

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError('Cannot call a class as a function'); } }

/**
 * Class Word Count
 */
var WordCount = (function () {

  /**
   * @param {String} elementId
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
    $('.md-editor').after($counterLabel);
    this.$counterLabel = $counterLabel;
  };

  /**
   * Update Word Count
   */
  WordCount.prototype.updateWordCount = function updateCharacterCount() {
    var words = this.$targetElement.val();
    words = words.replace(/\[\[[\w-=" ]+\]\]/gi,""); // exclude shortcodes
    words = words.replace(/<blockquote class="instagram-media" .*<\/blockquote>/gi,""); // exclude instagram
    words = words.replace(/<blockquote class="twitter-tweet" .*<\/blockquote>/gi,""); // exclude twitter

    // Convert markdown to html tags then remove html tags to get approximate word count
    var md = (marked(words));
    words = $(md).text();

    var count = words.split(/[\s]+/).filter(function(str){return str!="";}).length;

    this.$counterLabel.html(count + (count > 1 ? ' words' : ' word'));
  };

  return WordCount;
})();

