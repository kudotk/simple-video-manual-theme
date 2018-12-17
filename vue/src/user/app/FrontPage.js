/**
 * Page class
 *
 * Corresponding to the post of manual book post type.
 */
export default class FrontPage {
  /**
   * constructor
   *
   * @param {object} wpPost wordpress post object.
   */
  constructor (wpPost) {
    // WPPost ID
    this.id = wpPost.ID

    // WPPost object
    this.raw = wpPost

    // WPPost title
    this.title = wpPost.post_title ? wpPost.post_title : ''

    // WPPost contents text
    this.textContent = wpPost.post_content ? wpPost.post_content : ''
  }
}
