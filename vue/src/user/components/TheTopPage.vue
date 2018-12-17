<template>
  <v-layout
    class="ma-0 pt-5 pb-5 topPage"
    align-center
    justify-center
    column
  >
    <h1
      class="display-1 primary--text mb-4"
    >Manual Book</h1>
    <p
      class="body-1 mb-4"
    >
      {{ $t('No Data') }}
    </p>
  </v-layout>
</template>

<script>
  import config from '../app/config'

  export default {
    created() {
      if (config.svm_args.svm_book_post_id) {
        // redirect to the list page.
        this.$router.replace({name: 'pages'})
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import '../assets/css/_sass-loader-import.scss';
  
  .topPage {
    height: 100vh;
  }
</style> 
