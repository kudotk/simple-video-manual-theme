/**
 * Values passed by wordpress wp_localize_script function.
 */
export default Object.assign(
  {
    // wordpress locale setting
    locale: 'en',
    
    // parameters are used by exporting
    ajax_call_data: {
      action: '',
      process_step: '',
      svm_book_post_id: '',
      svm_include_media: '',
      svm_export_nonce: '',
    },
    admin_ajax_url: ''
  },
  window.svm_args || {}
)
