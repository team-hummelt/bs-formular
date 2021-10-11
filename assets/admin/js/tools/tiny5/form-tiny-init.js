tinymce.init({
    selector: "textarea.formulare-tinymce",
    language: 'de',
    height: 400,
    content_css: bs_form.admin_url + '/assets/admin/css/tinyCustom.css',
    valid_elements: '*[*]',
    schema: "html5",
    verify_html: false,
    valid_children: "+a[div], +div[*]",
    extended_valid_elements: "div[*]",
    force_p_newlines: false,
    forced_root_block: false,
});