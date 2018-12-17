<template>
  <v-container
    class="ma-0 pa-0 manualPage"
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
      >
        <article
          class="content"
        >
          <h1
            class="headline mt-4 mb-0 primary--text"
            v-text="pageTitle"
          />
          <p
            class="caption mt-2 text--secondary"
            v-text="pageUpdatedDateLabel"
          />
          <v-layout
            class="headline noDataMessage"
            v-if="noPageData"
            align-center
          >
            <p
              class="text-xs-center noDataMessageText"
            >{{ $t('No Data') }}</p>
          </v-layout>                  
          <p
            class="body-1 mt-4"
            v-if="headerText"
            v-html="headerText"
          />
          <div
            class="mt-4"
            v-show="existVideo"
          >
            <video
              class="videoSrc"
              ref="video"
              controls
              :src="videoSrc"
              @play="playVideo"
              @pause="pauseVideo"
            />
          </div>
          <v-layout
            class="description mt-3"
            v-show="existDescriptions"
            row
            align-center
          >
            <v-btn
              class="ma-0 pa-0 descriptionPrev"
              flat
              dark
              large
              @click.prevent="clickPrev"
            >
              <v-icon dark>keyboard_arrow_left</v-icon>
            </v-btn>
            <v-layout
              class="px-2"
              row
              align-center
              justify-center
            >
              <transition
                name="page-fade"
                mode="out-in">
                <p
                  class="title overflow-hidden descriptionText ma-0"
                  :key="subtitle"
                >
                  {{ subtitle }}
                </p>
              </transition>
            </v-layout>
            <v-btn
              class="ma-0 pa-0 descriptionNext"
              flat
              dark
              large
              @click.prevent="clickNext"
            >
              <v-icon dark>keyboard_arrow_right</v-icon>
            </v-btn>
          </v-layout>
          <p
            class="caption mt-3 mx-0 mb-0 descriptionPageNo text--secondary text-xs-center"
            v-show="existDescriptions"
            d-inline-block
          >{{ currentPageNo }} / {{ pageCount }}
          </p>
          <div
            v-show="existDescriptions"
          >
            <h2 class="body-2 mt-3">
              {{ $t('Description list') }}
            </h2>
            <div class="mt-2 mb-5 pa-0 timetableBorder">
              <v-list
                class="timetableList"
                two-line
                subheader
              >
                <v-list-tile
                  ripple
                  v-for="(message, index) in messages"
                  :key="index + '_' + message.time"
                  @click="clickTimeTableRow(index)"
                >
                  <v-list-tile-content>
                    <v-list-tile-title
                      class="subtitle-2"
                      :class="{ 'font-weight-bold': (currentMsgIdx === index) }"
                    >{{ timeTableTimeLabel(message) }}
                    </v-list-tile-title>
                    <v-list-tile-sub-title
                      class="body-2 text--secondary"
                      :class="{ 'font-weight-bold': (currentMsgIdx === index) }"
                    >{{ message.timeLabel }}
                    </v-list-tile-sub-title>
                  </v-list-tile-content>
                </v-list-tile>
              </v-list>
            </div>
          </div>
        </article>
      </v-flex>
    </v-layout>
  </v-container>
</template>

<script>
  import titleMixin from '../mixin/TitleMixin'
  import throttle from 'lodash-es/throttle'

  export default {
    mixins: [titleMixin],
    props: {
      frontPageTitle: 'a',
      frontPageHtml: '<strong>hoge</strong>',
      page: {
        type: Object,
        default() {
          return {}
        }
      }
    },
    data() {
      return {
        videoTimer: null,
        currentMsgIdx: -1,
        pageDataLoaded: false,
        videoCanPlay: 'wait',
        headerText: '',
        pageUpdateDate: undefined,
        messages: [],
        currentVideoTime: 0,
        subtitle: '',
        pageCount: '-',
        timeTableList: undefined,
        timeTableMaxTextLength: 150
      }
    },
    computed: {
      currentPageNo() {
        if (this.currentMsgIdx === -1) {
          return '-'
        }
        return this.currentMsgIdx + 1
      },
      existDescriptions() {
        return (this.messages && (this.messages.length > 0))
      },
      existVideo() {
        return this.page.videoSrc && (this.videoCanPlay === 'ok')
      },
      noPageData() {
        return !this.headerText && !this.existDescriptions && !this.existVideo
      },
      pageTitle() {
        return this.page.title
      },
      pageUpdatedDateLabel() {
        let label = ''
        if (this.page.updatedDate) {
          label = this.$t('Last update') + ' ' + this.$d(this.page.updatedDate, 'short')
        }
        return label
      },
      videoSrc() {
        return this.page.videoSrc
      }
    },
    watch: {
       currentVideoTime: throttle(function (val) {
        // update sub-title, page-no.
        if (this.messages && (this.messages.length !== 0)) {
          let text = ''
          let idx

          idx = this.getMessageIndex(this.messages, val)
          if (idx !== -1) {
            text = this.messages[idx].text
          }
          this.subtitle = text
          this.currentMsgIdx = idx
        }
      }, 200),     
      page() {
        this.initializePage()
      }
    },
    mounted() {
      this.initializePage()
    },
    methods: {
      playVideo() {
        if (this.videoTimer) {
          clearInterval(this.videoTimer)
        }
        this.videoTimer = setInterval(() => {
          this.currentVideoTime = this.$refs.video.currentTime
        }, 200)
      },
      pauseVideo() {
        if (this.videoTimer) {
          clearInterval(this.videoTimer)
        }
      },
      clickPrev() {
        let idx = this.getMessageIndex(this.messages, this.$refs.video.currentTime)
        idx = idx - 1
        if (idx < 0) {
          return
        }
        this.$refs.video.pause()
        this.$refs.video.currentTime = this.messages[idx].time
        this.currentVideoTime = this.messages[idx].time
      },
      clickNext() {
        let idx = this.getMessageIndex(this.messages, this.$refs.video.currentTime)
        idx = idx + 1
        if (this.messages.length <= idx) {
          return
        }
        this.$refs.video.pause()
        this.$refs.video.currentTime = this.messages[idx].time
        this.currentVideoTime = this.messages[idx].time
      },
      getMessageIndex(messages, sec) {
        if (!messages || (messages.length === 0)) {
          return -1
        }
        let lIdx = 0
        let rIdx = messages.length
        let idx = Math.floor((lIdx + rIdx) / 2)
        // keep average search time cost.
        while (lIdx < idx) {
          if (messages[idx].time <= sec) {
            lIdx = idx
          } else {
            rIdx = idx
          }
          idx = Math.floor((lIdx + rIdx) / 2)
        }
        if (sec < messages[idx].time) {
          idx = -1
        }
        return idx
      },
      clickTimeTableRow(index) {
        const message = this.messages[index]
        this.$refs.video.pause()
        this.$refs.video.currentTime = message.time
        this.currentVideoTime = message.time
      },
      timeTableTimeLabel(message) {
        if (!message.text) {
          return ''
        }
        let text = message.text
        if (this.timeTableMaxTextLength < text.length) {
          text = text.substr(0, this.timeTableMaxTextLength) + '...'
        }
        return text
      },
      loadVideoSrc() {
        return new Promise((resolve, reject) => {
          if (!this.videoSrc) {
            resolve(true)
          } else {
              this.$refs.video.addEventListener('canplay', (e) => {
                this.videoCanPlay = 'ok'
                resolve(e)
              }, {
                once: true
              })
              this.$refs.video.addEventListener('error', (e) => {
                  this.videoCanPlay = 'error'
                  reject(e)
                }, {
                  once: true
                }
              )

              this.$refs.video.load()
          }
        })
      },
      async initializePage() {
        if (!this.page.id) {
          return
        }
        
        this.videoCanPlay = 'not'

        if (this.page.videoSrc) {
          await this.loadVideoSrc()
        }

        let messages = []
        if (this.page.descriptions) {
          this.page.descriptions.forEach((elm) => {
            const sec = (parseInt(elm.hour, 10) * 60 * 60) + (parseInt(elm.minute, 10) * 60) + parseInt(elm.second)
            const timeLabel = ('00' + elm.hour).slice(-2) + ':' + ('00' + elm.minute).slice(-2) + ':' + ('00' + elm.second).slice(-2)
            const text = elm.text
            messages.push({
              time: sec,
              timeLabel: timeLabel,
              text: text
            })
          })
          messages.sort((a, b) => {
            if (a.time < b.time) {
              return -1
            } else if (a.time > b.time) {
              return 1
            }
            return 0
          })
        }

        this.headerText = this.page.textContent ? this.page.textContent.replace(/\n/g, '<br>') : ''
        this.messages = messages
        this.pageCount = messages.length ? messages.length.toString() : '-'
        this.pageUpdateDate = this.page.updatedDate
        this.subtitle = ''
        this.currentMsgIdx = -1

        this.$emit('page-loaded')
      }
    }
  }
</script>

<style lang="scss" scoped>
  @import '../assets/css/_sass-loader-import.scss';

  .manualPage {
    .content {
      width: 100%;
      max-width: 900px;
      margin: auto;
    }

    .noDataMessage {
      height: 40vh;
    }
    
    .noDataMessageText {
      width: 100%;
    }   

    .videoSrc {
        width: 100%;
        height: 100%;
    }

    .description {
      min-height: 100px;
    }

    .descriptionText {
      text-align: center;
      max-height: 100px;
      line-height: 1.3 !important;
    }

    .descriptionPageNo {
      width: 100%;
    }

    .timetableBorder {
      border: solid 1px $color-primary;
    }
    
    .timetableList {
      height: 300px;
      overflow-y: scroll;

      & /deep/ .v-list__tile {
        height: 64px;
      }
    }

    .descriptionPrev {
      min-width: 44px;
      width: 44px;
      color: $color-primary;
      caret-color: $color-primary;
    }
    
    .descriptionNext {
      min-width: 44px;
      width: 44px;
      color: $color-primary;
      caret-color: $color-primary;
    }
  }
</style>

<style lang="scss">
  @import '../assets/css/_sass-loader-import.scss';
  @include transitionFade(page-fade, $base-animation-duration);
</style>
