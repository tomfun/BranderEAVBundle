import Backbone from 'backbone';
import ViewAttrCollection from './view/attributeCollection';
import ViewAttrItem from './view/attributeItem';
import ViewAttribute from './view/attribute';
import {render as mainTemplate} from 'templates/brander-eav/Manage/main.twig';
import ViewSetCollection from './view/setCollection';
import ViewSetItem from './view/setItem';
import ViewSet from './view/set';
import ViewGroupCollection from './view/groupCollection';
import ViewGroupItem from './view/groupItem';
import ViewGroup from './view/group';
import AttributeCollection from './eav/attributeCollection';
import AttributeGroupCollection from './eav/attributeGroupCollection';
import AttributeSetCollection from './eav/attributeSetCollection';


var ManageView = Backbone.View.extend({
  template: mainTemplate,
  initialize(options) {
    var attrCollection  = new AttributeCollection({manage: true}),
      groupCollection = new AttributeGroupCollection({manage: true}),
      setCollection   = new AttributeSetCollection({manage: true});
    attrCollection.fetch();
    groupCollection.fetch();
    setCollection.fetch();
    this.viewAttrCollection = new ViewAttrCollection({
      collection: attrCollection,
      childView:  ViewAttrItem,
    });
    this.viewAttribute = new ViewAttribute({
      currentLocale:  options.currentLocale,
      currentLocales: options.currentLocales,
    });

    this.viewSetCollection = new ViewSetCollection({
      collection: setCollection,
      childView:  ViewSetItem,
    });
    this.viewSet = new ViewSet();

    this.viewGroupCollection = new ViewGroupCollection({
      collection: groupCollection,
      childView:  ViewGroupItem,
    });
    this.viewGroup = new ViewGroup({
      currentLocale: options.currentLocale,
    });

    this.viewAttribute.on('locale:changing', function (locale) {
      this.viewGroup.changeLocale(locale);
    }, this);
  },

  render() {
    this.$el.html(this.template());
    this.viewAttrCollection.setElement('#attributes');
    this.viewAttrCollection.render();
    this.viewAttribute.setElement('#attribute');
    this.viewAttribute.render({noEffect: true});
    this.viewSetCollection.setElement('#attribute-set-list');
    this.viewSetCollection.render();

    this.viewSet.setElement('#attribute-set');
    this.viewSet.render();
    this.viewGroupCollection.setElement('#attribute-group-list');
    this.viewGroupCollection.render();
    this.viewGroup.setElement('#attribute-group');
    this.viewGroup.render();
  },
});

export default ManageView;
