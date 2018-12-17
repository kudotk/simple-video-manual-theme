// The Vue build version to load with the `import` command.
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import i18n from './i18n'
import VeeValidate from 'vee-validate'
import './assets/css/global.scss'

// for validation in metabox.
Vue.use(VeeValidate, {})

Vue.config.productionTip = false

// jquery is load by wordpress.

// set up metabox.
const metaBoxQuery = '.svm-Mbox'
if (jQuery(metaBoxQuery).length > 0) {
  const TheMetabox = require('./components/TheMetabox').default
  /* eslint-disable no-new */
  new Vue({
    components: {TheMetabox},
    template: '<TheMetabox/>',
    i18n: i18n,
    render: h => h(TheMetabox)
  }).$mount(metaBoxQuery)
}

// set up export page.
const exportQuery = '.svm-Exp'
if (jQuery(exportQuery).length > 0) {
  const TheExportPage = require('./components/TheExportPage').default
  /* eslint-disable no-new */
  new Vue({
    components: {TheExportPage},
    template: '<TheExportPage/>',
    i18n: i18n,
    render: h => h(TheExportPage)
  }).$mount(exportQuery)
}
