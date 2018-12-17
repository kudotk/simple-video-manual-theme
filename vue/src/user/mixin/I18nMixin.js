/**
 * I18n Helper Mixin
 *
 * Set up html lang attribute.
 */
const defaultLocale = 'en'

export default {
  created () {
    if (!this.$i18n) {
      return
    }
    
    if (!document.querySelector('html')) {
      return
    }

    let lang = document.querySelector('html').getAttribute('lang')
    if (!lang) {
      // get lang from browser environment.
      lang = navigator.language
    }

    if (!lang) {
      lang = defaultLocale
    }
    lang = defaultLocale
    
    if (this.$i18n.locale !== lang) {
      this.$i18n.locale = lang
      document.querySelector('html').setAttribute('lang', lang)
    }
  }
}
