/**
 * Page class
 * 
 * Corresponding to the post of manual page post type.
 */
export default class Page {
  /**
   * constructor
   *
   * @param {object} wpPost wordpress post object.
   */
  constructor (wpPost) {
    // Child pages
    this.pages = []

    // WPPost ID
    this.id = wpPost.ID

    // WPPost object
    this.raw = wpPost

    // video src path
    this.videoSrc = ''
    if (wpPost.post_meta && wpPost.post_meta.svm_video_meta) {
      const videoMeta = wpPost.post_meta.svm_video_meta
      this.videoSrc = videoMeta.url
    }

    // video's description texts
    this.descriptions = []
    if (wpPost.post_meta && wpPost.post_meta.svm_description_data_json) {
      const json = JSON.parse(wpPost.post_meta.svm_description_data_json)
      if (json.descriptions && Array.isArray(json.descriptions)) {
        this.descriptions = json.descriptions
      }
    }

    // WPPost title
    this.title = wpPost.post_title ? wpPost.post_title : ''

    // WPPost contents text
    this.textContent = wpPost.post_content ? wpPost.post_content : ''

    // update date
    this.updatedDate = undefined
    const ymdhms = wpPost.post_date_gmt.split(' ')
    const ymd = ymdhms[0].split('-')
    const hms = ymdhms[1].split(':')
    this.updatedDate = new Date(ymd[0], (ymd[1] - 1), ymd[2], hms[0], hms[1], hms[2]).getTime()

    // property that the search-function use.
    this.searchProp = {
      joinedDescriptionText: this.buildDescriptionText(wpPost)
    }

    // build child page object.
    if (wpPost.pages && wpPost.pages.length > 0) {
      wpPost.pages.forEach(childPage => {
        this.pages.push(new Page(childPage))
      })
    }
  }

  /**
   * Build text that search-function use.
   *
   * @param {object} wpPost WPPost object.
   * @returns {string} Text for search-function.
   */
  buildDescriptionText (wpPost) {
    if (!(wpPost.post_meta && wpPost.post_meta && wpPost.post_meta.svm_description_data_json)) {
      return ''
    }
    let obj = {}
    try {
      obj = JSON.parse(wpPost.post_meta.svm_description_data_json)
    } catch (e) {
      return ''
    }
    let text = ''
    if (obj.descriptions && Array.isArray(obj.descriptions)) {
      obj.descriptions.forEach((elm, i) => {
        if (i > 0) {
          text += ' '
        }
        text += elm.text
      })
    }
    return text
  }
}
