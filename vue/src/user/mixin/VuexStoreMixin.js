/**
 * Vuex Store Helper Mixin
 *
 * Define "$_VuexStoreMixin_subscribe" in a component.
 * It's called when store is committed.
 */
export default {
  created () {
    if (this.$store && (typeof this.$_VuexStoreMixin_subscribe === 'function')) {
      this.$store.subscribe((mutation, state) => {
        this.$_VuexStoreMixin_subscribe.call(this, mutation, state)
      })
    }
  },
  methods: {
    /**
     * Vuex subscribe handler
     */
    // $_VuexStoreMixin_subscribe (mutation, state) {
    //   return
    // }
  }
}
