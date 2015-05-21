# CHANGELOG


## v1.1.10
* issue #4: Add support for Markdown in article post contents. Add EpicEditor.


## v1.1.9
* issue #2: use function callbacks instead of /e regex modifier.  part of php5.6 upgrade fixes.
* issue #1: Move psr classes from `vendors` to `src` and use composer to load them.


## v1.1.8
* Added view handlers to _presenter.cft.  ticket #192.
* [WbdisplayFilterer] Adds "autoParagraph" method.
* Removed tweetology template and route.
* [WbxmlFilterer] convert now calls [WB\Common\Util\StringUtils::xmlEscape]
* [WB\Common\Util\StringUtils] created with xmlEscape method.


## v1.1.7
* Update Outbrain embed code. Ticket #160.
* Adding Presenter controller and loader. Add TweetUtils. Ticket #140
* Adds google/_universal-analytics-js.cft.  Should setup a new UA account or upgrade existing.
* Adds UseRemarketing param to google/_analytics-js.cft.
* Added widgets/_require-css.cft and widgets/_require-js.cft
* Adds [WbjsonFilterer] with "format" method.  For printing a human readable json string.
* Added [WB\SimpleFeed\SimpleFeedService]


## v1.1.6
* Added WbValidationHandler::rejectInlineSpreecast.
* Adding SetMetaWhereParams to WbneighborWebController


## v1.1.5
* Removed "match" validator and changed string length to "20" on @wb-mixin-sf-listtool
* added new test in WbValidationHandler at rejectInlinePoll to reject "cdn.polls" urls too


## v1.1.4
* Add new partners/_google-adsense.cft
* Adds QSA (boolean) parameter to WbqsredirectWebController
* Adds "#primary-image-destination-target" to @wb-mixin-primary-image-settings, implemented in widgets/_primary-image.cft
* Adds WbdateFilterer::secondsToHMS method
* Modifies short code notice on reject inline code validators to [[shortcode]].
* Wbdisplay::numberFormat param is now cast to string before float so meta objects are converted first.
* Modifies WbValidationHandler::requirePrimaryImage to look at tag role instead of aspects so any aspect that defines #primary-image will work.
* Adds WbdateFilterer::modify method.
* Adds cms template _subset-service-js.cft.


## v1.1.3
* Loosens up rejectInlineKaltura so external videos using kaltura can be embedded.  A very non-ideal solution to kaltura's hideous embed code.
* Makes Wbxml::convert bullet proof so it properly encoded bendy quotes and other fancy chars.
* Adds @wb-mixin-contents aspect and xmod.
* Adds trackers/_parsely.cft


## v1.1.2
* Update WbValidationHandler to check for tags in scope prior to validation.  Handles the validation errors that occur when bulk cms actions are executed since those only load the noderefs but
    also run in the cms context which expects the request to be coming from a cms edit screen. Already opened a discussion with CF about moving bulk cms action to either API context or its own context.


## v1.1.1
* All meebo related functionality moved to wb-meebo plugin.
* Added partners/_adsonar.cft for adsonar sponsored links.
* Added wbcache.durations pluginconfig and WbcacheFilterer.
* Added layout-renderer.cft.  Allows for automatic rendering of content into layouts via route variables.
* Moved @wb-mixin-sf-poll aspect and xmod to wb-polls plugin.
* Added #sf-listtool-stencil-id to @wb-mixin-sf-listtool
    - Sites need to implement handling for this field in widgets/_listtool.cft
    - Sites will need to make sure their ShortCodes handler passes the new variable.
* Moved WbkalturaFilterer.php to wb-videos plugin.
* Moved wbkaltura config props to wb-videos plugin.
* Moved @wb-mixin-kaltura-video aspect and xmod to wb-videos plugin and renamed to @wb-mixin-kaltura-videos
    - Sites currently using "@wb-mixin-kaltura-video" should now use "@wb-mixin-kaltura-videos".
    - Sites with video teasers should be using "@wb-mixin-kaltura-video".


## v1.1.0
* Moved WbpromotionsWebController to wb-promotions plugin.  Any sites using this plugin must install wb-promotions plugin in order to use the wbpromotions-* data source methods.
* Updated wbitter-linkify filter method to use twitter's new urls for hashtag search and user links.
* Added _wbt.cft for rendering dynamic template code.
* Removed widgets/_add-my-link.cft (now provided by wb-profiles 1.0.2)
* Removed widgets/_flag-content.cft (now provided by wb-profiles 1.0.2)
* Adds pluginconfig option for wblib.image.names, provides config for #embed-primary-image.
* Adds widgets/_primary-image.cft for embedding images that use @wb-mixin-primary-image-settings
* Adds route and template for tweetology iframe.
* Moved trackers/_google-analytics.cft to google/_analytics-js.cft
* Added config params for common trackers and partners (meebo, google analytics, chartbeat, etc.)
* Removes js/nielsen.js call from trackers/_nielsen.cft


## v1.0.4
* Adds support for multiple primary videos
* Adds google/_plusone-js.cft template
* Add WbeventFilterer that provides filter method that passes through locals to transport.
* Added meta #extra-kaltura-entry-ids to aspect wb-mixin-kaltura-video.xml.  It will be used to support extra videos on permalinks but is considered a temporary solution.  When kaltura videos are elements in the system this mixin will be converted to a tag widget.
* Added items method to WbfetchWebController.  Generic rss/atom feed reader datasource.
* Added explodeItems method to WblibWebController.  Allows for a datasource to be made from an exploded string.
* Added twitter/_widgets-js.cft which includes the twitter widget js call.


## v1.0.0
* Initial version.