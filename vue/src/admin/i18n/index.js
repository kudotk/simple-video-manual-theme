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
      'Manual': 'マニュアル',
      'Select from media': '動画を選択',
      'Select by URL': '動画をURLで指定',
      'Input description text here': '動画の説明を入力',
      'Add text': '追加',
      'Time': '時間',
      'Text': '説明',
      'Add to manual': 'マニュアル選択',
      'Search manual': '入力で絞り込み',
      'Search page': '入力で絞り込み',
      'Export Manual Page Files': 'マニュアル出力',
      'Preparing': '準備しています',
      'Building assets': 'ファイルを作成しています',
      'Compress files': 'ファイルを圧縮しています',
      'Done. Wait for Downloading.': '完了しました。ダウンロードを始めます。',
      'If not start to download, Click Download.': 'ダウンロードが始まらない場合は、ダウンロードボタンをクリックしてください。',
      'Unable to export files. Retry.': "出力に失敗しました。もう一度お試しください。",
      'Download': 'ダウンロード',
      'Time value is invalid': '無効な時間です',
      'URL is invalid': 'URLが無効です',
      'Input description text': '動画の説明を入力してください',
      'Not select': '選択なし',
      'Delete description text. OK?': 'この動画の説明を削除します。よろしいですか？',
      'Preview': 'プレビュー'
    }
  }
}

export default new VueI18n(options)
