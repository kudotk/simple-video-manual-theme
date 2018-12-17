<template>
  <BaseMessageOnlyPage
    :title="'Page Not Found'"
    :messages="['Page Not Found']"
  />
</template>

<script>
  import BaseMessageOnlyPage from './BaseMessageOnlyPage'

  export default {
    components: {
      BaseMessageOnlyPage
    },
  }
</script>
