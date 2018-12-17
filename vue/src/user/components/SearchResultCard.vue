<template>
  <v-card
    class="card mb-4"
    ripple
    hover
    ref="card"
    :tabindex="tabIndex"
  >
    <v-card-title
      class="card_Content"
      primary-title
    >
      <div>
        <p
          class="title resultTitle ma-0 pa-0 text--primary"
          v-html="page.title"
        />
        <p
          class="body-2 mt-3 resultText text--primary"
          v-html="page.text"
        />
      </div>
    </v-card-title>
  </v-card>
</template>

<script>
  export default {
    name: 'SearchResultCard',
    props: {
      page: Object,
      itemIndex: Number,
      cursorIndex: Number,
    },
    computed: {
      tabIndex() {
        return (this.itemIndex === this.cursorIndex) ? 0 : -1
      },
    },
    methods: {
      focus() {
        $(this.$refs.card.$el).focus()
      },
    }
  }
</script>

<style lang="scss" scoped>
  @import '../assets/css/_sass-loader-import.scss';

  .card {
    &:focus:hover {
      outline: none;
    }

    .resultTitle /deep/ code, .resultText /deep/ code {
      background-color: $color-code-bg;
      padding: 0 .2rem;
      margin: 0 .1rem;
      &:before, &:after {
        content: ""
      }
    }
  }
</style>
