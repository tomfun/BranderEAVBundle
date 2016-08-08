import SetItemView from './setItem';
import {render as template} from 'templates/brander-eav/Widgets/group.item.twig';

export default SetItemView.extend({
  template,
  serializeData() {
    const first = this.model.get('translations').find({locale: this.options.currentLocale});
    return {
      currentLocale: this.options.currentLocale,
      // lcl:           this.model.get('translations').indexOfLocale(this.currentLocale),
      title:         first ? first.get('title') : '',
      model:         this.model,
    };
  },
});
