document.addEventListener("DOMContentLoaded", function (event) {
    let bsFormFiles = document.querySelectorAll(".bsFiles");

    /*========================================
    ========== LOAD FILEPOND UPLOAD ==========
    ==========================================
    */
    function loadFilPondScript() {
        return new Promise(function (resolve, reject) {
            // Create FilePond Plugin Validate Size
            let fileSizeScript = document.createElement('script');
            fileSizeScript.type = 'text/javascript';
            fileSizeScript.src = bs_form_ajax_obj.assets_url + 'js/filepond/filepond-plugin-file-validate-size.min.js';
            // Create FilePond Plugin Validate Type
            let fileSizeType = document.createElement('script');
            fileSizeType.type = 'text/javascript';
            fileSizeType.src = bs_form_ajax_obj.assets_url + 'js/filepond/filepond-plugin-file-validate-type.min.js';
            //create FilePond JS
            let script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = bs_form_ajax_obj.assets_url + 'js/filepond/filepond.min.js';
            script.onload = () => resolve(script);
            script.onerror = () => reject(new Error(`Script load error for ${bs_form_ajax_obj.assets_url}`));
            //create FilePond CSS
            let cssFile = document.createElement('link');
            cssFile.rel = 'stylesheet';
            cssFile.type = 'text/css';
            cssFile.href = bs_form_ajax_obj.assets_url + 'css/filepond/filepond.min.css';

            //FilePond
            document.head.appendChild(cssFile);
            //Plugin Validate Size
            document.body.appendChild(fileSizeScript);
            //Plugin Validate Type
            document.body.appendChild(fileSizeType);
            //FilePond
            document.body.appendChild(script);
        });
    }

    if (bsFormFiles) {
        let bsFileNode = Array.prototype.slice.call(bsFormFiles, 0);
        bsFileNode.forEach(function (bsFileNode) {

            if (typeof FilePond === 'undefined') {
                let lang = bs_form_ajax_obj.language;
                let pondBtn = document.createElement('button');
                pondBtn.classList.add('btn');
                pondBtn.classList.add('btn-outline-secondary');
                pondBtn.classList.add('btn-file-upload');
                pondBtn.classList.add('filepond-browse-files')
                pondBtn.innerHTML = lang.datei_select; //'Datei auswählen';
                pondBtn.type = 'button';
                bsFileNode.parentNode.insertAdjacentElement('beforeend', pondBtn);

                let form = pondBtn.form;
                let formId = form.querySelector("[name='formId']");
                let form_id = form.querySelector("[name='id']");
                let loadFilePondScript = loadFilPondScript();
                let button = form.querySelector('button[type="submit"]');
                let pondInit = 1;

                let mimes = bsFileNode.getAttribute('accept');
                let sendMimes = mimes.replace(/\./g, '');
                const mimeArray = mimes.split(',');

                loadFilePondScript.then(
                    script => {
                        FilePond.registerPlugin(
                            FilePondPluginFileValidateSize,
                            FilePondPluginFileValidateType
                        );

                        const bsFilepond = FilePond.create(
                            bsFileNode,
                            {
                                //maxFiles: bs_form_ajax_obj.max_files,
                                labelIdle: lang.drag_file, //'Datei hier per Drag & Drop ablegen.',
                                labelFileProcessingError: lang.upload_err, //'Fehler beim Upload',
                                labelTapToRetry:  lang.erneut_vers,  //'erneut versuchen',
                                labelTapToCancel: lang.tap_cancel, //'zum Abbrechen antippen',
                                labelTapToUndo: lang.click_delete, //'zum Löschen klicken',
                                Remove: lang.remove, //'entfernen',
                                credits: false,
                                maxParallelUploads: 2,
                                //instantUpload:null,
                                //required:true,
                                //checkValidity:true,
                                //allowProcess :  false,
                                //forceRevert:true,
                                //itemInsertLocation:'before',

                                /*======================= FileSize Plugin =======================*/
                                allowFileSizeValidation: true,
                                minFileSize: null,
                                maxFileSize: bs_form_ajax_obj.file_size_mb+'MB',
                                maxTotalFileSize: bs_form_ajax_obj.file_size_all_mb+'MB',
                                labelMaxFileSizeExceeded: lang.file_large, //'Datei ist zu groß',
                                labelMaxFileSize: lang.max_filesize, //'Maximale Dateigröße ist {filesize}',
                                labelMaxTotalFileSizeExceeded: lang.max_total_size, //'Maximale Gesamtgröße überschritten',
                                labelMaxTotalFileSize: lang.max_total_file,//'Maximale Gesamtgröße der Datei ist {filesize}',

                                /*======================= FileType Plugin =======================*/
                                allowFileTypeValidation: true,
                                acceptedFileTypes: mimeArray,
                                fileValidateTypeDetectType: (source, type) =>
                                    new Promise((resolve, reject) => {
                                        resolve(type);
                                    }).then(resolve => {
                                        let typePos = resolve.indexOf("/") + 1;
                                        let sendType = resolve.substring(typePos);
                                        if(mimeArray.includes('.'+sendType)) {
                                            return '.'+sendType;
                                        } else {
                                            return null;
                                        }
                                    }),
                                labelFileTypeNotAllowed : lang.invalid_type, //'Ungültiger Dateityp',
                                fileValidateTypeLabelExpectedTypes : lang.expects,//'Erwartet {allButLastType} oder {lastType}',
                                fileValidateTypeLabelExpectedTypesMap : {},
                            }
                        );

                        let pondRoot = pondBtn.form.querySelector('.filepond--root');

                        bsFilepond.setOptions({
                            server: {
                                revert: null,
                                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                                    const formData = new FormData();
                                    formData.append(fieldName, file, file.name);

                                    formData.append('input_field', bsFileNode.id);
                                    formData.append('formId', formId.value);
                                    formData.append('id', form_id.value);
                                    formData.append('firstUpload', pondInit);
                                    formData.append('accept_mimes', sendMimes);
                                    formData.append('method', 'add_file');
                                    formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
                                    formData.append('action', 'BsFormularFileUploadNoAdmin');
                                    const request = new XMLHttpRequest();
                                    request.open('POST', bs_form_ajax_obj.ajax_url);
                                    request.upload.onprogress = (e) => {
                                        progress(e.lengthComputable, e.loaded, e.total);
                                    };

                                    request.onload = function () {
                                        if (request.status >= 200 && request.status < 300) {
                                            let data = JSON.parse(request.responseText);
                                            if (data.status) {
                                                pondRoot.classList.remove('fileError');
                                                let errMsg = pondRoot.parentElement.querySelector('.invalid-feedback');
                                                button = form.querySelector('button[type="submit"]');
                                                let dsCheck = form.querySelector('.dscheck input');
                                                if(errMsg){
                                                    errMsg.style.display = null;
                                                }
                                                if (dsCheck.checked) {
                                                    button.classList.remove('disabled');
                                                }
                                                load(data.file_id);
                                            } else {
                                                error(data.msg);
                                                bsFilepond.labelTapToRetry = data.msg;
                                                //bsFilepond.labelFileProcessingError = data.msg;
                                            }
                                        } else {
                                            error('oh no');
                                        }
                                    };

                                    request.send(formData);
                                    pondInit = 0;
                                    // Should expose an abort method so the request can be cancelled
                                    return {
                                        abort: () => {
                                            // This function is entered if the user has tapped the cancel button
                                            request.abort();

                                            // Let FilePond know the request has been cancelled
                                            abort();
                                        }
                                    };
                                }
                            },
                        });

                        /*================================ EVENTS ============================*/

                        form.addEventListener('reset', (e) => {
                            bsFilepond.removeFiles();
                        });

                        pondBtn.addEventListener('click', () => {
                            this.blur();
                            bsFilepond.browse();
                        });

                        pondRoot.addEventListener('FilePond:removefile', (e) => {
                            let xhr = new XMLHttpRequest();
                            let formData = new FormData();
                            xhr.open('POST', bs_form_ajax_obj.ajax_url, true);
                            formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
                            formData.append('action', 'BsFormularFileUploadNoAdmin');
                            formData.append('file_name', e.detail.file.filename);
                            formData.append('input_field', bsFileNode.id);
                            formData.append('formId', formId.value);
                            formData.append('id', form_id.value);
                            formData.append('method', 'delete_file');
                            xhr.send(formData);
                            xhr.onreadystatechange = function () {
                                if (this.readyState === 4 && this.status === 200) {
                                    let data = JSON.parse(this.responseText);
                                    if (data.status) {

                                    }
                                }
                            }
                        });

                        pondRoot.addEventListener('FilePond:processfilerevert', (e) => {
                            let xhr = new XMLHttpRequest();
                            let formData = new FormData();
                            xhr.open('POST', bs_form_ajax_obj.ajax_url, true);
                            formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
                            formData.append('action', 'BsFormularFileUploadNoAdmin');
                            formData.append('file_name', e.detail.file.filename);
                            formData.append('input_field', bsFileNode.id);
                            formData.append('formId', formId.value);
                            formData.append('id', form_id.value);
                            formData.append('method', 'delete_file');
                            xhr.send(formData);
                            xhr.onreadystatechange = function () {
                                if (this.readyState === 4 && this.status === 200) {
                                    let data = JSON.parse(this.responseText);
                                    if (data.status) {

                                    }
                                }
                            }
                        });
                    },
                    error => console.log(`Error: ${error.message}`)
                );
            }
        });
    }
});
