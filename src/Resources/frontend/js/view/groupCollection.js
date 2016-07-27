import Base from './baseCollection';
import _ from 'underscore';
import {render as template} from 'templates/brander-eav/Widgets/group.collection.twig';
import 'backbone.radio';


var BaseProto = Base.prototype;

export default Base.extend({
  template,

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
