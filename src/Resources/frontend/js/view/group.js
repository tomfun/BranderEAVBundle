import SetView from './set';
import {render as template} from 'templates/brander-eav/Widgets/group.one.twig';
import 'backbone';
import 'brander-eav/view/attributeCollection';
import 'brander-eav/view/attributeItem';
import 'backbone.modelbinder';
import 'jquery-ui';


var BaseProto = SetView.prototype;

export default SetView.extend({
  template,

  initialize(options) {
    BaseProto.initialize.call(this, {channel: 'attribute-group'});
    this.currentLocale = options.currentLocale;
  },

  serializeData() {
    return {
      model:         this.model,
      currentLocale: this.currentLocale,
      lcl:           this.model
                       ? this.model.get('translations').indexOfLocale(this.currentLocale) : undefined,
    };
  },

  changeLocale(newLocale) {
    if (!newLocale) {
      console.warn('locale is not defined');
      return;
    }
    this.currentLocale = newLocale;
    this.render();
  },
});
