jQuery(document).ready(function ($) {

    $('#TableFormulare').DataTable({
        "language": {
            "url": bs_form.data_table
        },

        "columns": [
            null,
            null,
            null,

            {
                "width": "3%"
            },
            {
                "width": "3%"
            }
        ],
        columnDefs: [{
            orderable: false,
            targets: [3,4]
        },
            {
                targets: [3, 4],
                className: 'text-center'
            },
            {
                targets: ['_all'],
                className: 'align-middle'
            }
        ],
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: bs_form_ajax_obj.ajax_url,
            type: 'POST',
            data : {
                action: 'BsFormularHandle',
                '_ajax_nonce': bs_form_ajax_obj.nonce,
                method: 'formular_data_table'
            }
        }
    });

    $('#TablePosts').DataTable({
        "language": {
            "url": bs_form.data_table
        },

        "columns": [
            null,
            null,
            null,
            null,

            {
                "width": "5%"
            },
            {
                "width": "5%"
            }
        ],
        columnDefs: [{
            orderable: false,
            targets: [4,5]
        },
            {
                targets: [],
                className: 'text-center'
            },
            {
                targets: ['_all'],
                className: 'align-middle'
            }
        ],
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": {
            url: bs_form_ajax_obj.ajax_url,
            type: 'POST',
            data : {
                action: 'BsFormularHandle',
                '_ajax_nonce': bs_form_ajax_obj.nonce,
                method: 'formular_post_table'
            }
        }
    });
});