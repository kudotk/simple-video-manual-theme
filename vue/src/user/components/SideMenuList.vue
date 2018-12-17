<template>
  <v-list
    class="pa-0 searchMenu"
    subheader
  >
    <v-subheader class="text--secondary menuSubheader">
      {{ $t('Table of contents') }}
    </v-subheader>
    <div
      @keydown.up="keyUpDown(true)"
      @keydown.down="keyUpDown(false)"
      @keydown.enter.space="keyEnter"
    >
      <SideMenuListTile
        v-for="(item, index) in items"
        :key="item.id"
        :item="item"
        :item-index="index"
        :current-page-id="currentPageId"
        :cursor-index="cursorIndex"
        :is-focus-in-list="isFocusIn"
        :ref="'item' + index"
        @click-item="clickItem"
        @focus-item="focusItem"
        @blur-item="blurItem"
      />
    </div>
  </v-list>
</template>

<script>
  import SideMenuListTile from './SideMenuListTile'

  export default {
    name: 'SideMenuList',
    components: {
      SideMenuListTile
    },
    props: {
      pages: Array,
      currentPageId: Number
    },
    data() {
      return {
        cursorIndex: 0,
        isFocusIn: false
      }
    },
    mounted() {
      this.cursorIndex = this.getCurrentIndex(this.currentPageId)
    },
    watch: {
      currentPageId(value) {
        this.cursorIndex = this.getCurrentIndex(value)
      }
    },
    computed: {
      items() {
        if (!this.pages) {
          return []
        }
        let items = []
        this.pages.forEach((page) => {
          items.push({page: page, level: 1})
          if (page.pages) {
            page.pages.forEach((child) => {
              items.push({page: child, level: 2})
            })
          }
        })
        return items
      },

    },
    methods: {
      getCurrentIndex (id) {
        let currentIndex = 0
        this.items.some((item, index) => {
          if (item.page.id === id) {
            currentIndex = index
            return true
          }
          return false;
        })        
        return currentIndex
      },     
      keyUpDown(up) {
        this.cursorIndex = up ? this.cursorIndex - 1 : this.cursorIndex + 1
        if (this.cursorIndex < 0) {
          this.cursorIndex = 0
        } else if (this.items.length <= this.cursorIndex) {
          this.cursorIndex = (this.items.length - 1)
        }
        this.$refs['item' + this.cursorIndex][0].focus()
      },
      keyEnter() {
        const item = this.items[this.cursorIndex]
        this.$router.push({name: 'pages', params: {id: item.page.id}})
      },
      clickItem(item) {
        this.$emit('click-item', item)
      },
      focusItem(event) {
        this.isFocusIn = 0 < $(this.$el).has(event.currentTarget).length
      },
      blurItem(event) {
        this.isFocusIn = 0 < $(this.$el).has(event.relatedTarget).length
      },
    }
  }
</script>

<style lang="scss" scoped>
  .searchMenu {
    /* 20px = separator width */
    width: calc(100% + 20px);
    
    .menuSubheader {
      height: 40px;
    }   
  }

</style>
