/**
 * Values set here. These are used when the app runs on node env.
 */
export default {
  svm_args: {
    // wordpress locale setting
    locale: 'en',
    // post id of manual post type     
    svm_book_post_id: '<set wordpress manual post id on your enviriontment.>',
    // wordpress Host
    wp_rest_endpoint_base: 'http://127.0.0.1/wp-json/svm/v1',
  }
}
