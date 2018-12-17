import Vue from 'vue'
import VueI18n from 'vue-i18n'

Vue.use(VueI18n)

// @see https://github.com/kazupon/vue-i18n
const options = {
  locale: 'en',
  dateTimeFormats: {
    en: {
      short: {
        year: 'numeric', month: 'short', day: 'numeric'
      },
      long: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        weekday: 'short',
        hour: 'numeric',
        minute: 'numeric'
      }
    },
    ja: {
      short: {
        year: 'numeric', month: 'short', day: 'numeric'
      },
      long: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        weekday: 'short',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
      }
    }
  },
  messages: {
    ja: {
      'Page Not Found': 'このURLのページはありません',
      'Unregistered Page': '未登録',
      'Not belongs in Manual book': 'このページはマニュアルに登録されていません',
      'Attach to Manual book': '設定してください。',
      'found below pages': 'が見つかったページ',
      'is not found': 'は見つかりませんでした。',
      'input search word': '文字入力で検索',
      'Last update': '更新日',
      'No Data': '登録データなし',
      'Table of contents' : '目次',
      'Description list' : 'リスト',
      'Search': '検索'
    }
  }
}

export default new VueI18n(options)
