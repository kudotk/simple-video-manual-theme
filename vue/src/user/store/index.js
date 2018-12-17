import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const options = {
  state: {
    /** @type {FrontPage} */
    frontPagePost: {},
    /** @type {Page[]} */
    allPages: [],
    /** @type {Page[]} */
    parentPages: [],
    /** @type {Map<number,number>} */   
    pageIdIndexMap: undefined,
    /** @type {Page[]} */   
    searchResultPages: [],
    
    pageTitle: '',
    currentPageId: 0
  },
  getters: {
    siteTitle(state, getters) {
      let title = ''
      if (getters.manualTitle && getters.pageTitle) {
        title = getters.pageTitle + ' | ' + getters.manualTitle
      } else if (getters.pageTitle) {
        title = getters.pageTitle
      } else if (getters.manualTitle) {
        title = getters.manualTitle
      }
      return title
    },
    manualTitle(state) {
      return state.frontPagePost.title || ''
    },
    frontPageContent(state) {
      return state.frontPagePost.textContent || ''
    },
    pageTitle(state) {
      return state.pageTitle
    },
    searchedPages(state) {
      return state.searchResultPages
    },
    pages(state) {
      return state.parentPages
    },
    page: (state) => (id) => {
      return state.allPages[state.pageIdIndexMap[id]]
    },
    currentPageId(state) {
      return state.currentPageId
    },
    hasCurrentPageId(state) {
      return state.currentPageId && (state.currentPageId === 0)
    },
    
    // static values
    baseTransitionDuration() {
      return 200;
    }
  },
  mutations: {
    setFrontPagePost(state, post) {
      state.frontPagePost = post
    },
    setPageTitle(state, title) {
      state.pageTitle = title
    },
    setSearchResultPages(state, pages) {
      state.searchResultPages = pages
    },
    setPages(state, pages) {
      state.parentPages = pages
      
      let index = 0
      let pageIdIndexMap = {}
      let allPages = []
      const pushPage = (page) => {
        allPages.push(page)
        pageIdIndexMap[page.id] = index
        index++
        if (page.pages) {
          page.pages.forEach((page) => {
            pushPage(page)
          })
        }
      }
      pages.forEach((page) => {
        pushPage(page)
      })
      
      state.allPages = allPages
      state.pageIdIndexMap = pageIdIndexMap
    },
    setCurrentPageId(state, id) {
      state.currentPageId = parseInt(id)
    }
  },
  actions: {
    /**
     * Search pages.
     * @param {string} SearchWord
     * @returns {Array} Result pages
     */
    async searchPages({commit, state}, searchWord) {
      const re = new RegExp(searchWord, 'i')
      const test = !searchWord
        ? () => {
          return true
        }
        : (page) => {
          return (re.test(page.title) || re.test(page.searchProp.joinedDescriptionText))
        }
      let pages = []
      state.allPages.forEach((page) => {
        if (test(page)) {
          pages.push(page)
        }       
      })
      commit('setSearchResultPages', pages)
    }
  }
}

export default new Vuex.Store(options)
