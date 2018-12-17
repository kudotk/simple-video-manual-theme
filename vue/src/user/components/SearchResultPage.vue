<template>
  <v-container
    class="ma-0 pa-0 searchPage"
    fluid
  >
    <v-layout
      row
    >
      <v-flex
        class="pa-0"
        xs10
        offset-xs1
        align-center
        v-show="searchQuery"
      >
        <article
          class="content"
        >
          <h1
            class="headline mt-4"
            v-if="0 < decoratedTextPages.length"
          >"{{ searchQuery }}" {{ $t('found below pages') }}</h1>
          <h1
            class="headline mt-4"
            v-else
          >"{{ searchQuery }}" {{ $t('is not found') }}</h1>

          <v-layout
            class="mt-4 pa-0 mr-0 ml-0 mb-4"
            column
            @keydown.up="keyUpDown(true)"
            @keydown.down="keyUpDown(false)"
            @keydown.enter.space="keyEnter"
          >
            <v-flex
              class="searchItem"
              xs12
              @click="clickItem(page)"
              v-for="(page, index) in decoratedTextPages"
              :key="page.id"
            >
              <SearchResultCard
                :page="page"
                :item-index="index"
                :cursor-index="cursorIndex"
                :ref="'page' + index"
              />
            </v-flex>
          </v-layout>
        </article>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
  import SearchResultCard from './SearchResultCard'

  export default {
    name: 'SearchResultPage',
    components: {
      SearchResultCard,
    },
    props: {
      titleLength: {
        type: Number,
        default() {
          return 20
        }
      },
      subTextLength: {
        type: Number,
        default() {
          return 100
        }
      },
      searchQuery: String,
      searchedPages: Array,
    },
    data() {
      return {
        cursorIndex: undefined
      }
    },
    watch: {
      searchedPages() {
        // reset focused index.
        this.cursorIndex = 0
      }
    },
    computed: {
      decoratedTextPages() {
        if (!this.searchQuery) {
          return []
        }
        const pages = []
        const regexSearch = new RegExp('(' + this.searchQuery + ')', 'i')
        const regexReplace = new RegExp(this.searchQuery, 'gi')

        const buildPage = (page) => {
          let title = page.title
          if (this.titleLength < title.length) {
            title = title.substr(0, this.titleLength) + '...'
          }
          title = title.replace(regexSearch, `<code class="primary--text">${this.searchQuery}</code>`)

          let text = this.$t('-')
          if (page.searchProp.joinedDescriptionText) {
            const m = page.searchProp.joinedDescriptionText.match(regexSearch)
            if (m || title) {
              let from = 0
              let length = this.subTextLength

              const range = length / 2
              let prefix = ''
              let suffix = ''
              // even if it does not match description, it will show if match title.
              let matchedIndex = !m ? 0 : m.index

              if (page.searchProp.joinedDescriptionText.length <= length) {
                length = page.searchProp.joinedDescriptionText.length
              } else {
                let left = matchedIndex - range
                let right = matchedIndex + range
                if (left < 0) {
                  right += Math.abs(left)
                  left = 0
                }
                if (page.searchProp.joinedDescriptionText.length <= right) {
                  const margin = right - page.searchProp.joinedDescriptionText.length
                  if ((left - margin) > 0) {
                    left -= margin
                  }
                }
                prefix = (left > 0) ? '...' : ''
                suffix = (right < page.searchProp.joinedDescriptionText.length) ? '...' : ''

                from = left
                length = right - left
              }
              text = page.searchProp.joinedDescriptionText.substr(from, length)
              text = text.replace(regexReplace, `<code class="primary--text">${this.searchQuery}</code>`)
              text = prefix + text + suffix
            }
          }
          return {
            id: page.id,
            title: title,
            text: text
          }
        }
        this.searchedPages.forEach((elm) => {
          pages.push(buildPage(elm))
        })

        return pages
      }
    },
    methods: {
      keyUpDown(up) {
        this.cursorIndex = up ? this.cursorIndex - 1 : this.cursorIndex + 1
        if (this.cursorIndex < 0) {
          this.cursorIndex = 0
        } else if (this.decoratedTextPages.length <= this.cursorIndex) {
          this.cursorIndex = this.decoratedTextPages.length - 1
        }
        this.$refs['page' + this.cursorIndex][0].focus()
      },
      keyEnter() {
        const page = this.decoratedTextPages[this.cursorIndex]
        this.$emit('click-page', page)
      },
      clickItem(item) {
        this.$emit('click-page', item)
      },
    }
  }
</script>

<style lang="scss" scoped>
  .searchPage {
    .content {
      width: 100%;
      max-width: 900px;
      margin: auto;
    }
  }
</style>
