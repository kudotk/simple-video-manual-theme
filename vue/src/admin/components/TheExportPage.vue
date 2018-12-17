<template>
  <div class="wrap process export">
    <h1 class="wp-heading-inline">
      {{ $t('Export Manual Page Files') }}
    </h1>
    <div class="bar"/>
    <p class="message">
    <span>0/3 {{ $t('Preparing') }}</span><span>{{ progressSuffix }}</span></p>
    <p
      class="link"
      aria-hidden="true"
    >
      {{ $t('If not start to download, Click Download.') }}.
    </p>
    <button
      type="button"
      class="button button-primary download"
      aria-hidden="true"
      @click.prevent="clickDownload"
    >
      {{ $t('Download') }}
    </button>
  </div>
</template>

<script>
  import config from '../app/config'
  import i18nMixin from '../mixin/I18nMixin'

  export default {
    name: 'TheExportPage',
    mixins: [i18nMixin],
    data() {
      return {
        downloadArchiveUrl: '',
        progressSuffixCountTimer: undefined,
        progressSuffixCount: 0
      }
    },
    mounted() {
      // call starting an export method.
      jQuery('.bar').progressbar({
        value: 0
      })
      this.progressSuffixCountTimer = setInterval(() => {
        this.incrementProgressSuffix()
      }, 500)
      this.$nextTick(() => {
        this.requestNextProcess({
          process_step: '1'
        })
      })
    },
    computed: {
      progressSuffix() {
        return '...'.slice(0, this.progressSuffixCount)
      }
    },
    methods: {
      incrementProgressSuffix() {
        this.progressSuffixCount = (this.progressSuffixCount >= 3) ? 0 : (this.progressSuffixCount + 1)
      },
      clearProgressSuffix() {
        jQuery('.message span').eq(1).attr('aria-hidden', true)
        clearInterval(this.this.progressSuffixCountTimer)
        this.progressSuffixCount = 0
      },
      clickDownload() {
        window.location.href = this.downloadArchiveUrl
      },
      requestNextProcess(appendData) {
        const data = config.ajax_call_data
        for (const key in appendData) {
          data[key] = appendData[key]
        }

        const displayError = (message) => {
          jQuery('.bar').addClass('bar-error')
          jQuery('.message').addClass('message-error').text(message)
          this.clearProgressSuffix()
        }

        jQuery.ajax({
          url: config.admin_ajax_url,
          type: 'POST',
          data: data
        }).then(
          (res) => {
            const data = res.data

            if (!res.success) {
              displayError(data.message)
              return
            }

            if (data.process_step === '1') {
              jQuery('.bar').progressbar({
                value: 33
              })
              setTimeout(() => {
                jQuery('.message span').eq(0).text('1/3 ' + this.$t('Building assets'))
                this.requestNextProcess({
                  process_step: '2'
                })
              }, 1000)
            } else if (data.process_step === '2') {
              jQuery('.bar').progressbar({
                value: 66
              })
              setTimeout(() => {
                jQuery('.message span').eq(0).text('2/3 ' + this.$t('Compress files'))
                this.requestNextProcess({
                  process_step: '3'
                })
              }, 1000)
            } else if (data.process_step === '3') {
              jQuery('.bar').progressbar({
                value: 100
              })
              setTimeout(() => {
                jQuery('.message span').eq(0).text('3/3 ' + this.$t('Done. Wait for Downloading.'))
                jQuery('.link').attr('aria-hidden', false)
                jQuery('.download').attr('aria-hidden', false)
                this.downloadArchiveUrl = data.archive_url
                window.location.href = data.archive_url
                this.clearProgressSuffix()
              }, 1000)
            }
          },
          () => {
            displayError(this.$t('Unable to export files. Retry.'))
          }
        )
      }
    }
  }
</script>

<style lang="scss" scoped>
  .export {
    .bar {
      /* 10px: same as margin height between h1 and post-title text field in post-view */
      /* 20px: same as margin-left of view-area. */
      margin: 10px 20px 0 0;
      height: 20px;
      border: solid 1px #0073aa;

      & /deep/ .ui-progressbar-value {
        transition: width 1.0s ease-in-out;
        height: 20px;
        background-color: #0073aa;
      }

      &-error {
        /* 20px: same as margin-left of view-area. */
        margin: 1.0em 20px 0 0;
        height: 20px;
        border: solid 1px #dc3232;

        & /deep/ .ui-progressbar-value {
          transition: background-color 1.0s ease-in-out;
          height: 20px;
          background-color: #dc3232;
        }
      }
    }

    .message {
      margin: 0.5em 0;
      span[aria-hidden="true"] {
        display: none;
      }
      &-error {
        color: #dc3232;
      }
    }

    .link {
      margin: 0.5em 0;

      &[aria-hidden="true"] {
        display: none;
      }
    }

    .download {
      &[aria-hidden="true"] {
        display: none;
      }
    }
  }
</style>
