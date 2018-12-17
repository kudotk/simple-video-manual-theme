import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import '../assets/stylus/main.styl'
import i18n from '../i18n'

Vue.use(Vuetify, {
  iconfont: 'md',
  theme: {
    primary: '#165e83',
  },
  // @see https://vuetifyjs.com/en/framework/internationalization
  lang: {
    t: (key, ...params) => i18n.t(key, params)
  }
})
