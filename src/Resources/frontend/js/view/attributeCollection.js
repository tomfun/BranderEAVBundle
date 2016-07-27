import Base from './baseCollection';
import _ from 'underscore';
import {render as template} from 'templates/brander-eav/Widgets/attribute.collection.twig';
import 'backbone.radio';
import 'jquery-ui';


var BaseProto = Base.prototype;

export default Base.extend({
  template,
  childViewContainer: 'ul',
  draggable:          true,
  initialize(options) {
    BaseProto.initialize.call(this, _.extend({channel: 'attribute', draggable: true}, options));
  },
});