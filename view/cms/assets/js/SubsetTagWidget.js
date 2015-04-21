
var SubsetTagWidget = function(page,tagPartial,domID,options) {

    SubsetTagWidget.superclass.constructor.call(this,page,tagPartial,domID,options);

    this.DefaultService = (options.DependsOn != undefined) ? SubsetService.getInstance(options.DependsOn) : NodeService.getInstance();
};
extend(SubsetTagWidget,AbstractTagWidget);


SubsetTagWidget.prototype._itemRemoved = function(tagpartial) {
    if (this.Options.ProviderFor != undefined) {
        var dependentPartial = new TagPartial(tagpartial);

        dependentPartial.TagRole = this.Options.ProviderFor;

        this.taggableObject.removeTags(this.Options.TagDirection,dependentPartial); // TODO: could dependent have different tagDir than provider?
    }
};
