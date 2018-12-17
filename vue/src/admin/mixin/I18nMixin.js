/**
 * I18n Helper Mixin
 *
 * Set up html lang attribute.
 */
import config from '../app/config'

const defaultLocale = 'en'

export default {
  created () {
    if (!this.$i18n) {
      return
    }

    // Set locale by wordpress locale
    let locale = defaultLocale 
    if (config.locale) {
      if (config.locale.match(/ja/)) {
        locale = 'ja'
      }
    }
    this.$i18n.locale = locale
  }
}
