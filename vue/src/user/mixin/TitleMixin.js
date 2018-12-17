const isSSR = () => {
  // `VUE_ENV` can be injected with `webpack.DefinePlugin`
  return process.env.VUE_ENV === 'server'
}

/**
 * Page Title Helper Mixin
 *
 * @see https://vuejs.org/v2/style-guide/index.html#Private-property-names-essential
 * @see https://ssr.vuejs.org/guide/head.html
 */
export default {
  methods: {
    $_TitleMixin_setTitle (title) {
      if (title) {
        if (isSSR()) {
          this.$ssrContext.title = title
        } else {
          if (document.title !== title) {
            document.title = title
          }
        }
      }
    }
  }
}
