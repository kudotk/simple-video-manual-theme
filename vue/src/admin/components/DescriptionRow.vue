<template>
  <tr class="adr-v-description-text-root author-self status-inherit is-expanded inline-edit-row description">
    <td
      class="time date column-date column-primary"
      @mouseover="mouseOverTime(true)"
      @mouseleave="mouseOverTime(false)"
      :data-colname="$t('Time')"
    >
      <div class="timeContent">
        <div class="timeInput">
          <div
            class="timeInputLabel"
            :class="{ 'timeInputLabel-label': !activeTimeEdit, 'timeInputLabel-edit': activeTimeEdit }"
          >
            <span v-text="hourLabel"/><span class="timeInputMark">:</span><span
              v-text="minuteLabel"/><span class="timeInputMark">:</span><span
                v-text="secondLabel"/>
          </div>
          <div
            class="timeInputEdit"
            :class="{ 'timeInputEdit-label': !activeTimeEdit, 'timeInputEdit-edit': activeTimeEdit }"
          >
            <input
              type="text"
              class="timeHh"
              placeholder="0"
              tabindex="0"
              :name="inputHour"
              v-model="description.hour"
              v-validate="{ required: true, min_value: 0, max_value: 23 }"
              @focus="focusTime"
              @blur="blurTime"
              :class="{ 'sw-Flg_Error': timeError}"
              data-vv-as="Description Hour"
            ><span>:</span><input
              type="text"
              class="timeMm"
              placeholder="0"
              tabindex="0"
              :name="inputMinute"
              v-model="description.minute"
              v-validate="{ required: true, min_value: 0, max_value: 59 }"
              @focus="focusTime"
              @blur="blurTime"
              :class="{ 'sw-Flg_Error': timeError}"
              data-vv-as="Description Minute"
            ><span>:</span><input
              type="text"
              class="timeSs"
              placeholder="0"
              tabindex="0"
              :name="inputSecond"
              v-validate="{ required: true, min_value: 0, max_value: 59 }"
              v-model="description.second"
              @focus="focusTime"
              @blur="blurTime"
              :class="{ 'sw-Flg_Error': timeError}"
              data-vv-as="Description Second"
            >
          </div>
        </div>
        <span
          class="timeInputError"
          v-show="timeError"
          v-text="timeError"/>
        <div
          class="row-actions timeAction"
          :class="{ 'timeAction-edit': activeTimeEdit }"
        >
          <span class="delete">
            <a
              class="submitdelete aria-button-if-js"
              tabindex="0"
              role="button"
              @click="deleteText($event)"
              @focus="focusDeleteText"
              @blur="blurDeleteText"
            >Delete
            </a>
          </span>
        </div>
      </div>
    </td>
    <td
      class="title column-title has-row-actions text"
      @mouseover="mouseOverText(true)"
      @mouseleave="mouseOverText(false)"
      :data-colname="$t('Text')"
    >
      <div class="filename textContent">
        <span
          class="textLabel"
          ref="descriptionTextLabel"
          v-text="descriptionTextLabel"
          :class="{ 'textLabel-label': !activeTimeEdit, 'textLabel-edit': activeTimeEdit }"
        />
        <textarea
          class="textInput"
          tabindex="0"
          ref="descriptionText"
          v-validate="{ required: true }"
          v-model="description.text"
          :name="inputDescriptionText"
          :class="{ 'sw-Flg_Error': descriptionTextError, 'textInput-label': !activeTimeEdit, 'textInput-edit': activeTimeEdit }"
          :placeholder="$t('Input description text here')"
          @focus="focusText"
          @blur="blurText"
          data-vv-as="Description Text"
        />
      </div>
      <span
        class="textInputError"
        v-show="descriptionTextError"
        v-text="descriptionTextError"
      />
    </td>
  </tr>
</template>

<script>
  import DescriptionRow from './DescriptionRow'

  export default {
    name: 'DescriptionRow',
    components: {
      DescriptionRow
    },
    props: {
      no: {
        type: Number
      },
      description: {
        type: Object,
        default() {
          return {text: '', hour: 0, minute: 0, second: 0}
        }
      }
    },
    mounted() {
      this.$validator.validate()
      this.adjustTextAreaHeight()
    },
    data() {
      return {
        timeEditable: false,
        textEditable: false,
        rowHour: 2,
        activeTimeEdit: false,
        activeTextEdit: false
      }
    },
    computed: {
      descriptionTextError() {
        const msg = (this.$validator.errors.has(this.inputDescriptionText))
          ? this.$t('Input description text')
          : ''
        return msg
      },
      timeError() {
        if (!(this.fields[this.inputHour] && this.fields[this.inputMinute] && this.fields[this.inputSecond])) {
          return ''
        }
        // show messages after edit hour, min and sec input.
        const keys = [this.inputHour, this.inputMinute, this.inputSecond]
        const exist = keys.some((key) => {
          return this.$validator.errors.has(key)
        })
        const msg = exist ? this.$t('Time value is invalid') : ''
        return msg
      },
      inputHour() {
        return 'svm_input_hour_' + this.no
      },
      inputMinute() {
        return 'svm_input_minute_' + this.no
      },
      inputSecond() {
        return 'svm_input_second_' + this.no
      },
      inputDescriptionText() {
        return 'svm_input_text_' + this.no
      },
      hourLabel() {
        return this.description.hour ? ('00' + this.description.hour).slice(-2) : ''
      },
      minuteLabel() {
        return this.description.minute ? ('00' + this.description.minute).slice(-2) : ''
      },
      secondLabel() {
        return this.description.second ? ('00' + this.description.second).slice(-2) : ''
      },
      descriptionTextLabel() {
        return this.description.text ? this.description.text : this.$t('Input description text here')
      }
    },
    methods: {
      blurTime(e) {
        this.activeTimeEdit = false
        this.activeTextEdit = false
        const name = e.currentTarget.getAttribute('name')
        this.fields[name].dirty = true
      },
      focusTime() {
        this.activeTimeEdit = true
        this.activeTextEdit = true
      },
      blurDeleteText() {
        this.activeTimeEdit = false
        this.activeTextEdit = false
      },
      focusDeleteText() {
        this.activeTimeEdit = true
        this.activeTextEdit = true
      },
      deleteText() {
        this.$emit('delete-text', {index: this.no})
      },
      blurText() {
        this.activeTextEdit = false
        this.activeTimeEdit = false
      },
      focusText() {
        this.activeTextEdit = true
        this.activeTimeEdit = true
      },
      mouseOverTime(on) {
        this.activeTimeEdit = on
        this.activeTextEdit = on
      },
      mouseOverText(on) {
        this.activeTextEdit = on
        this.activeTimeEdit = on
        this.adjustTextAreaHeight()
      },
      adjustTextAreaHeight() {
        // adjust textarea height as same as description label.
        const height = jQuery(this.$refs.descriptionText).outerHeight()
        jQuery(this.$refs.descriptionText).css({height: height})
        jQuery(this.$refs.descriptionTextLabel).css({height: height - 1})
      }
    }
  }
</script>

<style lang="scss" scoped>
  $wp-max-width: 782px;
  
  .description {
    display: flex;
    @media screen and (max-width: $wp-max-width) {
      display: block;
      width: 100%;
    }

    .time {
      width: 180px;
      box-sizing: border-box;
      padding: .4rem;
      margin: 0;

      @media screen and (max-width: $wp-max-width) {
        box-sizing: border-box;
        width: 100%;
        display: block;
        padding: .4rem .4rem 0 .4rem;
      }
    }

    .timeContent {
      display: flex;
      flex-direction: column;
    }

    .timeInput {
      min-height: 1.85rem;
    }

    .timeInputLabel {
      &-edit {
        position: relative;
        left: -999px;
        height: 0;
      }
      @media screen and (max-width: $wp-max-width) {
        position: relative;
        left: -999px;
        height: 0;
      }
    }

    .timeInputEdit {
      &-label {
        position: relative;
        left: -999px;
        height: 0;
      }
      @media screen and (max-width: $wp-max-width) {
        position: static;
        height: auto;
      }
    }

    .timeAction {
      &-edit {
        margin-top: .17rem;
        position: static;
      }
      @media screen and (max-width: $wp-max-width) {
        margin-top: .17rem;
        position: static;
      }
      a {
        cursor: pointer;
      }
    }

    .timeInputMark {
      vertical-align: top;
      display: inline-block;
      margin: .25rem .25rem 0 .25rem;
    }

    .timeInput {
      span {
        vertical-align: top;
        display: inline-block;
        margin: .25rem .25rem 0 .25rem;
      }
    }

    .timeHh {
      vertical-align: top;
      width: 2.65rem;
      &.sw-Flg_Error {
        border-color: #dc3232;
      }
    }

    .timeMm {
      vertical-align: top;
      width: 2.65rem;
      &.sw-Flg_Error {
        border-color: #dc3232;
      }
    }

    .timeSs {
      vertical-align: top;
      width: 2.65rem;
      &.sw-Flg_Error {
        border-color: #dc3232;
      }
    }

    .timeInputError {
      color: #dc3232;
      margin: .17rem 0 0 0;

      @media screen and (max-width: $wp-max-width) {
        margin-top: .4rem;
        display: inline-block;
      }
    }

    .text {
      flex: 1;
      padding: .4rem;
      margin: 0;

      @media screen and (max-width: $wp-max-width) {
        box-sizing: border-box;
        width: 100%;
        display: block;
        padding: .6rem .4rem .4rem .4rem;
      }
    }

    .textContent {
      overflow: hidden;
    }

    .textLabel {
      width: 100%;
      margin: 1px;
      padding: .125rem .375rem;
      display: block;
      height: 60px;

      &-edit {
        position: absolute;
        left: -99999px;
      }
      @media screen and (max-width: $wp-max-width) {
        position: absolute;
        left: -99999px;
      }
    }

    .textInput {
      width: 100%;
      overflow: hidden;
      display: inline-block;
      /* Prevent a little off when MouseOver. */
      height: 61px;
      &.sw-Flg_Error {
        border-color: #dc3232;
      }

      &-label {
        position: absolute;
        left: -99999px;
      }
      @media screen and (max-width: $wp-max-width) {
        position: static;
      }
    }

    .textInputError {
      color: #dc3232;
      margin: .17rem 0 0 0;

      @media screen and (max-width: $wp-max-width) {
        margin-top: .4rem;
        display: inline-block;
      }
    }
  }
</style>
