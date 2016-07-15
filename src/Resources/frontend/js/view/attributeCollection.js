import Base from './baseCollection';
import _ from 'underscore';
import 'backbone.radio';
import 'jquery-ui';


var BaseProto = Base.prototype;

export default Base.extend({
  templateName:       '@BranderEAV/Widgets/attribute.collection.twig',
  childViewContainer: 'ul',
  draggable:          true,
  initialize(options) {
    BaseProto.initialize.call(this, _.extend({channel: 'attribute', draggable: true}, options));
  },
});
