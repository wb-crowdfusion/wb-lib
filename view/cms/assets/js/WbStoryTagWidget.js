/**
 * Customizes the tag widget so it tells the user if they are
 * selecting a teaser or not.
 *
 * these tag widgets are disgusting
 */
var WbStoryTagWidget = function (node, tagPartial, domID, options) {
  options = $.extend({
    ShowThumbnailsInSearchResults: true
  }, options || {});

  WbStoryTagWidget.superclass.constructor.call(this, node, tagPartial, domID, options);
};
extend(WbStoryTagWidget, NodeTagWidget);
//EXTERNAL EVENT HANDLERS
WbStoryTagWidget.prototype._handleInitialized  = function() {
  var me = this;

  var ESC = function(event){
    if(event.keyCode == 27/*ESC*/) {
      event.preventDefault();
      event.stopPropagation();
      me.closeWidget();
    }
  };

  //INITIALIZE MAIN DOM CONTAINER
  this.DOM.container.empty().addClass("tag-widget");

  this.DOM.container[0].getWidget = function(){
    return me;
  };


  //BUILD ALL WIDGET UI COMPONENTS
  this.DOM.label = $('<label>'+(this.Options.AllowMultiple?this.Options.LabelPlural:this.Options.Label)+'</label>');

  this.DOM.clearChosenListButton = $('<a href="#" title="Clear List">[Clear]</a>').css({display:'none'})
    .click(function(event){

      if(me.Options.WarnOnClearChosenList && !confirm("Clear all items? This action cannot be undone.")) {
        event.preventDefault();
        return;
      }

      me.clearChosenItems.apply(me,[event]);
    });

  this.DOM.reorderChosenListButton = $('<a href="#" title="Reorder List">[Reorder]</a>').css({display:'none'})
    .click(function(event){
      me.activateReordering.apply(me,[event]);
    });

  this.DOM.reorderChosenListFinishButton = $('<a href="#" title="Done Reordering">[Done Reordering]</a>').css({display:'none'})
    .click(function(event){
      me.deactivateReordering.apply(me,[event]);
    });

  this.DOM.undoRemoveButton = $('<a href="#" title="Undo Remove">[Undo]</a>').css({display:'none'});

  /*this.DOM.activateButton = $('<a class="activate-link" href="#"><span>'+(this.Options.AllowMultiple?"Add":"Choose")+' '+this.Options.ActivateButtonLabel+'</span></a>')
   .click(function(event){
   me.activateWidget.apply(me,[event]);
   });*/

  this.DOM.activateButton = $('<a class="activate-link" href="#"><span class="glyphicon glyphicon-plus-sign"></span></a>')
    .click(function(event){
      me.activateWidget.apply(me,[event]);
    });


  //CREATE THE DOM REQUIRED FOR THE SEARCH COMPONENT
  this.DOM.searchContainer = $('<div class="tag-widget-search-container"></div>').css({display:'none'}).keyup(ESC);
  this.DOM.searchClippingContainer = $('<div class="tag-widget-search-clipping-container"></div>');
  this.DOM.searchShadowTop = $('<div class="tag-widget-search-shadow-top"><div></div></div>');
  this.DOM.searchShadowContainer = $('<div class="tag-widget-search-shadow-container"></div>');
  this.DOM.searchContentContainer = $('<div class="tag-widget-search-content-container"></div>');
  this.DOM.searchInput = $('<input autocomplete="false" type="text" value="'+AbstractTagWidget.CONSTANTS.START_TYPING+'"/>')
    .keydown(function(event){
      me.processSearchInputKeyDown.apply(me,[event]);
    })
    .click(function(event){
      if(me.DOM.searchInput.val() == AbstractTagWidget.CONSTANTS.START_TYPING)
        me.DOM.searchInput[0].select();
    })
    .inputFocus();
  this.DOM.clearSearchButton = $('<a class="clear-link" href="#" title="Clear Search">Clear</a>')
    .click(function(event){
      me.clearSearch.apply(me,[event]);
    });
  this.DOM.closeButton = $('<a class="close" href="#">Close</a>')
    .click(function(event){
      me.closeWidget.apply(me,[event]);
    });
  this.DOM.searchResultsContainer = $('<div class="tag-widget-search-results-container"></div>');
  this.DOM.searchResultsScrollPane = $('<div class="tag-widget-search-results-scrollpane"></div>')
    .scroll(function(event){
      me.scroll.apply(me,[event]);
    });
  this.DOM.searchResultsList = $('<ol></ol>');
  this.DOM.searchResultsToolbar = $('<div class="tag-widget-search-results-toolbar"></div>');
  this.DOM.searchResultsTotalLabel = $('<span>&nbsp;</span>');
  this.DOM.searchResultsQuickAddTypeSelect = $('<select><option value="-1"></option></select>');
  this.DOM.searchResultsQuickAddInput = $('<input type="text" value=""/>');
  this.DOM.searchResultsQuickAddButton = $('<a href="#" title="Quick Add '+this.Options.Label+'">Add '+this.Options.QuickAddLabel+'</a>')
    .click(function(event){
      me.quickAdd.apply(me,[event]);
    });
  this.DOM.searchResultsQuickAddMessage = $('<span class="quick-add-message loading">Adding. Please Wait...</span>').css({display:'none'});
  this.DOM.searchShadowRight = $('<div class="tag-widget-search-shadow-right"></div>');
  this.DOM.searchShadowBottom = $('<div class="tag-widget-search-shadow-bottom"><div></div></div>');
  this.DOM.searchIcon = $('<div class="tag-widget-search-icon"></div>');

  //ASSEMBLE THE SEARCH COMPONENT DOM
  this.DOM.searchContainer
    .append(this.DOM.searchClippingContainer)
    .append(this.DOM.searchShadowBottom)
    .append(this.DOM.searchIcon);

  this.DOM.searchClippingContainer
    .append(this.DOM.searchShadowTop)
    .append(this.DOM.searchShadowContainer);

  this.DOM.searchShadowContainer
    .append(this.DOM.searchContentContainer)
    .append(this.DOM.searchShadowRight);

  this.DOM.searchContentContainer
    .append(this.DOM.searchInput)
    .append(this.DOM.clearSearchButton)
    .append(this.DOM.closeButton)
    .append(this.DOM.searchResultsContainer);

  this.DOM.searchResultsContainer
    .append(this.DOM.searchResultsScrollPane)
    .append(this.DOM.searchResultsToolbar);

  this.DOM.searchResultsScrollPane
    .append(this.DOM.searchResultsList);

  this.DOM.searchResultsToolbar
    .append(this.DOM.searchResultsTotalLabel);
  if(this.Options.AllowQuickAdd && this._isMultiType()) {
    this.DOM.searchResultsToolbar
      .append(this.DOM.searchResultsQuickAddTypeSelect);
    this.DOM.searchResultsQuickAddTypeSelect.empty();
    $.each(SystemService.getElementsByAspect(this.Options.QuickAddElement.substring(1)),function(i,element){
      me.DOM.searchResultsQuickAddTypeSelect.append($('<option value="'+element.Slug+'">'+element.Name+'</option>'));
    });
  }
  if(this.Options.AllowQuickAdd)
    this.DOM.searchResultsToolbar
      .append(this.DOM.searchResultsQuickAddInput)
      .append(this.DOM.searchResultsQuickAddButton)
      .append(this.DOM.searchResultsQuickAddMessage);


  this.DOM.chosenList = $('<ol></ol>');
  this._renderChosenList();

  this.DOM.chosenReorderList = $('<div class="sortable"></div>').css({display:'none'});


  //CREATE THE DOM REQUIRED FOR THE ITEM OPTIONS COMPONENT
  this.DOM.itemOptionsContainer = $('<div class="tag-widget-item-options-container"></div>').css({display:'none'});
  this.DOM.itemOptionsClippingContainer = $('<div class="tag-widget-item-options-clipping-container"></div>');
  this.DOM.itemOptionsShadowTop = $('<div class="tag-widget-item-options-shadow-top"><div></div></div>');
  this.DOM.itemOptionsShadowContainer = $('<div class="tag-widget-item-options-shadow-container"></div>');
  this.DOM.itemOptionsContentContainer = $('<div class="tag-widget-item-options-content-container"></div>');
  this.DOM.itemOptionsFormContainer = $('<div class="tag-widget-item-options-form-container"></div>');
  this.DOM.itemOptionsValuesContainer = $('<div class="tag-widget-item-options-values-container"></div>');
  this.DOM.itemOptionsCancelButton = $('<a class="cancel" href="#" title="Cancel">Cancel</a>');
  this.DOM.itemOptionsDoneButton = $('<a class="done" href="#" title="Done">Done</a>');
  this.DOM.itemOptionsLabel = $('<span></span>');
  this.DOM.itemOptionsList = $('<ul></ul>');
  this.DOM.itemOptionsTypeinInput = $('<input type="text"/>');
  this.DOM.itemOptionsAddButton = this.Options.AllowMultipleValues ? $('<a href="#" class="add" title="Add">Add</a>') : $('<a href="#" class="add" title="Save">Save</a>');
  this.DOM.itemOptionsShadowRight = $('<div class="tag-widget-item-options-shadow-right"></div>');
  this.DOM.itemOptionsShadowBottom = $('<div class="tag-widget-item-options-shadow-bottom"><div></div></div>');

  //ASSEMBLE THE ITEM OPTIONS COMPONENT DOM
  this.DOM.itemOptionsContainer
    .append(this.DOM.itemOptionsClippingContainer)
    .append(this.DOM.itemOptionsShadowBottom);

  this.DOM.itemOptionsClippingContainer
    .append(this.DOM.itemOptionsShadowTop)
    .append(this.DOM.itemOptionsShadowContainer);

  this.DOM.itemOptionsShadowContainer
    .append(this.DOM.itemOptionsContentContainer)
    .append(this.DOM.itemOptionsShadowRight);

  this.DOM.itemOptionsContentContainer
    .append(this.DOM.itemOptionsFormContainer);

  this.DOM.itemOptionsFormContainer
    .append(this.DOM.itemOptionsValuesContainer);

  this.DOM.itemOptionsValuesContainer
    .append(this.DOM.itemOptionsCancelButton)
    .append(this.DOM.itemOptionsDoneButton)
    .append(this.DOM.itemOptionsLabel)
    .append(this.DOM.itemOptionsList)
    .append($('<div style="clear:both;></div>'));
  if(this.Options.ValueMode == 'typein') {
    this.DOM.itemOptionsValuesContainer
      .append(this.DOM.itemOptionsTypeinInput)
      .append(this.DOM.itemOptionsAddButton);
  }




  //ASSEMBLE TOP-LEVEL WIDGET UI COMPONENTS
  this.DOM.container
    .append(this.DOM.label
      .append(this.Options.AllowClearChosenList && this.Options.ShowChosenList && !me.Options.ReadOnly ? this.DOM.clearChosenListButton : null)
      .append(this.Options.AllowReorderChosenList && this.Options.ShowChosenList && !me.Options.ReadOnly ? this.DOM.reorderChosenListButton : null)
      .append(this.Options.AllowReorderChosenList && this.Options.ShowChosenList ? this.DOM.reorderChosenListFinishButton : null)
      .append(this.Options.AllowRemoveUndo && this.Options.ShowChosenList ? this.DOM.undoRemoveButton : null)
    )
    .append(this.Options.ShowChosenList ? this.DOM.chosenList : null)
    .append(this.Options.ShowChosenList && !me.Options.ReadOnly ? this.DOM.chosenReorderList : null)
    .append(this.Options.ShowActivateButton && !me.Options.ReadOnly ? this.DOM.activateButton : null)
    .after($('<div style="clear:both;"></div>'))
    .after(this.Options.ShowChosenList && this.Options.ValueMode != 'none' ? this.DOM.itemOptionsContainer : null)
    .after(this.Options.ShowActivateButton ? this.DOM.searchContainer : null);


  //SET THE PARENT OF THE TAG WIDGET TO RELATIVE SO THE SEARCH AND ITEM OPTION PANELS CAN BE
  //POSITIONED CORRECTLY
  $(this.DOM.container.parent().get(0)).css({position:'relative'});


  //AUTOMATICALLY CLOSE WIDGET WHEN ANOTHER WIDGET ON THE PAGE IS ACTIVATED
  $(document).bind(AbstractTagWidget.EVENTS.WIDGET_ACTIVATED,function(event,widgetUUID){
    me._handleWidgetActivatedEvent.apply(me,[widgetUUID]);
  });

  //INITIALIZE QUICK ADD
  if(this.Options.AllowQuickAdd)
    this.DOM.searchContainer.addClass("quick-add");

  //INITIALIZE MULTI-TYPE
  if(this._isMultiType())
    this.DOM.searchContainer.addClass("multi-type");

  //ADD CLASS FOR ADD MULTIPLE/CHOOSE ONE
  this.DOM.searchContainer.addClass(this.Options.AllowMultiple?"multiple":"single");
  this.DOM.activateButton.addClass(this.Options.AllowMultiple?"multiple":"single");

  //If this is a prepend tag widget, move the activateButton to the top
  if(this.Options.TagPrepend){
    this.DOM.chosenList.before(this.DOM.activateButton);
    this.DOM.chosenList.css({float:'left'});
  }
};

WbStoryTagWidget.prototype.enterSearchKeyword = function (keyword) {
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

WbStoryTagWidget.prototype.scroll = function (event) {
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

WbStoryTagWidget.prototype._postRenderSearchResult = function (li, node, index, total) {
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

WbStoryTagWidget.prototype._postRenderChosen = function(li, index){
  var me = this;

  if(me.taggableObject.outTags.length == 0) return;

  $.each(me.taggableObject.outTags, function(i, tag) {
    if (tag.TagElement === 'denormalized-teaser') {
      var itemHtml = '<div id="' + tag.TagSlug + '" class="dz-preview dz-image-preview js-tagwidget" data-id="'
        + tag.TagSlug + '"><div class="dz-image"><img alt="' + tag.TagLinkTitle + '" src="' + tag.TagLinkNode.ThumbnailUrl
        + '"></div><div class="dz-details"><div class="dz-filename"><span>' + tag.TagLinkTitle
        + '</span></div></div><button class="dz-remove remove-photo js-remove-tagged">&#215; Remove</button></div>';
      // $(itemHtml).insertAfter('.dz-message');
      $("#contents-md").insertAtCursor(itemHtml);
    }

  });

  $(me.DOM.container).find('ol').hide();

  $.fn.extend({
    insertAtCursor: function(myValue) {
      return this.each(function(i) {
        if (document.selection) {
          //For browsers like Internet Explorer
          this.focus();
          sel = document.selection.createRange();
          sel.text = myValue;
          this.focus();
        }
        else if (this.selectionStart || this.selectionStart == '0') {
          //For browsers like Firefox and Webkit based
          var startPos = this.selectionStart;
          var endPos = this.selectionEnd;
          var scrollTop = this.scrollTop;
          this.value = this.value.substring(0, startPos) + myValue +
            this.value.substring(endPos,this.value.length);
          this.focus();
          this.selectionStart = startPos + myValue.length;
          this.selectionEnd = startPos + myValue.length;
          this.scrollTop = scrollTop;
        } else {
          this.value += myValue;
          this.focus();
        }
      })
    }
  });
};

/**
 * Allows for customization of the string that gets appended to the node title
 * in the tag widget results.  Override at site level to add more metas.
 *
 * @param {NodeObject} node
 * @param {string} str
 * @returns {string}
 */
WbStoryTagWidget.prototype._postRenderSearchResultFilterText = function (node, str) {
  return str;
};
