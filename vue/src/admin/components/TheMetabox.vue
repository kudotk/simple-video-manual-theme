<template>
  <div class="mbox">
    <div
      class="videoInput"
      v-show="!videoSrc"
    >
      <button
        type="button"
        class="button videoInput_FileButton"
        @click.prevent="clickUploadVideo"
      >
        {{ $t('Select from media') }}
      </button>
      <div class="videoInput_UrlContent">
        <button
          type="button"
          class="button videoInput_UrlButton"
          @click.prevent="clickLoadVideo"
          :disabled="disabledSelectVideoUrl"
        >
          {{ $t('Select by URL') }}
        </button>
        <input
          type="url"
          name="svm_input_url"
          class="videoInput_Url"
          v-validate="{ required: true, url: true }"
          v-model="videoUrl"
          :class="inputUrlError ? 'sw-Flg_Error' : ''"
        >
      </div>
      <div
        class="videoInput_Error"
        v-if="inputUrlError"
      >
        <span v-text="inputUrlErrorMessage"/>
      </div>
    </div>
    <div
      class="videoContent"
      v-show="videoSrc"
      ref="video"
    >
      <div class="videoClose">
        <button
          type="button"
          class="media-modal-close videoCloseButton"
          @click="clickCloseVideo"
        >
          <span class="media-modal-icon">
            <span class="screen-reader-text">動画の選択を解除</span>
          </span>
        </button>
      </div>
    </div>
    <div class="description">
      <div class="descriptionTextInput">
        <div class="descriptionTextInputTime">
          <input
            type="text"
            name="svm_input_hour"
            class="descriptionTextInputHh"
            placeholder="0"
            @blur="blurInputTime"
            v-model="inputDescription.hour"
            :class="inputTimeError ? 'sw-Flg_Error' : ''"
            v-validate="{ required: true, min_value: 0, max_value: 23 }"
            data-vv-as="description Hour"
          ><span>:</span><input
            type="text"
            name="svm_input_minute"
            class="descriptionTextInputMm"
            placeholder="0"
            @blur="blurInputTime"
            v-model="inputDescription.minute"
            :class="inputTimeError ? 'sw-Flg_Error' : ''"
            v-validate="{ required: true, min_value: 0, max_value: 59 }"
            data-vv-as="description Minute"
          ><span>:</span><input
            type="text"
            name="svm_input_second"
            class="descriptionTextInputSs"
            placeholder="0"
            @blur="blurInputTime"
            v-model="inputDescription.second"
            :class="inputTimeError ? 'sw-Flg_Error' : ''"
            v-validate="{ required: true, min_value: 0, max_value: 59 }"
            data-vv-as="description Second"
          >
        </div>
        <textarea
          name="svm_input_text"
          rows="3"
          v-model="inputDescription.text"
          :class="errors.has('svm_input_text') ? 'sw-Flg_Error' : ''"
          v-validate="{ required: true }"
          :placeholder="$t('Input description text here')"
          data-vv-as="description Text"
        />
      </div>
      <div class="descriptionTextInputError">
        <span
          v-for="(message, index) in inputDescriptionErrorMessages"
          :key="index"
          v-show="inputDescriptionErrorMessages"
          v-text="message"
        />
      </div>
      <div
        class="descriptionTextInputControl"
        :class="inputDescriptionErrorMessages.length ? 'descriptionTextInputControl-error' : false"
      >
        <button
          class="button descriptionTextInputAddButton"
          type="button"
          @click="addDescription"
          :disabled="disabledAddText"
        >
          {{ $t('Add text') }}
        </button>
      </div>
      <table
        class="descriptionTextTable wp-list-table widefat fixed striped media"
        v-show="descriptions.length"
      >
        <thead class="descriptionTextTableHead">
          <tr class="descriptionTextTableRow">
            <th
              class="manage-column column-time column-primary sortable descriptionTextTable_Time"
              :class="{ asc: isTimeColumnAsc, desc: !isTimeColumnAsc }"
              scope="col"
            >
              <a
                tabindex="0"
                @click.prevent="clickColumn('time')"
              >
                <span>{{ $t('Time') }}</span>
                <span class="sorting-indicator"/>
              </a>
            </th>
            <th
              class="manage-column column-title sortable descriptionTextTable_Text"
              :class="{ asc: isTextColumnAsc, desc: !isTextColumnAsc }"
              scope="col"
            >
              <a
                tabindex="0"
                @click.prevent="clickColumn('text')"
              >
                <span>{{ $t('Text') }}</span>
                <span class="sorting-indicator"/>
              </a>
            </th>
          </tr>
        </thead>
        <tbody
          id="the-list"
          class="descriptionTextTableBody"
        >
          <tr
            is="DescriptionRow"
            v-for="(description, index) in descriptions"
            :key="index"
            :no="index"
            :description="description"
            @delete-text="deleteText"
          />
        </tbody>
      </table>
      <!-- Manual selection -->
      <div class="descriptionRelation">
        <div class="descriptionRelationWrapper">
          <p class="descriptionRelationTitle">
            <strong>{{ $t('Add to manual') }}</strong>
          </p>
          <div class="descriptionRelationBookPage">
            <div class="descriptionRelationBook">
              <div class="descriptionRelationBookInput">
                <input
                  type="text"
                  name="svm_book_search"
                  v-model="bookSearch"
                  :placeholder="$t('Search manual')"
                >
              </div>
              <select
                name="svm_book_post_id"
                type="number"
                v-model="bookPostId"
                size="10"
              >
                <option
                  v-for="(title) in searchedBooks"
                  :key="title.id"
                  :value="title.id"
                  v-text="title.title"
                />
              </select>
            </div>
            <div class="descriptionRelationSpace">
              <i class="dashicons dashicons-arrow-right-alt2"/>
            </div>
            <div class="descriptionRelationPage">
              <div class="descriptionRelationPageInput">
                <input
                  name="svm_parent_page_search"
                  type="text"
                  v-model="parentPageSearch"
                  :placeholder="$t('Search page')"
                >
              </div>
              <select
                name="svm_parent_page_post_id"
                type="number"
                size="10"
                v-model="parentPagePostId"
              >
                <option
                  v-for="(page) in searchedPages"
                  :key="page.id"
                  :value="page.id"
                  v-text="page.title"
                />
              </select>
            </div>
          </div>
        </div>
        <div class="descriptionRelationPagePreviewWrapper">
          <p class="descriptionRelationPagePreviewTitle">
            <strong>
              {{ $t('Preview') }}
            </strong>
          </p>
          <div class="descriptionRelationPagePreview">
            <div class="descriptionRelationPagePreviewWrapper1">
              <dl
                v-for="(parentPageDescription, index) in parentPageDescriptions"
                :key="parentPageDescription.id + index.toString()"
              >
                <dt
                  v-show="parentPageDescriptionTimeText(parentPageDescription)"
                  v-text="parentPageDescriptionTimeText(parentPageDescription)"
                />
                <dd
                  v-show="parentPageDescription.text"
                  v-text="parentPageDescription.text"
                />
              </dl>
            </div>
          </div>
        </div>
      </div>
      <input
        type="hidden"
        name="svm_description_json"
        v-model="description_json"
      >
    </div>
  </div>
</template>

<script>
  import DescriptionRow from './DescriptionRow'
  import config from '../app/config'
  import utils from '../app/utils'
  import throttle from 'lodash-es/throttle'
  import i18nMixin from '../mixin/I18nMixin'

  export default {
    name: 'TheMetabox',
    mixins: [i18nMixin],
    components: {
      DescriptionRow
    },
    data() {
      return {
        postId: undefined,
        player: undefined,
        videoUrl: undefined,
        inputDescription: {hour: '', minute: '', second: '', text: ''},
        descriptions: [],
        bookSearch: undefined,
        parentPageSearch: undefined,
        bookPostId: 0,
        books: [],
        searchedBooks: [],
        parentPagePostId: 0,
        pages: [],
        searchedPages: [],
        videoId: 0,
        videoSrc: undefined,
        videoTimer: undefined,
        videoEl: undefined,
        parentPageDescriptions: [],
        isTimeColumnAsc: false,
        isTextColumnAsc: false,

        // for control WPMediauploader.
        orgSendAttachment: false,
        mediaUseByPlugin: false
      }
    },
    created() {
      this.postId = parseInt(config.page_post_id, 10)
    },
    mounted() {
      // store send_to_editor function which an other plugins set.
      // @see: wordpress media-editor.js line.737
      if (!window.send_to_editor) {
        this.orgSendToEditor = window.send_to_editor
      }

      // get saved values.
      const self = this
      jQuery.ajax({
        url: config.rest_endpoint + '/pages/' + encodeURIComponent(this.postId) + '.json',
        method: 'GET'
      }).then((response) => {
          if (!response || !response.post_meta) {
            return
          }
          self.bookSearch = ''
          self.parentPageSearch = ''
          const bookPostId = response.post_meta[config.meta_key.book_post_id] || 0
          const p1 = self.refreshManualBooks()
          const p2 = self.refreshManualPages(bookPostId)
          Promise.all([p1, p2]).then(() => {
              let obj = {}
              if (response.post_meta[config.meta_key.description_json]) {
                try {
                  obj = JSON.parse(response.post_meta[config.meta_key.description_json])
                } catch (e) {
                  utils.error('unable to get data.', 're input fields.')
                  return
                }
                if (obj['input_description']) {
                  self.inputDescription = obj['input_description']
                }
                if (obj['descriptions']) {
                  self.descriptions = obj['descriptions']
                }
                if (self.books) {
                  self.books.some((book) => {
                    if (book.id === obj['book_post_id']) {
                      self.bookPostId = obj['book_post_id']
                      return true
                    }
                    return false
                  })
                }
                if (self.pages) {
                  self.pages.some((page) => {
                    if (page.id === obj['parent_page_post_id']) {
                      self.parentPagePostId = obj['parent_page_post_id']
                      return true
                    }
                    return false
                  })
                }

                // init video field.
                if (response.post_meta[config.meta_key.video_meta]) {
                  const videoMeta = response.post_meta[config.meta_key.video_meta]
                  if (videoMeta.url && videoMeta.type) {
                    self.setVideoSrc(videoMeta.url, obj['video_id'])
                  }
                }
              }
            }
          ).catch((reason) => {
            utils.error(reason, 're input fields.')
          })
        },
        (error) => {
          utils.error(error)
        }
      )
    },
    computed: {
      description_json: {
        get() {
          const setInt = (str, key, obj) => {
            if (str) {
              const v = parseInt(str)
              if (!isNaN(v)) {
                obj[key] = v
              }
            }
            return obj
          }
          const buildJson = (inst) => {
            const json = {}
            if (inst.hour && (typeof inst.hour === 'string')) {
              json.hour = inst.hour.replace(/[^0-9]/, '')
            }
            if (inst.minute && (typeof inst.minute === 'string')) {
              json.minute = inst.minute.replace(/[^0-9]/, '')
            }
            if (inst.second && (typeof inst.second === 'string')) {
              json.second = inst.second.replace(/[^0-9]/, '')
            }
            if (inst.text) {
              json.text = inst.text
            }
            return json
          }

          let jsonObj = {}
          let obj = buildJson(this.inputDescription)
          if (obj && (Object.keys(obj).length > 0)) {
            jsonObj['inputDescription'] = obj
          }
          if (this.descriptions && (this.descriptions.length > 0)) {
            const ary = []
            this.descriptions.forEach(
              (inst) => {
                obj = buildJson(inst)
                if (obj && (Object.keys(obj).length > 0)) {
                  ary.push(obj)
                }
              }
            )
            if (ary.length > 0) {
              jsonObj['descriptions'] = ary
            }
          }
          jsonObj = setInt(this.bookPostId, 'book_post_id', jsonObj)
          jsonObj = setInt(this.parentPagePostId, 'parent_page_post_id', jsonObj)
          jsonObj = setInt(this.videoId, 'video_id', jsonObj)
          jsonObj.video_src = this.videoSrc ? this.videoSrc : '';
          return JSON.stringify(jsonObj)
        }
      },
      inputUrlError() {
        return this.inputUrlErrorMessage
      },
      inputUrlErrorMessage() {
        if (!this.videoUrl) {
          return ''
        }
        const key = 'svm_input_url'
        if (this.fields[key].dirty) {
          if (this.fields[key].dirty && this.$validator.errors.has(key)) {
            return this.$t('URL is invalid')
          }
        }
      },

      inputTimeErrorMessage() {
        if (!(this.fields['svm_input_hour'] && this.fields['svm_input_minute'] && this.fields['svm_input_second'])) {
          return ''
        }
        // show message after edit hour, min, sec.
        const keys = ['svm_input_hour', 'svm_input_minute', 'svm_input_second']
        const notEdit = keys.some((key) => {
          return !(this.fields[key].dirty)
        })
        if (notEdit) {
          return ''
        }
        const exist = keys.some((key) => {
          return this.fields[key].dirty && this.$validator.errors.has(key)
        })
        const msg = exist ? this.$t('Time value is invalid') : ''
        return msg
      },
      inputTimeError() {
        return this.inputTimeErrorMessage
      },
      inputDescriptionErrorMessages() {
        const messages = []
        if (this.inputTimeErrorMessage) {
          messages.push(this.inputTimeErrorMessage)
        }
        if (this.$validator.errors.has('svm_input_text')) {
          messages.push(this.$t('Input description text'))
        }
        return messages
      },
      disabledSelectVideoUrl() {
        const edit = ('svm_input_url' in this.fields) && this.fields['svm_input_url'].dirty
        const error = edit && this.$validator.errors.has('svm_input_url')
        return !edit || error
      },
      disabledAddText() {
        // check enable after input values.
        const keys = ['svm_input_hour', 'svm_input_minute', 'svm_input_second', 'svm_input_text']
        const disabled = keys.some((key) => {
          // check value after edit.
          const edit = (key in this.fields) && this.fields[key].dirty
          const error = edit && this.$validator.errors.has(key)
          return !edit || error
        })
        return disabled
      },
      descriptionsLength() {
        return this.descriptions ? this.descriptions.length : 0
      }
    },
    watch: {
      bookPostId(val) {
        this.refreshManualPages(val)
          .then(() => {
            this.parentPageSearch = ''
          })
      },
      parentPagePostId(val) {
        this.refreshParentPagePreview(val)
      },
      bookSearch: throttle(
        function (val) {
          const filtered = []
          if (!val) {
            this.searchedBooks = this.books
            return val
          }
          const re = new RegExp(val, 'i')
          for (let i = 0, len = this.books.length; i < len; i++) {
            const elm = this.books[i]
            if (re.test(elm.title)) {
              filtered.push(elm)
            }
          }
          this.searchedBooks = filtered
          return val
        }, 500
      ),
      parentPageSearch: throttle(
        function (val) {
          const filtered = []
          if (!val) {
            this.searchedPages = this.pages
            return val
          }
          const re = new RegExp(val, 'i')
          for (let i = 1, len = this.pages.length; i < len; i++) {
            const elm = this.pages[i]
            if (re.test(elm.title)) {
              filtered.push(elm)
            }
          }
          this.searchedPages = filtered
          return val
        }, 500
      )
    },
    methods: {
      playVideo() {
        if (this.videoTimer) {
          clearInterval(this.videoTimer)
        }
        this.videoTimer = setInterval(() => {
          const sec = (Math.floor(this.videoEl.currentTime % 60)).toString()
          const min = (Math.floor((this.videoEl.currentTime / 60) % 60)).toString()
          const hour = (Math.floor(this.videoEl.currentTime / 60 / 60)).toString()
          this.inputDescription = Object.assign(
            {},
            this.inputDescription,
            {hour: hour, minute: min, second: sec}
          )
        }, 200)
      },
      pauseVideo() {
        if (this.videoTimer) {
          clearInterval(this.videoTimer)
        }
      },
      clickUploadVideo() {
        // handling wordpress media upload browser.
        // need default .add_media button control, so this button does not contain vue area.

        // check whether media upload is loaded.
        if (!(wp && wp.media && wp.media.editor)) {
          utils.error('Not loaded wordpress Media Uploader', '')
          return
        }

        // store default functions.
        if (!this.orgSendAttachment) {
          this.orgSendAttachment = wp.media.editor.send.attachment
        }

        // set flg off when window is opened by default media button.
        jQuery('.add_media').off('click.manual').on('click.manual', () => {
          this.mediaUseByPlugin = false
        })

        this.mediaUseByPlugin = true

        const self = this
        wp.media.editor.send.attachment = function (props, attachment) {
          if (!self.mediaUseByPlugin) {
            // call original function.
            return self.orgSendAttachment.apply(this, [props, attachment])
          } else {
            // call injected function.
            self.setVideoSrc(attachment.url, attachment.id)
          }
        }

        // open window.
        // @see: wordpress wp.media.editor.init attaches event to dom, which has 'data-editor'.
        wp.media.editor.open(undefined, {
          multiple: false
        })
      },
      clickLoadVideo() {
        if (this.videoUrl) {
          this.setVideoSrc(this.videoUrl)
        }
      },
      clickCloseVideo() {
        this.clearVideoSrc()
      },
      blurInputTime(e) {
        const name = e.currentTarget.getAttribute('name')
        if (name) {
          this.fields[name].dirty = true
        }
      },
      addDescription() {
        const data = JSON.parse(JSON.stringify(this.inputDescription))
        this.descriptions.unshift(data)
      },
      clearVideoSrc() {
        jQuery('video', this.$refs.video).remove()
        this.videoEl = undefined
        this.videoId = undefined
        this.videoSrc = undefined
      },
      setVideoSrc(src, videoId) {
        this.clearVideoSrc()

        const $video = jQuery('<video style="width:100%" controls>')
          .attr({
            src: src,
            style: 'width: 100%'
          })
          .on('play', this.playVideo)
          .on('pause', this.pauseVideo)
        jQuery(this.$refs.video).append($video)
        this.videoEl = $video.get(0)
        this.videoId = videoId || ''
        this.videoSrc = src

        this.inputDescription.hour = '0'
        this.inputDescription.minute = '0'
        this.inputDescription.second = '0'
        this.$validator.flag(
          'svm_input_hour', {
            dirty: true
          }
        )
        this.$validator.flag(
          'svm_input_minute', {
            dirty: true
          }
        )
        this.$validator.flag(
          'svm_input_second', {
            dirty: true
          }
        )
      },
      deleteText(payload) {
        if (!utils.confirm(this.$t('Delete description text. OK?'))) {
          return
        }
        // Array.slice not effect.
        this.$delete(this.descriptions, payload.index)
      },
      clickColumn(name) {
        if (name === 'time') {
          this.isTimeColumnAsc = !this.isTimeColumnAsc
          if (this.descriptions.length === 0) {
            return
          }
          let orderR = this.isTimeColumnAsc ? 1 : -1
          let orderL = this.isTimeColumnAsc ? -1 : 1
          const keys = ['hour', 'minute', 'second']
          this.descriptions.sort((lhs, rhs) => {
            let dir = 0
            keys.some((key) => {
              const lv = ('00' + lhs[key]).slice(-2)
              const rv = ('00' + rhs[key]).slice(-2)
              if (lv < rv) {
                dir = orderL
                return true
              } else if (lv > rv) {
                dir = orderR
                return true
              }
            })
            return dir
          })
        } else if (name === 'text') {
          this.isTextColumnAsc = !this.isTextColumnAsc
          let orderR = this.isTextColumnAsc ? 1 : -1
          let orderL = this.isTextColumnAsc ? -1 : 1
          this.descriptions.sort(
            (lhs, rhs) => {
              let dir = 0
              if (lhs.text < rhs.text) {
                dir = orderL
              } else if (lhs.text > rhs.text) {
                dir = orderR
              }
              return dir
            }
          )
        }
      },
      refreshManualBooks() {
        return new Promise(
          (resolve) => {
            const ajaxData = config.ajax_call_data
            ajaxData['status'] = 'any'
            jQuery.ajax({
              url: config.rest_endpoint + '/manuals.json',
              method: 'POST',
              data: ajaxData
            }).then(
              response => {
                if (response) {
                  const books = []
                  response.forEach((elm) => {
                    books.push({
                      id: elm.ID,
                      title: elm.post_title
                    })
                  })
                  this.searchedBooks = books
                  this.books = books
                }
                resolve(response)
              },
              error => {
                resolve({reason: error})
              }
            )
          }
        )
      },
      refreshManualPages(bookPostId) {
        return new Promise(
          (resolve) => {
            if (!bookPostId || bookPostId === 0) {
              const pages = [{id: 0, title: this.$t('Not select')}]
              this.searchedPages = pages
              this.pages = pages
              resolve({})
              return
            }

            const ajaxData = config.ajax_call_data
            ajaxData['status'] = 'any'
            jQuery.ajax({
              url: config.rest_endpoint + '/manuals/' + encodeURIComponent(bookPostId) + '/pages.json',
              method: 'POST',
              data: ajaxData
            }).then(
              (response) => {
                if (response) {
                  const pages = []
                  response.forEach((elm) => {
                    if (this.postId !== elm.ID) {
                      pages.push({
                        id: elm.ID,
                        title: elm.post_title
                      })
                    }
                  })
                  pages.unshift({id: 0, title: this.$t('Not select')})
                  this.searchedPages = pages
                  this.pages = pages
                }
                resolve(response)
              },
              (error) => {
                resolve({reason: error})
              }
            )
          }
        )
      },
      refreshParentPagePreview(pageId) {
        return new Promise(
          (resolve) => {
            if (!pageId || pageId === 0) {
              this.parentPageDescriptions = []
              resolve({})
              return
            }
            jQuery.ajax({
              url: config.rest_endpoint + '/pages/' + encodeURIComponent(pageId) + '.json',
              method: 'GET'
            }).then(
              response => {
                if (!response || !response.post_meta || !response.post_meta[config.meta_key.description_json]) {
                  this.parentPageDescriptions = []
                  return
                }

                const json = response.post_meta[config.meta_key.description_json]
                try {
                  let jsonObj = JSON.parse(json)
                  if (jsonObj && jsonObj.descriptions) {
                    this.parentPageDescriptions = jsonObj.descriptions
                  } else {
                    this.parentPageDescriptions = []
                  }
                } catch (e) {
                  utils.error('unable to get page preiew.', 'click page name again')
                  this.parentPageDescriptions = []
                }
              },
              error => {
                utils.error(error)
              }
            )
          }
        )
      },
      parentPageDescriptionTimeText(description) {
        if (!description.hour || !description.minute || !description.second) {
          return ''
        }
        const h = ('00' + description.hour).slice(-2)
        const m = ('00' + description.minute).slice(-2)
        const s = ('00' + description.second).slice(-2)
        const text = h + ':' + m + ':' + s + ' '
        return text
      }
    }
  }
</script>

<style lang="scss" scoped>
  $wp-max-width: 782px;

  .mbox {
    .videoInput {
      margin: 0 0 1.5rem 0;
    }

    .videoInput_FileButton {
      width: 8.75rem;
      margin: .75rem 0 0 0;
      display: block;
    }

    .videoInput_UrlContent {
      display: flex;
      flex-direction: row;
      width: 100%;
      margin: .75rem 0 0 0;
    }

    .videoInput_UrlButton {
      width: 8.75rem;
      margin: 0;
    }

    .videoInput_Url {
      flex: 1;
      margin: 0;
    }

    .videoInput_Error {
      span {
        padding: .17rem 0 0 0;
        color: #dc3232;
        display: inline-block;
        width: 100%;
      }
    }

    .videoContent {
      margin: .75rem 0 0 0;
      max-width: 640px;
    }

    .videoClose {
      position: relative;
    }

    .videoCloseButton {
      cursor: pointer;
      z-index: 1000;
      background-color: rgba(255, 255, 255, 0);

      &:hover, &:focus,
      .descriptionVideo:hover &, .descriptionVideo:focus & {
        background-color: rgba(255, 255, 255, 0.9);
      }

      @media screen and (max-width: $wp-max-width) {
        background-color: rgba(255, 255, 255, 0.9);
      }
    }

    .description {
      margin: 0;
    }

    .descriptionTextInput {
      margin: .725rem 0 0 0;
      width: 100%;
      display: flex;
      align-items: flex-start;
      flex-wrap: wrap;
    }

    .descriptionTextInputTime {
      width: 8rem;
      display: flex;
    }

    .descriptionTextInputHh {
      vertical-align: top;
      width: 1.5rem;
      flex: 1;
    }

    .descriptionTextInputMm {
      vertical-align: top;
      width: 1.5rem;
      flex: 1;
    }

    .descriptionTextInputSs {
      vertical-align: top;
      width: 1.5rem;
      flex: 1;
    }

    .descriptionTextInputHh.sw-Flg_Error,
    .descriptionTextInputMm.sw-Flg_Error,
    .descriptionTextInputSs.sw-Flg_Error {
      border-color: #dc3232;
    }

    .descriptionTextInput span {
      vertical-align: top;
      display: inline-block;
      margin: .25rem .25rem 0 .25rem;
    }

    .descriptionTextInput textarea {
      margin: 0 0 0 .75rem;
      flex: 1;
    }

    .descriptionTextInput textarea.sw-Flg_Error {
      border-color: #dc3232;
    }

    .descriptionTextInputError {
      span {
        padding: 0;
        color: #dc3232;
        display: inline-block;
        width: 100%;

        &:first-child {
          padding: .4rem 0 0 0;

        }
      }
    }

    .descriptionTextInputControl {
      margin: .725rem 0 0 0;
      display: flex;
      flex-direction: row-reverse;
    }

    .descriptionTextInputControl-error {
      /* if show errors, error area height serves as margin */
      margin: 0;
    }

    .descriptionTextTable {
      margin: .725rem 0 0 0;
      display: flex;
      flex-flow: column;
      width: 100%;
      box-sizing: border-box;
    }

    .descriptionTextTableHead {
      flex: 0 0 auto;
      width: 100%;
    }

    .descriptionTextTableRow {
      width: 100%;
      display: flex;
    }

    th.descriptionTextTable_Time {
      cursor: pointer;
      padding: 0;
      a {
        width: 180px;
        box-sizing: border-box;
        padding: .5rem .5rem;
      }
    }

    th.descriptionTextTable_Text {
      cursor: pointer;
      flex: 1;
      width: auto;
      padding: 0;
      a {
        box-sizing: border-box;
        padding: .5rem 0.7rem;
      }
    }

    .descriptionTextTableBody {
      position: relative;
      flex: 1 1 auto;
      width: 100%;
      display: block;
      overflow-y: scroll;
      height: 300px;
    }

    .descriptionRelation {
      width: 100%;
      margin: 1.5rem 0 0 0;
      display: flex;
      width: 100%;
      flex-direction: row;

      @media screen and (max-width: $wp-max-width) {
        flex-direction: column;
      }
    }

    .descriptionRelationWrapper {
      display: flex;
      flex-direction: column;
      width: 70%;

      @media screen and (max-width: $wp-max-width) {
        width: 100%;
      }
    }

    .descriptionRelationTitle {
      margin: 0 0 .4rem 0;
    }

    .descriptionRelationBookPage {
      display: flex;
      flex-direction: row;
      height: 200px;
    }

    .descriptionRelationBook {
      flex: 1;
      float: left;
      position: relative;
      display: flex;
      flex-direction: column;
      select {
        flex: 1;
      }
    }

    .descriptionRelationBookInput {
      input {
        width: 99%;
      }
    }

    .descriptionRelationSpace {
      width: 20px;
      height: 100%;
      float: left;
      position: relative;
      text-align: center;
    }

    .descriptionRelationSpace i {
      position: relative;
      top: 45%;
    }

    .descriptionRelationPage {
      flex: 1;
      float: left;
      position: relative;
      display: flex;
      flex-direction: column;
      select {
        flex: 1;
      }
    }

    .descriptionRelationPageInput {
      input {
        width: 99%;
      }
    }

    .descriptionRelationBook select,
    .descriptionRelationPage select {
      width: 99%;
    }

    .descriptionRelationPagePreviewWrapper {
      display: flex;
      flex-direction: column;
      flex: 1;
      padding-left: 20px;

      @media screen and (max-width: $wp-max-width) {
        margin: 1.5rem 0 0 0;
        padding-left: 0;
      }
    }

    .descriptionRelationPagePreview {
      float: left;
      position: relative;
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 200px;
    }

    .descriptionRelationPagePreviewWrapper1 {
      overflow-x: hidden;
      overflow-y: scroll;
      border: 1px solid #ddd;
      background-color: #fff;
      outline: 0;
      flex: 1;
    }

    .descriptionRelationPagePreviewTitle {
      margin: 0 0 .4rem 0;
    }

    .descriptionRelationPagePreview dl:first-of-type {
      margin-top: .2rem;
    }

    .descriptionRelationPagePreview dl {
      padding: 0;
      margin: .65rem 0 0 .35rem;
    }

    .descriptionRelationPagePreview dt {
      padding: 0;
      margin: 0;
    }

    .descriptionRelationPagePreview dd {
      padding: 0;
      margin: 0;
    }
  }
</style>
