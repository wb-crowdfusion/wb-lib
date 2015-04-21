/*
THE ROSCO SINGLETON-FACTORY PATTERN:

var T = (function(){

    var me = function() {
            var d = new Date();

            return {
                a : function() {
                    return d;
                },
                getInstance : function() {
                    return me();
                }
            }
        };

    return {
        getInstance : me
    };
})().getInstance();

var newT = T.getInstance();

var notTheSame = newT.a() != T.a();

*/

var SubsetService = (function() {

    var newInstance = function(provider) {

        var UUID = 'subsetservice-'+(new Date().getTime())+Math.floor(Math.random()*1024);

        /* public methods */
        return {

            getProvider : function() {
                return provider;
            },

            getUUID : function() {
                return UUID;
            },

            getInstance : function(provider) {
                return newInstance(provider);
            },

            findAll : function(nodeQuery,options) {
                var results = new Array();
                var providerPartial = new TagPartial(null,null,this.getProvider());
                var tags = document.taggableRecord.getOutTags(providerPartial);

                for (var i = 0; i < tags.length; i++) {
                    var tag = tags[i];
                    var node = new NodeObject();
                    node.Title = tag.TagLinkTitle;
                    node.Element = { Slug: tag.TagElement };
                    node.Slug = tag.TagSlug;
                    node.RecordLink = tag.TagLinkURL;

                    results[i] = node;
                }

                nodeQuery.setTotalRecords(results.length);
                nodeQuery.setResults(results);
                options.success(nodeQuery);

            }
        };
    };

    return {
        getInstance : newInstance
    }
})().getInstance('??');
