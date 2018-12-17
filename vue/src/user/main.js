// The Vue build version to load with the `import` command.
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import i18n from './i18n'
import store from './store'
import './plugins/vuetify'
import 'material-design-icons/iconfont/material-icons.css'
import './assets/css/global.scss'

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  router,
  components: { App },
  template: '<App/>',
  i18n: i18n,
  store: store,
  render: h => h(App)
}).$mount('#app')

