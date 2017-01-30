/**
 * Customizes the tag widget so it tells the user if they are
 * selecting a teaser or not.
 *
 * these tag widgets are disgusting
 */
var WbRelatedLinkTagWidget = function (node, tagPartial, domID, options, mdContainer) {
  options = $.extend({
    ShowThumbnailsInSearchResults: true
  }, options || {});

  WbRelatedLinkTagWidget.superclass.constructor.call(this, node, tagPartial, domID, options);
  this.DOM.mdContainer = $(mdContainer);
};
extend(WbRelatedLinkTagWidget, NodeTagWidget);

//EXTERNAL EVENT HANDLERS
WbRelatedLinkTagWidget.prototype._handlePostInitialize = function() {
  var me = this;

  // Add button back that markdown added that was removed by TagWidget during initialization
  var buttonIconContainer = $('<span/>');
  buttonIconContainer.addClass('glyphicon glyphicon-plus-sign');
  buttonIconContainer.prependTo(me.DOM.container)

  me.DOM.container.after(me.DOM.searchContainer);
};

WbRelatedLinkTagWidget.prototype.enterSearchKeyword = function (keyword) {
  var me = this;

  if (keyword == AbstractTagWidget.CONSTANTS.START_TYPING || keyword.length < 2) {
    this.DOM.searchResultsTotalLabel.text('Please enter a search query.').removeClass('loading');
    return;
  }

  this.SearchOffset = 1;
  this.DOM.searchResultsTotalLabel.text('Searching. Please Wait...').addClass('loading');

  var params = $.extend({
    'q': keyword,
    'per_page': this.Options.SearchLimit,
    'page': this.SearchOffset++
  }, this.Options.SearchParameters || {});

  var successCallback = function (json, xhr) {
    var nodeQuery = new NodeQuery();
    nodeQuery.setTotalRecords(json.TotalRecords);
    nodeQuery.setResults(json.Nodes);

    me._renderSearchResults.apply(me, [nodeQuery, false]);

    //RESET SCROLL PANE TO THE TOP, BROWSER WILL REMEMBER THE SCROLL POSITION BETWEEN LOADS WHICH
    //CAUSES THE AJAX SCROLL-AHEAD TO KEEP LOADING UP TO THE LAST SCROLL POSITION
    //FURTHER NOTE: jQuery scrollTo PLUGIN USES A TIMEOUT WHICH DOESN'T WORK HERE, NEEDS TO BE SEQUENTIAL
    me.DOM.searchResultsScrollPane[0].scrollTop = 0;
  };

  var errorCallback = function (xhr, msg) {
    //me.closeWidget.apply(me);
  };

  $.ajax({
    url: '/wb-teasers-search/tag-widget.json',
    dataType: 'json',
    type: 'GET',
    data: params,
    async: true,
    success: successCallback,
    error: errorCallback
  });
};

WbRelatedLinkTagWidget.prototype.scroll = function (event) {
  //SEARCH RESULTS FULLY LOADED, NO NEED TO CONTINUE
  if (this.TotalRecords == this.DOM.searchResultsList.children().length) {
    return;
  }

  var pane = $(event.target);
  var ratio = (pane[0].scrollTop + pane.height()) / this.DOM.searchResultsList.height();

  //WE HAVE HIT THE 80% MARK ON THE SCROLL PANE; LOAD MORE SEARCH RESULTS
  if (ratio >= (this.Options.ScrollThreshold / 100.0)) {
    if (this.Locks.scroll) {
      event.preventDefault();
      return;
    }

    this.Locks.scroll = true;

    var me = this;

    var keyword = this.DOM.searchInput.val();
    if (keyword == AbstractTagWidget.CONSTANTS.START_TYPING || keyword.length < 2) {
      this.Locks.scroll = false;
      this.DOM.searchResultsTotalLabel.text('Please enter a search query.').removeClass('loading');
      return;
    }

    var params = $.extend({
      'q': keyword,
      'per_page': this.Options.SearchLimit,
      'page': this.SearchOffset++
    }, this.Options.SearchParameters || {});

    var successCallback = function (json, xhr) {
      var nodeQuery = new NodeQuery();
      nodeQuery.setTotalRecords(json.TotalRecords);
      nodeQuery.setResults(json.Nodes);

      me._renderSearchResults.apply(me, [nodeQuery, true]);
      me.Locks.scroll = false;
    };

    var errorCallback = function (xhr, msg) {
      //me.closeWidget.apply(me);
    };

    $.ajax({
      url: '/wb-teasers-search/tag-widget.json',
      dataType: 'json',
      type: 'GET',
      data: params,
      async: true,
      success: successCallback,
      error: errorCallback
    });
  }
};

WbRelatedLinkTagWidget.prototype._postRenderSearchResult = function (li, node, index, total) {
  var $link = $(li).find('.choice-link');
  var $em = $link.find('em');

  var str = node.Status;
  str += ' : ' + (node.metas.target_node_ref || node.metas.target_curie);
  str += ' : ' + node.ActiveDate.substr(0, 10);

  $em.css({fontWeight: 'normal', color: '#999'}).html(this._postRenderSearchResultFilterText(node, str));

  if (this.Options.ShowThumbnailsInSearchResults) {
    var thumbnailUrl = node.metas['thumbnail_100x100'] || node.metas['thumbnail_150'];
    if (thumbnailUrl) {
      $link.prepend($('<img class="thumbnail" src="' + thumbnailUrl + '">'));
      $(li).append($('<div style="clear:both"></div>'));
    }
  }
};

WbRelatedLinkTagWidget.prototype._postRenderChosen = function(li, index){
  var me = this;
  var mdTextarea = $(me.DOM.mdContainer)[0];

  console.log(me.taggableObject.outTags);
  if(me.taggableObject.outTags.length == 0) return;
  $.each(me.taggableObject.outTags.reverse(), function(i, tag) {
    if (tag.TagElement === 'denormalized-teaser' && tag.TagRole === 'story') {

      var shortCode = '[[related-link id="' + tag.TagSlug + '"]]';
      var selectedLength = mdTextarea.selectionEnd - mdTextarea.selectionStart;

      var selected = {
        start: mdTextarea.selectionStart,
        end: mdTextarea.selectionEnd,
        length: selectedLength,
        text: mdTextarea.value.substr(mdTextarea.selectionStart, selectedLength)
      };

      mdTextarea.value = mdTextarea.value.substr(0, mdTextarea.selectionStart) + "\n" + shortCode + "\n"
        + mdTextarea.value.substr(mdTextarea.selectionEnd, mdTextarea.value.length);

      var cursor = selected.start + shortCode.length + 2;

      mdTextarea.selectionStart = cursor;
      mdTextarea.selectionEnd = cursor;

      return false;
    }
  });

  $(me.DOM.container).find('ol').hide();
  $(me.DOM.container).find('label').remove();
};

/**
 * Allows for customization of the string that gets appended to the node title
 * in the tag widget results.  Override at site level to add more metas.
 *
 * @param {NodeObject} node
 * @param {string} str
 * @returns {string}
 */
WbRelatedLinkTagWidget.prototype._postRenderSearchResultFilterText = function (node, str) {
  return str;
};
