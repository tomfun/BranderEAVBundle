require(['brander-eav/manage'], function(ManageViewes6) {
  const ManageView = ManageViewes6.default;
  /*{
   el:            '#page_content',
   currentLocale: '{{ brander_eav_global.localeDefault }}',
   currentLocales: '{{ brander_eav_global.localesSupported|json_encode|raw }}'
   }*/
  const options = window.brndreavinitialdata;
  const manageView = new ManageView(window.brndreavinitialdata);
  manageView.render();
});
