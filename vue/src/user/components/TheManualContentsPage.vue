<template>
  <v-container
    class="pa-0 ma-0 contentPage"
    fluid
  >
    <header class="header">
      <v-text-field
        class="pa-0 body-1 searchInput"
        :label="$t('input search word')"
        prepend-inner-icon="search"
        clearable
        v-model="pageSearchQuery"
        solo
        flat
        full-width
        hide-details
        height="50"
      />
    </header>
    <div class="content">
      <div class="navi">
        <nav class="naviList">
          <SideMenuList
            :pages="pages"
            :current-page-id="currentPageId"
            @click-item="clickSideMenuItem"
          />
        </nav>
        <div
          class="separatorLeft"
          @mouseenter="mouseEnterSeparator"
          @mouseleave="mouseLeaveSeparator"
        />
      </div>
      <div
        class="separatorRight"
        @mouseenter="mouseEnterSeparator"
        @mouseleave="mouseLeaveSeparator"
      />
      <div class="scrollWrapper">
        <div class="pageContent">
          <transition
            name="page-fade"
            @after-leave="pageFadeTransitionAfterLeaveHandler"
          >
            <SearchResultPage
              class="searchContent"
              :key="1"
              v-show="showSearchPage"
              :searched-pages="searchedPages"
              :search-query="pageSearchQuery"
              @click-page="clickSearchPage"
            />
          </transition>
          <transition
            name="page-fade"
            @after-leave="pageFadeTransitionAfterLeaveHandler"
          >
            <TheManualPage
              class="manualContent"
              :key="2"
              v-show="showManualPage"
              :page="currentManualPage"
              @page-loaded="manualPageLoadedHandler"
            />
          </transition>
          <transition
            name="page-fade"
            @after-leave="pageFadeTransitionAfterLeaveHandler"
          >
            <v-layout
              class="ma-0 pt-5 pb-5 titleContent"
              align-center
              justify-center
              column
              :key="3"
              v-show="showTitlePage">
              <h1 class="display-1 primary--text mb-4">
                {{ manualTitle }}
              </h1>
              <div
                class="body-1 mb-4"
                v-html="this.$store.getters.frontPageContent"
              />
            </v-layout>
          </transition>
        </div>
      </div>
    </div>
  </v-container>
</template>

<script>
  import SearchResultPage from './SearchResultPage'
  import TheManualPage from './TheManualPage'
  import titleMixin from '../mixin/TitleMixin'
  import i18nMixin from '../mixin/I18nMixin'
  import vuexStoreMixin from '../mixin/VuexStoreMixin'
  import config from '../app/config'
  import Page from '../app/Page'
  import FrontPage from '../app/FrontPage'
  import SideMenuList from './SideMenuList'

  export default {
    mixins: [titleMixin, vuexStoreMixin, i18nMixin],
    components: {
      TheManualPage,
      SideMenuList,
      SearchResultPage
    },
    data() {
      return {
        pageSearchQuery: '',
        separatorMousePosX: 0,
        isSeparatorMoving: false,
        startWidth: 0,
        currentManualPage: undefined,
        loadingPageId: undefined,
        pageTransitionFrom: '',
        activePage: 'title'
      }
    },
    computed: {
      manualTitle() {
        return this.$store.getters.manualTitle
      },
      searchedPages() {
        return this.$store.getters.searchedPages
      },
      pages() {
        return this.$store.getters.pages
      },
      currentPageId() {
        return this.$store.getters.currentPageId
      },
      showSearchPage() {
        return this.activePage === 'search'
      },
      showTitlePage() {
        return this.activePage === 'title'
      },
      showManualPage() {
        return this.activePage === 'manual'
      },
    },
    watch: {
      '$route'(to) {
        if (to) {
          if (to.params && to.params.id) {
            this.$store.commit('setCurrentPageId', to.params.id)
          } else {
            this.$store.commit('setCurrentPageId', '')
          }
        }
      },
      pageSearchQuery(val) {
        if (val) {
          this.activePage = 'search'
          this.$_TitleMixin_setTitle(this.$t('Search') + ' | ' + this.$store.getters.manualTitle)
        } else {
          this.activePage = 'manual'
          this.$_TitleMixin_setTitle(this.$store.getters.siteTitle)
        }
        this.$store.dispatch('searchPages', val)
      }
    },
    mounted() {
      // get manual-book post-data.
      this.get_manual_data()
        .then(response => {
          if (!response) {
            return
          }
          this.$store.commit('setFrontPagePost', new FrontPage(response[0]))
        })
      // get manual-book-pages post-data.
      this.get_manual_pages_data()
        .then(response => {
          if (!response) {
            return
          }
          const pages = []
          response.forEach(elm => {
            pages.push(new Page(elm))
          })
          this.$store.commit('setPages', pages)

          const id = this.$route.params.id
          if (id) {
            this.$store.commit('setCurrentPageId', id)
          }
        })
    },
    methods: {
      async get_manual_data() {
        if (process.env.VUE_APP_CONFIG_ENV === 'user-serve') {
          const url = config.svm_args.wp_rest_endpoint_base + '/manuals/{{ID}}.json'
          return $.ajax({
            url: url.replace('{{ID}}', encodeURIComponent(config.svm_args.svm_book_post_id)),
            method: 'GET'
          })
        } else {
          return config.svm_args.manual_post_data
        }
      },
      async get_manual_pages_data() {
        if (process.env.VUE_APP_CONFIG_ENV === 'user-serve') {
          const url = config.svm_args.wp_rest_endpoint_base + '/manuals/{{ID}}/pages.json'
          return $.ajax({
            url: url.replace('{{ID}}', encodeURIComponent(config.svm_args.svm_book_post_id)),
            method: 'GET'
          });
        } else {
          return config.svm_args.manual_pages_post_data
        }
      },
      clickSideMenuItem(item) {
        setTimeout(() => {
          if (item.page.id === this.currentPageId) {
            this.activePage = 'manual'
          } else {
            this.$store.commit('setCurrentPageId', item.page.id)
          }
        }, this.$store.getters.baseTransitionDuration)        
      },
      clickSearchPage(page) {
        setTimeout(() => {
          if (page.id === this.currentPageId) {
            this.activePage = 'manual'
          } else {
            this.pageTransitionFrom = 'search'
            this.activePage = ''
            this.loadingPageId = page.id
          }
        }, this.$store.getters.baseTransitionDuration)
      },
      mouseMoveSeparator(e) {
        // prevent text selection.
        e.preventDefault()

        if (this.separatorMousePosX === e.pageX) {
          return
        }

        const move = e.pageX - this.separatorMousePosX
        const width = this.startWidth + move
        const css = {
          'width': width + 'px'
        }
        $('.navi').css(css)
      },
      mouseDownSeparator(e) {
        this.separatorMousePosX = e.pageX
        this.startWidth = $('.navi').outerWidth()
        $(document).on('mouseup', this.mouseUpSeparator)
          .on('mousemove', this.mouseMoveSeparator)
        this.isSeparatorMoving = true
      },
      mouseUpSeparator() {
        $(document).off('mousemove', this.mouseMoveSeparator)
        this.isSeparatorMoving = false
      },
      mouseEnterSeparator(e) {
        $(e.target).on('mousedown', this.mouseDownSeparator)
      },
      mouseLeaveSeparator(e) {
        $(e.target).off('mousedown', this.mouseDownSeparator)
      },
      async startToLoadPage(id) {
        this.loadingPageId = id
        if (this.currentManualPage) {
          if (this.activePage === 'manual') {
            this.pageTransitionFrom = 'manual'
            this.activePage = ''
          } else {
            await this.loadPage()
          }
        } else {
          await this.loadPage()
        }
      },
      async loadPage() {
        // get page post-data.
        try {
          const postData = this.$store.getters.page(this.loadingPageId)
          this.currentManualPage = postData
        } catch (e) {
          this.currentManualPage = undefined
        }
      },
      async pageFadeTransitionAfterLeaveHandler() {
        if (this.pageTransitionFrom === 'search') {
          if (!this.currentManualPage || (this.loadingPageId !== this.currentManualPage.id)) {
            this.$router.push({name: 'pages', params: {id: this.loadingPageId}})
          }
        } else {
          if (this.currentManualPage) {
            if (this.currentManualPage.id !== this.loadingPageId) {
              await this.loadPage()
            }
          }
        }
      },
      manualPageLoadedHandler() {
        this.activePage = 'manual'
        if (this.currentManualPage) {
          this.$store.commit('setPageTitle', this.currentManualPage.title)
        }
      },
      $_VuexStoreMixin_subscribe(mutation, state) {
        if ((mutation.type === 'setFrontPagePost') || (mutation.type === 'setPageTitle')) {
          this.$_TitleMixin_setTitle(this.$store.getters.siteTitle)
        } else if (mutation.type === 'setCurrentPageId') {
          if (state.currentPageId) {
            this.startToLoadPage(state.currentPageId)
          }
        }
      }
    },
  }
</script>

<style lang="scss" scoped>
  @import '../assets/css/_sass-loader-import.scss';

  .contentPage {
    .header {
      border-bottom: solid 1px $color-primary;
      z-index: 100;
      background: #fff;
    }

    .searchInput {
      outline: none;
    }

    .content {
      display: flex;
    }

    .navi {
      display: flex;
      flex-direction: row;
      /* 50px = header height */
      height: calc(100vh - 50px);
      min-width: 220px;
      width: 220px;
      overflow-x: hidden;
      overflow-y: scroll;
      border-right: solid 1px $color-primary;
      background: #fff;
    }

    .naviList {
      width: calc(100% - 20px);
    }

    .separatorLeft {
      min-width: 20px;
      height: 100%;
      z-index: 500;
      cursor: col-resize;
    }

    .separatorRight {
      height: calc(100vh - 50px);
      z-index: 500;
      cursor: col-resize;
      &:before {
        content: '';
        display: inline-block;
        width: 20px;
      }
    }

    .scrollWrapper {
      /* 50px = header height */
      height: calc(100vh - 50px);
      min-width: 600px;
      overflow-x: hidden;
      overflow-y: scroll;
      flex: 1;
      position: relative;
    }

    .pageContent {
      width: 100%;
      min-height: calc(100vh - 50px);
      padding-right: 20px !important;
    }

    .searchContent {
      position: absolute;
      min-height: calc(100vh - 50px);
      padding-right: 20px !important;
    }

    .manualContent {
      position: absolute;
      min-height: calc(100vh - 50px);
      padding-right: 20px !important;
    }
    
    .titleContent {
      width: 100%;
      position: absolute;
      min-height: calc(100vh - 50px);
      padding-right: 20px !important;
    }
  }
</style>

<style lang="scss">
  @import '../assets/css/_sass-loader-import.scss';

  @include transitionFade(page-fade, $base-animation-duration);
</style>
