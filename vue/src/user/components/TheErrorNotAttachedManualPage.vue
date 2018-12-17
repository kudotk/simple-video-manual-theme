<template>
  <BaseMessageOnlyPage
    :title="'Unregistered Page'"
    :messages="['Not belongs in Manual book', 'Attach to Manual book']"
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
