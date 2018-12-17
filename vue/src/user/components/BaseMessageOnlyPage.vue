<template>
  <v-container
    class="ma-0 pa-0 base"
    fluid
  >
    <header class="pt-3 pl-4">
      <a href="/">
        <h1 class="title">Manual Book</h1>
      </a>
    </header>
    <p class="text-xs-center headline mb-5 message">
      <span
        v-for="(message, index) in messages"
        :key="index"
      >
        {{ $t(message) }}
      </span>
    </p>
  </v-container>
</template>

<script>
  import titleMixin from '../mixin/TitleMixin'

  export default {
    name: 'BaseMessageOnlyPage',
    mixins: [titleMixin],
    props: {
      title: String,
      messages: Array,
    },
    created() {
      this.$store.commit('setPageTitle', this.$t(this.title))
      this.$_TitleMixin_setTitle(this.$store.getters.siteTitle)
    },
  }
</script>

<style lang="scss" scoped>
  .base {
    display: flex;
    flex-direction: column;
    height: 100vh;

    .message {
      display: flex;
      flex: 1;
      flex-direction: column;
      justify-content: center;
    }
  }
</style>
