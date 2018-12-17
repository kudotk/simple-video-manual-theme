<template>
  <v-list-tile
    class="menuItem"
    ripple
    :class="{ 'menuItem-focused' : isFocus }"
    @click="clickItem"
    @focus="focusItem"
    @blur="blurItem"
    ref="root"
    :aria-selected="selected"
    :tabindex="tabIndex"
  >
    <v-list-tile-content
      :class="listContentClass"
    >
      {{ item.page.title }}
    </v-list-tile-content>
  </v-list-tile>
</template>

<script>
  export default {
    name: 'SideMenuListTile',
    props: {
      item: Object,
      itemIndex: Number,
      currentPageId: Number,
      cursorIndex: Number,
      isFocusInList: Boolean
    },
    computed: {
      selected() {
        return this.item.page.id === this.currentPageId
      },
      tabIndex() {
        return (this.itemIndex === this.cursorIndex) ? 0 : -1
      },
      isFocus() {
        return this.isFocusInList && this.itemIndex === this.cursorIndex
      },
      listContentClass() {
        let name = ''
        if (this.item.level === 1) {
          name += 'body-2'
        } else {
          name += 'menuItemOutline2 body-2'
        }
        name += this.selected ? ' primary--text font-weight-bold': ''
        return name
      }
    },
    methods: {
      clickItem() {
        this.$emit('click-item', this.item)
      },
      focusItem(event) {
        this.$emit('focus-item', event)
      },
      blurItem(event) {
        this.$emit('blur-item', event)
      },
      focus() {
        $('a', this.$el).focus()
      }
    }
  }
</script>

<style lang="scss" scoped>
  .menuItem {
    white-space: nowrap;
    &-focused {
      background: rgba(0, 0, 0, 0.04);
    }
    & /deep/ a {
      outline: none;
    }
    & /deep/ .v-list__tile {
      height: 40px;
    }
  }
  .menuItemOutline2 {
    padding-left: 1rem;
  }
</style>

