/**
 * Values passed by wordpress wp_localize_script function.
 */
export default {
  svm_args: Object.assign(
    {
      // wordpress locale setting
      locale: 'en',
      // post id of manual post type
      svm_book_post_id: '',
      // manual post data
      manual_post_data: [],
      // manual post pages data
      manual_pages_post_data: []
    },
    window.svm_args || {}
  )
}




