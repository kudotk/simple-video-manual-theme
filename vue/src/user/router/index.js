import Vue from 'vue'
import VueRouter from 'vue-router'
import TheManualContentsPage from '../components/TheManualContentsPage'
import TheTopPage from '../components/TheTopPage'
import TheError404Page from '../components/TheError404Page'
import TheErrorNotAttachedManualPage from '../components/TheErrorNotAttachedManualPage'

Vue.use(VueRouter)

const options = {
  routes: [
    {
      path: '/pages/:id*',
      name: 'pages',
      component: TheManualContentsPage
    },
    {
      path: '/',
      name: 'top',
      component: TheTopPage
    },
    {
      path: '/error-not-attach',
      name: 'errorNotAttach',
      component: TheErrorNotAttachedManualPage
    },
    {
      path: '*',
      name: 'error404',
      component: TheError404Page
    }
  ]
}

export default new VueRouter(options)
