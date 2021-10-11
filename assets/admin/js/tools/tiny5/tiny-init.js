var useDarkMode = window.matchMedia('(prefers-color-scheme: dark)').matches;

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

tinymce.init({
    selector: "textarea.response-formulare-tinymce",
    language: 'de',
    height: 300,
});


tinymce.init({
    selector: "textarea.bs-formulare-tinymce",
    language: 'de',
    height: 400,
    image_advtab: true,
    image_uploadtab: false,
    image_caption: false,
    importcss_append: true,
    browser_spellcheck: true,
    toolbar_sticky: true,
    toolbar_mode: 'wrap',
    statusbar: true,
    draggable_modal: true,
    relative_urls: false,
    remove_script_host: false,
    convert_urls: true,
    //content_css: 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css',

    content_css: bs_form.admin_url + '/assets/admin/css/tinyCustom.css',
    valid_elements: '*[*]',
    schema: "html5",
    verify_html: false,
    valid_children: "+a[div], +div[*]",
    extended_valid_elements: "div[*]",

    //element_format : 'html',
   // forced_root_block_attrs: {
        //'class': 'tiny-p',
        //'data-something': 'my data'
   // },
    protect: [
        /\<\/?(if|endif)\>/g,  // Protect <if> & </endif>
        /\<xsl\:[^>]+\>/g,  // Protect <xsl:...>
        /<\?php.*?\?>/g  // Protect php code
    ],

    //force_br_newlines : false,
    force_p_newlines: false,
    forced_root_block: false,
    //remove_linebreaks : false,


    plugins: `print preview importcss searchreplace fullscreen
    autolink autosave save directionality visualblocks visualchars image link 
    media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime 
    advlist lists wordcount imagetools textpattern noneditable code spellchecker quickbars
    help charmap emoticons `,

    menu: {
        file: {title: 'File', items: 'newdocument restoredraft | preview | print '},
        edit: {title: 'Edit', items: 'undo redo | cut copy paste | selectall | searchreplace'},
        view: {title: 'View', items: 'code | visualaid visualchars visualblocks | spellchecker | preview fullscreen'},
        insert: {
            title: 'Insert',
            items: 'template link codesample inserttable | charmap emoticons hr | pagebreak nonbreaking anchor toc | insertdatetime'
        },
        format: {
            title: 'Format',
            items: 'bold italic underline strikethrough superscript subscript codeformat | formats blockformats fontformats fontsizes align lineheight | forecolor backcolor | removeformat'
        },
        tools: {title: 'Tools', items: ' code wordcount'},
        table: {title: 'Table', items: 'inserttable | cell row column | tableprops deletetable'},
        help: {title: 'Help', items: 'help'}
    },

    toolbar1: `undo redo | formatselect | fontsizeselect |
         bold italic forecolor backcolor | customInsertImage | alignleft aligncenter 
         alignright alignjustify | bullist numlist outdent indent | 
         code | preview  | template | fullscreen`,
    //toolbar2: 'alignleft aligncenter alignright',

    quickbars_selection_toolbar: `bold italic | forecolor backcolor | quicklink | alignleft aligncenter 
         alignright alignjustify | blockformats | h1 h2 h3 h4 h5 h6 `,


    /* templates: [
         {
             title: 'Vorlage auswählen...',
             description: '',
             content: ''
         },
         {
             title: 'E-Mail (Default-Template) Bestätigung Registrierung',
             description: '',
             content: email_template('email')
         },
         {title: 'Starting my story', description: 'A cure for writers block', content: 'Once upon a time...'},
         {
             title: 'New list with dates',
             description: 'New List with dates',
             content: '<div class="mceTmpl"><span class="cdate">cdate</span><br /><span class="mdate">mdate</span><h2>My List</h2><ul><li></li><li></li></ul></div>'
         }
     ],
     template_cdate_format: '[Datum erstellt (CDATE): %m/%d/%Y : %H:%M:%S]',
     template_mdate_format: '[Datum der Änderung (MDATE): %m/%d/%Y : %H:%M:%S]',
     */

    //templates: email_template(buchung_plugin_settings.tinymce_tmpl),

    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',

 /*   init_instance_callback: function (editor) {
        editor.on('click', function (e) {
            let t = e.target.className;
            let cl = e.path[1].className;
            cl = cl.substring(0, 8);
            console.log(e.path[1].className);
            if (t === 'delete-container') {
                tinymce.activeEditor.dom.remove(tinymce.activeEditor.dom.select('div.' + cl));
                //tinymce.activeEditor.execCommand('mceInsertContent', 0, `<p>${createRandomCode()}</p>`);
            }

            function createRandomCode(length = 8) {
                let randomCodes = '';
                let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let charactersLength = characters.length;
                for (let i = 0; i < length; i++) {
                    randomCodes += characters.charAt(Math.floor(Math.random() * charactersLength));
                }
                return randomCodes;
            }
        });

        editor.on('BeforeSetContent', function (e) {
            // e.content += 'My custom content!';
        });
    },*/

    setup: function (editor) {
        editor.ui.registry.addButton('customInsertImage', {
            icon: 'image',
            onAction: function () {
                editor.windowManager.open(ImgDialogConfig)
            }
        });
    }
});

/*===================================================================================================
====================================== TEMPLATE CONTENT VORLAGEN  ===================================
=====================================================================================================*/
function email_template(type) {

    let template = [];
    switch (type) {
        case'edit-template':
        case'add-template':
            let content_v1 = `<!-- [if (mso 16)]>
               <style type="text/css">
               a {text-decoration: none;}
               </style>
               <![endif]--><!-- [if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!-- [if gte mso 9]>
               <xml>
               <o:OfficeDocumentSettings>
               <o:AllowPNG></o:AllowPNG>
               <o:PixelsPerInch>96</o:PixelsPerInch>
               </o:OfficeDocumentSettings>
               </xml>
               <![endif]--> <!-- [if gte mso 9]>
               <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
               <v:fill type="tile" color="#efefef"></v:fill>
               </v:background>
               <![endif]-->
               <div class="es-wrapper-color" style="background-color: #efefef;">
               <table class="es-wrapper" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; padding: 0; margin: 0; width: 100%; height: 100%; background-repeat: repeat; background-position: center top;" width="100%" cellspacing="0" cellpadding="0">
               <tbody>
               <tr style="border-collapse: collapse;">
               <td style="padding: 0; margin: 0;" valign="top">
               <table class="es-footer" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; table-layout: fixed !important; width: 100%; background-color: transparent; background-repeat: repeat; background-position: center top;" cellspacing="0" cellpadding="0" align="center">
               <tbody>
               <tr style="border-collapse: collapse;">
               <td style="padding: 0; margin: 0;" align="center">
               <table class="es-footer-body" cellspacing="0px" cellpadding="0" align="center" style="border-collapse: collapse; width: 800px; height: 10px;">
               <tbody>
               <tr style="border-collapse: collapse; background-color: #fafafa;">
               <td style="margin: 0px; padding: 20px; width: 599px; height: 10px;" align="left">
               <table style="border-collapse: collapse; border-spacing: 0px; height: 800px; width: 100%;" width="100%" cellspacing="0" cellpadding="0">
               <tbody>
               <tr style="height: 775px;">
               <td style="padding: 0px; margin: 0px; width: 100%; text-align: left; height: 775px;">
               <table style="border-collapse: collapse; width: 99.8679%; height: 355px;">
               <tbody>
               <tr style="height: 19px;">
               <td style="padding: 0px; margin: 0px; width: 100%; height: 19px;">
               <table style="border-collapse: collapse; width: 99.8639%;">
               <tbody>
               <tr>
               <td style="width: 99.8638%;">[logo]</td>
               </tr>
               </tbody>
               </table>
               <p style="margin: 0; line-height: 18px; mso-line-height-rule: exactly; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 20px; font-style: normal; font-weight: normal; color: #666666;"></p>
               </td>
               </tr>
               <tr style="height: 25px;">
               <td style="padding: 0px; margin: 0px; height: 25px; width: 100%;" align="left">
               <h3 style="margin: 0; line-height: 18px; mso-line-height-rule: exactly; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 20px; font-style: normal; font-weight: normal; color: #666666;"><br />Willkommen zur Schulung "[schulung_name]"</h3>
               </td>
               </tr>
               <tr style="height: 37px;">
               <td style="padding: 15px 0px 0px; margin: 0px; height: 37px; width: 100%;" align="left">
               <p style="margin: 0; -webkit-text-size-adjust: none; -ms-text-size-adjust: none; mso-line-height-rule: exactly; font-family: arial, 'helvetica neue', helvetica, sans-serif; line-height: 21px; color: #999999; font-size: 14px;">Hiermit best&auml;tigen wir Ihre Buchung zur Schulung <strong>[schulung_name]</strong> am <strong>[date_start]</strong>.</p>
               <hr /></td>
               </tr>
               <tr style="height: 10px;">
               <td style="padding: 15px 0px 0px; margin: 0px; height: 10px; width: 100%;" align="left"><span style="font-weight: bolder; color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 18px;"><br />Details zu Ihrer Anmeldung:</span><br /><hr />
               <table border="0" style="border-collapse: collapse; width: 98%; height: 162px;">
               <tbody>
               <tr style="height: 40px;">
               <td style="width: 36.4284%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;"><strong>Firma:</strong>&nbsp;<br /></span></td>
               <td style="width: 63.3843%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;"><strong>[firma]</strong><br />[firma_adresse]<br /><br /></span></td>
               </tr>
               <tr style="height: 27px;">
               <td style="width: 36.4284%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;"><strong>Gebuchte Schulung:</strong>&nbsp;</span></td>
               <td style="width: 63.3843%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">[schulung_name]</span></td>
               </tr>
               <tr style="height: 27px;">
               <td style="width: 36.4284%; height: 27px; vertical-align: top;"><strong><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">Veranstaltungsort:&nbsp;</span></strong></td>
               <td style="width: 63.3843%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">[location]</span></td>
               </tr>
               <tr style="height: 27px;">
               <td style="width: 36.4284%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;"><strong>Zeitraum:</strong>&nbsp;</span></td>
               <td style="width: 63.3843%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">[schulung_von] bis [schulung_bis]</span></td>
               </tr>
               <tr style="height: 27px;">
               <td style="width: 36.4284%; height: 27px; vertical-align: top;"><strong><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">Preis:</span></strong></td>
               <td style="width: 63.3843%; height: 27px; vertical-align: top;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">[preis] ([preis_modell]) zzgl. MwSt.</span></td>
               </tr>
               <tr style="height: 27px;">
               <td style="width: 36.4284%; vertical-align: top; height: 27px;"><span style="font-weight: bolder; color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">Anzahl Teilnehmer:</span><strong><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;"><br /></span></strong></td>
               <td style="width: 63.3843%; vertical-align: top; height: 27px;"><span style="color: #999999; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 14px;">[anzahl_teilnehmer] Teilnehmer<br /></span></td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               <tr style="height: 264px;">
               <td style="padding: 15px 0px 0px; margin: 0px; width: 100%; height: 264px;" align="left"><br />[teilnehmer_liste]<br />[gutschein_liste]<br /><br /><br />
               <p style="margin: 0; -webkit-text-size-adjust: none; -ms-text-size-adjust: none; mso-line-height-rule: exactly; font-family: arial, 'helvetica neue', helvetica, sans-serif; line-height: 21px; color: #999999; font-size: 14px;"><span style="font-weight: bold;">Hinweis:<br /><span style="font-weight: 400;">Bei &Auml;nderungen, Fragen oder W&uuml;nschen Ihrer Buchung wenden Sie sich bitte Telefonisch oder per E-Mail direkt an das LVA Schulungszentrum.&nbsp;<br /><br /></span><span style="font-weight: 400;">Wir freuen uns darauf Sie begr&uuml;&szlig;en zu d&uuml;rfen. Vielen Dank f&uuml;r Ihrer Registrierung.<br /><br /></span></span></p>
               <p style="margin: 0; -webkit-text-size-adjust: none; -ms-text-size-adjust: none; mso-line-height-rule: exactly; font-family: arial, 'helvetica neue', helvetica, sans-serif; line-height: 21px; color: #999999; font-size: 14px;">Mit freundlichen Gr&uuml;&szlig;en</p>
               <p style="margin: 0; -webkit-text-size-adjust: none; -ms-text-size-adjust: none; mso-line-height-rule: exactly; font-family: arial, 'helvetica neue', helvetica, sans-serif; line-height: 21px; color: #999999; font-size: 14px;"><span style="font-weight: bold;"></span></p>
               <p style="margin: 0; -webkit-text-size-adjust: none; -ms-text-size-adjust: none; mso-line-height-rule: exactly; font-family: arial, 'helvetica neue', helvetica, sans-serif; line-height: 21px; color: #999999; font-size: 14px;">Ihr LVA Schulungszentrum<br /><br /></p>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               <tr style="border-collapse: collapse;">
               <td style="padding: 0px; margin: 0px; width: 100%; text-align: left; height: 25px;" align="center" valign="top"><br />[signature]</td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </div>`;

            let content_v2 = `<!-- [if (mso 16)]>
               <style type="text/css">
               a {text-decoration: none;}
               </style>
               <![endif]--><!-- [if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!-- [if gte mso 9]>
               <xml>
               <o:OfficeDocumentSettings>
               <o:AllowPNG></o:AllowPNG>
               <o:PixelsPerInch>96</o:PixelsPerInch>
               </o:OfficeDocumentSettings>
               </xml>
               <![endif]--> <!-- [if gte mso 9]>
               <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
               <v:fill type="tile" color="#efefef"></v:fill>
               </v:background>
               <![endif]-->
               <div class="es-wrapper-color" style="background-color: #efefef;">
               <table class="es-wrapper" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; padding: 0; margin: 0; width: 100%; height: 100%; background-repeat: repeat; background-position: center top;" width="100%" cellspacing="0" cellpadding="0">
               <tbody>
               <tr style="border-collapse: collapse;">
               <td style="padding: 0; margin: 0;" valign="top">
               <table class="es-footer" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; table-layout: fixed !important; width: 100%; background-color: transparent; background-repeat: repeat; background-position: center top;" cellspacing="0" cellpadding="0" align="center">
               <tbody>
               <tr style="border-collapse: collapse;">
               <td style="padding: 0; margin: 0;" align="center">
               <table class="es-footer-body" cellspacing="0px" cellpadding="0" align="center" style="border-collapse: collapse; width: 800px; height: 10px; background-color: #37763c;">
               <tbody>
               <tr style="border-collapse: collapse;">
               <td style="margin: 0px; padding: 20px; width: 599px; height: 10px;" align="left">
               <table style="border-collapse: collapse; width: 100.147%; height: 377px;">
               <tbody>
               <tr style="background-color: #fafafa;">
               <td style="width: 100%; height: 377px;">
               <table cellpadding="15" style="border-collapse: collapse; width: 99.5822%; margin-left: auto; margin-right: auto; height: 435px;">
               <tbody>
               <tr style="height: 385px;">
               <td style="width: 100%; text-align: center; height: 385px;">
               <table style="border-collapse: collapse; width: 99.6771%; height: 232px; margin-left: auto; margin-right: auto;">
               <tbody>
               <tr style="height: 52px;">
               <td style="width: 100%; height: 10px; text-align: left;"><span style="color: #666666; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 20px; text-align: center;">LVA Kundenschulungen</span><br style="color: #666666; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 20px; text-align: center;" /><span style="font-size: 14px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;"><span style="font-size: 14px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;">Digitales Schulungszentrum</span></span><span style="font-size: 14px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;"><br /></span></td>
               </tr>
               <tr style="height: 212px;">
               <td style="width: 100%; text-align: left; height: 212px;"><hr /><br />
               <table style="height: 20px; width: 99.86%;">
               <tbody>
               <tr style="height: 25px;">
               <td style="height: 10px; width: 100%; margin: 0px; padding: 0px;" align="left">
               <p style="font-size: 18px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 18px;">Vielen Dank f&uuml;r Ihre Anfrage.</p>
               </td>
               </tr>
               <tr style="height: 37px;">
               <td style="height: 10px; width: 100%; margin: 0px; padding: 15px 0px 0px; vertical-align: top;" align="left">
               <p style="font-size: 14px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;">Wir werden Ihre Anfrage <strong>schnellstm&ouml;glich </strong>bearbeiten.<br /><br />Mit freundlichen Gr&uuml;&szlig;en<br />Ihr LVA Schulungszentrum</p>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               <tr style="height: 60px;">
               <td style="width: 100%; text-align: left; height: 10px;"><br /><br />[signature]</td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </div>`;

            let content_v3 = `<!-- [if (mso 16)]>
               <style type="text/css">
               a {text-decoration: none;}
               </style>
               <![endif]--><!-- [if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!-- [if gte mso 9]>
               <xml>
               <o:OfficeDocumentSettings>
               <o:AllowPNG></o:AllowPNG>
               <o:PixelsPerInch>96</o:PixelsPerInch>
               </o:OfficeDocumentSettings>
               </xml>
               <![endif]--> <!-- [if gte mso 9]>
               <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
               <v:fill type="tile" color="#efefef"></v:fill>
               </v:background>
               <![endif]-->
               <div class="es-wrapper-color" style="background-color: #efefef;">
               <table class="es-wrapper" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; padding: 0; margin: 0; width: 100%; height: 100%; background-repeat: repeat; background-position: center top;" width="100%" cellspacing="0" cellpadding="0">
               <tbody>
               <tr style="border-collapse: collapse;">
               <td style="padding: 0; margin: 0;" valign="top">
               <table class="es-footer" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; table-layout: fixed !important; width: 100%; background-color: transparent; background-repeat: repeat; background-position: center top;" cellspacing="0" cellpadding="0" align="center">
               <tbody>
               <tr style="border-collapse: collapse;">
               <td style="padding: 0; margin: 0;" align="center">
               <table class="es-footer-body" cellspacing="0px" cellpadding="0" align="center" style="border-collapse: collapse; width: 800px; height: 10px;">
               <tbody>
               <tr style="border-collapse: collapse; background-color: #37763c;">
               <td style="margin: 0px; padding: 20px; width: 599px; height: 10px;" align="left">
               <table style="border-collapse: collapse; width: 100.147%;">
               <tbody>
               <tr style="background-color: #fafafa;">
               <td style="width: 100%;">
               <table cellpadding="15" style="border-collapse: collapse; width: 99.5822%; margin-left: auto; margin-right: auto; height: 435px;">
               <tbody>
               <tr style="height: 385px;">
               <td style="width: 99.8601%; text-align: center; height: 385px;">
               <table style="border-collapse: collapse; width: 98.1457%; height: 413px; margin-left: auto; margin-right: auto;">
               <tbody>
               <tr style="height: 52px;">
               <td style="width: 100%; height: 52px; text-align: left;"><span style="color: #666666; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 20px; text-align: center;">LVA Kundenschulungen</span><br style="color: #666666; font-family: arial, 'helvetica neue', helvetica, sans-serif; font-size: 20px; text-align: center;" /><span style="font-size: 14px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;">Digitales Schulungszentrum</span></td>
               </tr>
               <tr style="height: 89px;">
               <td style="width: 100%; height: 89px;"><hr /><br />
               <table style="border-collapse: collapse; width: 99.5378%; margin-left: auto; margin-right: auto;">
               <tbody>
               <tr>
               <td style="width: 16.5635%;"><span style="font-size: 13.3333px;">[group_img1]</span></td>
               <td style="width: 16.5635%;"><span style="font-size: 13.3333px;">[group_img2]</span></td>
               <td style="width: 16.5635%;"><span style="font-size: 13.3333px;">[group_img3]</span></td>
               <td style="width: 16.7183%;"><span style="font-size: 13.3333px;">[group_img4]</span></td>
               <td style="width: 16.7183%;"><span style="font-size: 13.3333px;">[group_img5]</span></td>
               <td style="width: 16.7183%;"><span style="font-size: 13.3333px;">[group_img6]</span></td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               <tr style="height: 212px;">
               <td style="width: 100%; text-align: left; height: 212px;"><br /><hr /><br />
               <table style="height: 57px; width: 99.86%;">
               <tbody>
               <tr style="height: 25px;">
               <td style="height: 10px; width: 100%; margin: 0px; padding: 0px;" align="left">
               <p style="font-size: 20px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 18px;">Vielen Dank f&uuml;r Ihre Anfrage<br /><br /></p>
               </td>
               </tr>
               <tr style="height: 37px;">
               <td style="height: 37px; width: 100%; margin: 0px; padding: 15px 0px 0px 0px;" align="left">
               <p style="font-size: 14px; font-family: arial, 'helvetica neue', helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;">Wir werden Ihre Anfrage <strong>schnellstm&ouml;glich </strong>bearbeiten.<br /><br />Mit freundlichen Gr&uuml;&szlig;en<br />Ihr LVA Schulungszentrum</p>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               <tr style="height: 60px;">
               <td style="width: 100%; text-align: left; height: 60px;"><br /><br />[signature]</td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </td>
               </tr>
               </tbody>
               </table>
               </div>`;

            template = [
                {
                    title: 'Vorlage auswählen...',
                    description: '',
                    content: ''
                },
                {
                    title: 'E-Mail Bestätigung Registrierung',
                    description: '',
                    content: content_v1
                },
                {
                    title: 'E-Mail Autoresponder',
                    description: '',
                    content: content_v2
                },
                {
                    title: 'E-Mail Autoresponder mit Gruppen Image',
                    description: '',
                    content: content_v3
                }
            ];
            break;
        case'email-signature':
            let signatur1 = `<hr />
               <h5><span style="font-size: 12pt; color: #34495e;">[logo-sig]<br />Landmaschinen Vertrieb Altenweddingen GmbH<br /></span><span style="color: #34495e;"> <small> <span style="font-size: 10pt;">Buttenkrug 1</span> <br /><span style="font-size: 10pt;">39171 S&uuml;lzetal OT Altenweddingen</span><br /></small> </span></h5>
               <h5><span style="color: #34495e;"><small><span style="font-size: 10pt;"><strong>Tel.:</strong><a href="tel: 0392056650"> 039 205 / 665-0</a></span><br /><span style="font-size: 10pt;"><strong>Fax:</strong> 039 205 / 665-55</span><br /><span style="font-size: 10pt;"><strong>E-Mail:</strong><a href="mailto:info@lvaltenweddingen.de"> info@lvaltenweddingen.de</a></span><br /></small> </span></h5>
               <h5><span style="color: #34495e;"><small><span style="color: #34495e;"><span style="font-size: 10pt;"><strong>Gesch&auml;ftsf&uuml;hrer : </strong> Thomas Breyer, Ronny Kudwin</span></span><br /><span style="color: #34495e; font-size: 10pt;"> <strong>Prokura:</strong> Sybille Schoch<br /></span><br /><span style="color: #34495e; font-size: 10pt;"><strong>Handelsregister:</strong> Amtsgericht Stendal HRB-Nr. 114005</span><br /><span style="color: #34495e; font-size: 10pt;"> <strong>Umsatzsteuer-Identifikationsnummer:</strong> DE 230328668</span><br /></small> </span></h5>
               <hr />
               <p style="font-size: 8pt; font-family: Arial, sans-serif; color: #34495e;">Abh&auml;ngig vom Anlass Ihrer oder unserer Kontaktaufnahme werden Ihre personenbezogenen Daten von uns verarbeitet.<br />N&auml;here Informationen dazu erhalten Sie <a href="https://test.hu-ku.com/lva2020/wp-admin/[url_datenschutz]"> hier</a>.<br />Bitte beachten Sie auch, dass vertrauliche Informationen auf elektronischem<br />Wege nur verschl&uuml;sselt &uuml;bermittelt werden sollten.</p>`;
            template = [
                {
                    title: 'Vorlage E-Mail Signature',
                    description: '',
                    content: signatur1
                }
            ];
            break;
        case'send-email':
            let sendEmail1 = `<!-- [if (mso 16)]>
                 <style type="text/css">
                 a {text-decoration: none;}
                 </style>
                 <![endif]--><!-- [if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!-- [if gte mso 9]>
                 <xml>
                 <o:OfficeDocumentSettings>
                 <o:AllowPNG></o:AllowPNG>
                 <o:PixelsPerInch>96</o:PixelsPerInch>
                 </o:OfficeDocumentSettings>
                 </xml>
                 <![endif]--> <!-- [if gte mso 9]>
                 <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
                 <v:fill type="tile" color="#efefef"></v:fill>
                 </v:background>
                 <![endif]-->
                 <div class="es-wrapper-color" style="background-color: #efefef;">
                 <table class="es-wrapper" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; padding: 0; margin: 0; width: 100%; height: 100%; background-repeat: repeat; background-position: center top;" width="100%" cellspacing="0" cellpadding="0">
                 <tbody>
                 <tr style="border-collapse: collapse;">
                 <td style="padding: 0; margin: 0;" valign="top">
                 <table class="es-footer" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse; border-spacing: 0px; table-layout: fixed !important; width: 100%; background-color: transparent; background-repeat: repeat; background-position: center top;" cellspacing="0" cellpadding="0" align="center">
                 <tbody>
                 <tr style="border-collapse: collapse;">
                 <td style="padding: 0; margin: 0;" align="center">
                 <table class="es-footer-body" cellspacing="0px" cellpadding="0" align="center" style="border-collapse: collapse; width: 800px; height: 10px; background-color: #37763c;">
                 <tbody>
                 <tr style="border-collapse: collapse;">
                 <td style="margin: 0px; padding: 20px; width: 599px; height: 10px;" align="left">
                 <table style="border-collapse: collapse; width: 800px; height: 377px;">
                 <tbody>
                 <tr style="background-color: #fafafa;">
                 <td style="width: 100%; height: 377px;">
                 <table cellpadding="15" style="border-collapse: collapse; width: 99.5822%; margin-left: auto; margin-right: auto; height: 435px;">
                 <tbody>
                 <tr style="height: 385px;">
                 <td style="width: 100%; text-align: center; height: 385px;">
                 <table style="border-collapse: collapse; width: 99.6771%; height: 232px; margin-left: auto; margin-right: auto;">
                 <tbody>
                 <tr style="height: 52px;">
                 <td style="width: 100%; height: 10px; text-align: left;"><span style="color: #666666; font-family: arial, helvetica, sans-serif; font-size: 20px; text-align: center;">LVA Kundenschulungen</span><br style="color: #666666; font-family: arial, helvetica, sans-serif; font-size: 20px; text-align: center;" /><span style="font-size: 14px; font-family: arial,  helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;"><span style="font-size: 14px; font-family: arial, helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;">Digitales Schulungszentrum</span></span><span style="font-size: 14px; font-family: arial, helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;"><br /></span></td>
                 </tr>
                 <tr style="height: 212px;">
                 <td style="width: 100%; text-align: left; height: 212px;"><hr /><br />
                 <table style="height: 20px; width: 99.86%;">
                 <tbody>
                 <tr style="height: 25px;">
                 <td style="height: 10px; width: 100%; margin: 0px; padding: 0px;" align="left">
                 <p style="font-size: 18px; font-family: arial, helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 18px;">Überschrift für E-Mail...</p>
                 </td>
                 </tr>
                 <tr style="height: 37px;">
                 <td style="height: 10px; width: 100%; margin: 0px; padding: 15px 0px 0px; vertical-align: top;" align="left">
                 <p style="font-size: 14px; font-family: arial, helvetica, sans-serif; color: #666666; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; line-height: 21px; text-size-adjust: none;">
                 Hier Ihren <strong>E-Mail-Text</strong> eingeben...<br /><br />Mit freundlichen Gr&uuml;&szlig;en<br />Ihr LVA Schulungszentrum</p>
                 </td>
                 </tr>
                 </tbody>
                 </table>
                 </td>
                 </tr>
                 <tr style="height: 60px;">
                 <td style="width: 100%; text-align: left; height: 10px;"><br /><br />[signature]</td>
                 </tr>
                 </tbody>
                 </table>
                 </td>
                 </tr>
                 </tbody>
                 </table>
                 </td>
                 </tr>
                 </tbody>
                 </table>
                 </td>
                 </tr>
                 </tbody>
                 </table>
                 </td>
                 </tr>
                 </tbody>
                 </table>
                 </td>
                 </tr>
                 </tbody>
                 </table>
                 </div>`;
            template = [
                {
                    title: 'Vorlage E-Mail senden',
                    description: '',
                    content: sendEmail1
                }
            ];
            break;
        case'add':
            let zwei_column = `<div id="lva-bs-wrapper" class="custom-container">
            <div class="container-fluid tiny-container">
            <div class="row align-items-center CustomRow">
            <div class="t5554467 col-md-3 py-1 CustomCol">
            <!--<div class="delete-container"></div>-->
            <img class="img-fluid" src="${buchung_plugin_settings.plugin_url}/assets/images/group_img/blank-green.png" alt="" title="" width="273" height="247" style="display: block; margin-left: auto; margin-right: auto;"></div>
            <div class="t345678 col-md-9 py-1 CustomCol">
            <!--<div class="delete-container"></div>-->
            <span style="font-size: 16px;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
            invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et
            justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
            Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
            dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet
            clita kasd gubergren,
            </span></div>
            </div>
            <div class="t5554461 tiny-container">
            <div class="delete-container"></div>
            <p style="font-size: 18px;"><strong><br /><span style="color: #37763c;">Hauptschwerpunkte:</span></strong></p>
            <ul style="font-size: 16px;">
            <li>Lorem ipsum dolor sit amet</li>
            <li>Lorem ipsum dolor sit amet at</li>
            <li>Lorem ipsum dolor sit amet</li>
            <li>Lorem ipsum dolor sit amet diam</li>
            <li>Lorem ipsum dolor sit amet at de</li>
            <li>Lorem ipsum dolor sit amet, consetetur sadipscing</li>
            <li>Lorem ipsum dolor sit amet</li>
            <li>Lorem ipsum dolor sit</li>
            <li>Lorem ipsum</li>
            <li>Lorem ipsum dolor si eos</li>
            <li>Lorem ipsum dolor</li>
            <li>Lorem ipsum
            </li>
            </ul>
            </div>
            </div>
            </div>`;

            let drei_column = `<div id="lva-bs-wrapper" class="custom-container">
            <div class="container-fluid tiny-container">
            <div class="t555446q tiny-container my-4 py-1">
            <div class="delete-container"></div>
            <h4>Lorem ipsum</h4>
            <span style="font-size: 16px;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
                    invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et
                    justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                    Lorem ipsum dolor sit amet
            </span>
            </div>
            <div class="row align-items-center my-2 CustomRow">
            <div class="t5554467 col-md-3 py-1 CustomCol">
            <!--<div class="delete-container"></div>-->
            <img class="img-fluid" src="${buchung_plugin_settings.plugin_url}/assets/images/group_img/blank-green.png" alt="" title="" width="273" height="247" style="display: block; margin-left: auto; margin-right: auto;"></div>
            <div class="t345678 col-md-9 py-1 CustomCol">
            <!--<div class="delete-container"></div>-->
            <span style="font-size: 16px;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
                    invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et
                    justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
                    dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet
                    clita kasd gubergren,
            </span></div>
            </div>
            <div class="t5554461 tiny-container">
            <div class="delete-container"></div>
            <p style="font-size: 18px;"><strong><br /><span style="color: #37763c;">Hauptschwerpunkte:</span></strong></p>
            <ul style="font-size: 16px;">
                <li>Lorem ipsum dolor sit amet</li>
                <li>Lorem ipsum dolor sit amet at</li>
                <li>Lorem ipsum dolor sit amet</li>
                <li>Lorem ipsum dolor sit amet diam</li>
                <li>Lorem ipsum dolor sit amet at de</li>
                <li>Lorem ipsum dolor sit amet, consetetur sadipscing</li>
                <li>Lorem ipsum dolor sit amet</li>
                <li>Lorem ipsum dolor sit</li>
                <li>Lorem ipsum</li>
                <li>Lorem ipsum dolor si eos</li>
                <li>Lorem ipsum dolor</li>
                <li>Lorem ipsum
                </li>
                </ul>
                </div>
                </div>
                </div>`;

            let vier_column = `<div id="lva-bs-wrapper" class="custom-container">
                <div class="container-fluid tiny-container">
                <div class="t555446q tiny-container my-4 py-1">
                <div class="delete-container"></div>
                <h4>Lorem ipsum</h4>
                <span style="font-size: 16px;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
                    invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et
                    justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                    Lorem ipsum dolor sit amet
                </span>
                </div>
                <div class="row align-items-center my-2 CustomRow">
                <div class="t5554467 col-md-3 py-1 CustomCol">
                <!--<div class="delete-container"></div>-->
                <img class="img-fluid" src="${buchung_plugin_settings.plugin_url}/assets/images/group_img/blank-green.png" alt="" title="" width="273" height="247" style="display: block; margin-left: auto; margin-right: auto;"></div>
                <!--<div class="t345678 col-md-9 py-1 CustomCol">-->
                <div class="delete-container"></div>
                <span style="font-size: 16px;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
                    invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et
                    justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
                    dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet
                    clita kasd gubergren,
                </span></div>
                </div>
                <div class="t5554461 tiny-container">
                <div class="delete-container"></div>
                <p style="font-size: 18px;"><strong><br /><span style="color: #37763c;">Hauptschwerpunkte:</span></strong></p>
                <ul style="font-size: 16px;">
                <li>Lorem ipsum dolor sit amet</li>
                <li>Lorem ipsum dolor sit amet at</li>
                <li>Lorem ipsum dolor sit amet</li>
                <li>Lorem ipsum dolor sit amet diam</li>
                <li>Lorem ipsum dolor sit amet at de</li>
                <li>Lorem ipsum dolor sit amet, consetetur sadipscing</li>
                <li>Lorem ipsum dolor sit amet</li>
                <li>Lorem ipsum dolor sit</li>
                <li>Lorem ipsum</li>
                <li>Lorem ipsum dolor si eos</li>
                <li>Lorem ipsum dolor</li>
                <li>Lorem ipsum
                </li>
                </ul>
                </div>
                <div class="t555446P tiny-container my-4 py-1">
                <div class="delete-container"></div>
                <span style="font-size: 16px;">Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor
                    invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et
                    justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.
                    Lorem ipsum dolor sit amet
                </span>
                </div>
                </div>
                </div>`;

            template = [
                {
                    title: 'Vorlage auswählen...',
                    description: '',
                    content: ''
                },
                {
                    title: 'Gruppen Seite 2 Columns',
                    description: '',
                    content: zwei_column
                },
                {
                    title: 'Gruppen Seite 3 Columns',
                    description: '',
                    content: drei_column
                },
                {
                    title: 'Gruppen Seite 4 Columns',
                    description: '',
                    content: vier_column
                }
            ];
            break;
    }
    return template;
}

/*==================================================================================
========================== WORDPRESS IMAGE UPLOAD BUTTON ===========================
====================================================================================*/
const ImgDialogConfig = {
    title: 'Worpress Image auswahl',
    body: {
        type: 'panel',
        items: [
            {
                type: 'selectbox',
                name: 'imgSize',
                label: 'Image größe wählen',
                disabled: false,
                size: 1,
                items: [
                    {value: 'thumbnail', text: 'thumbnail'},
                    {value: 'medium', text: 'medium'},
                    // { value: 'large', text: 'large' },
                    {value: 'full', text: 'full'}
                ]
            },
            {
                type: 'htmlpanel',
                html: '<div style="min-height: .5rem"></div>'
            },
            {
                type: 'textarea',
                name: 'alt',
                label: 'Alt überschreiben: ',
                //placeholder: 'example',
                disabled: false,
                maximized: false
            },
            {
                type: 'htmlpanel',
                html: '<div style="min-height: .5rem"></div>'
            },
            {
                type: 'textarea',
                name: 'title',
                label: 'Titel überschreiben: ',
                //placeholder: 'example',
                disabled: false,
                maximized: false
            },
            {
                type: 'htmlpanel',
                html: '<div style="min-height: .5rem"></div>'
            },
            {
                type: 'checkbox',
                name: 'captCheck',
                label: 'Bildunterschrift anzeigen',
                disabled: false
            },
            {
                type: 'htmlpanel',
                html: '<div style="min-height: .5rem"></div>'
            },
            {
                type: 'input',
                name: 'caption',
                inputMode: 'text',
                label: 'Bildunterschrift überschreiben',
                //placeholder: 'example', // placeholder text for the input
                disabled: true,
                maximized: false
            },
            {
                type: 'htmlpanel',
                html: '<div style="min-height: .5rem"></div>'
            },
            {
                type: 'input',
                name: 'imgClass',
                inputMode: 'text',
                label: 'zuzätliche CSS class',
                //placeholder: 'example', // placeholder text for the input
                disabled: false,
                maximized: false
            },
            {
                type: 'htmlpanel',
                html: '<div style="min-height: .5rem"></div>'
            },
        ]
    },
    buttons: [
        {
            type: 'cancel',
            name: 'closeButton',
            text: 'Abbrechen'
        },
        {
            type: 'submit',
            id: 'imageBtn',
            text: 'zur Auswahl',
            tooltip: 'Wordpress Image-Upload.',
            //icon: 'image',
            primary: true,
            disabled: false,
        }
    ],
    initialData: {
        imgSize: 'medium',
        alt: '',
        title: '',
        caption: '',
        imgClass: '',
        captCheck: false,
    },

    onChange: function (dialogApi, details) {
        const data = dialogApi.getData();
        const toggle = data.captCheck ? dialogApi.enable : dialogApi.disable;
        toggle('caption');
    },

    onSubmit: function (api) {
        const data = api.getData();

        const frame = wp.media.frames.file_frame = wp.media({
            title: 'Wählen Sie ein Bild',
            button: {text: 'Ein Bild hinzufügen'},
            multiple: true,
        });
        frame.on('select', function () {

            const attachment = frame.state().get('selection').first().toJSON();
            let url = attachment.url;
            let alt = attachment.alt;
            let title = attachment.title;
            let imgClass = '';
            let caption = '';
            let CaptionStart = '';
            let CaptionEnd = '';
            let img = '';
            let width = '';
            let height = '';

            if (data.imgSize === 'medium') {
                url = attachment.url;
                width = attachment.width;
                height = attachment.height;
            }

            if (data.imgSize === 'thumbnail') {
                url = attachment.sizes.thumbnail.url;
                width = attachment.sizes.thumbnail.width;
                height = attachment.sizes.thumbnail.height;
            }
            if (data.imgSize === 'full') {
                url = attachment.sizes.full.url;
                width = attachment.sizes.full.width;
                height = attachment.sizes.full.height;
            }
            if (data.alt) {
                alt = data.alt;
            }
            if (data.title) {
                title = data.title;
            }
            if (data.imgClass) {
                imgClass = ` class="${data.imgClass}" `;
            }
            if (data.captCheck && data.caption) {
                caption = `<figcaption>${data.caption}</figcaption>`;
            }
            if (data.captCheck && !data.caption) {
                caption = `<figcaption>${attachment.caption}</figcaption>`;
            }
            if (data.captCheck) {
                CaptionStart = '<figure>';
                CaptionEnd = '</figure>';
            }
            //console.log(attachment);

            img += CaptionStart;
            img += `<img class="img-fluid ${imgClass}"  src="${url}" alt="${alt}" title="${title}" width="${width}" height="${height}"/>`;
            img += caption;
            img += CaptionEnd;
            tinymce.activeEditor.selection.setContent(img);
        });
        frame.open(api);
        //    tinymce.activeEditor.execCommand('mceInsertContent', false, '<p>My ' + pet +'\'s name is: <strong>' + data.catdata + '</strong></p>');
        api.close();
    }
};