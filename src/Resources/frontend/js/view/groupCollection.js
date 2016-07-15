import Base from './baseCollection';
import _ from 'underscore';
import 'backbone.radio';


var BaseProto = Base.prototype;

export default Base.extend({
  templateName:       '@BranderEAV/Widgets/group.collection.twig',
  childViewContainer: 'ul',
  initialize(options) {
    BaseProto.initialize.call(this, _.extend(options, {channel: 'attribute-group'}));
  },

  events: {
    'click .add-new-model'() {
      this.channel.trigger('select', new this.collection.model());
    },
  },
});
