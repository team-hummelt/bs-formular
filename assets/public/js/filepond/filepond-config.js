document.addEventListener("DOMContentLoaded", function (event) {
    let bsFormFiles = document.querySelectorAll(".bsFiles");

    /*========================================
    ========== LOAD FILEPOND UPLOAD ==========
    ==========================================
    */
    function loadFilPondScript() {
        return new Promise(function (resolve, reject) {
            //create JS
            let script = document.createElement('script');
            script.type = 'text/javascript';
            script.src = bs_form_ajax_obj.assets_url + 'js/filepond/filepond.min.js';
            script.onload = () => resolve(script);
            script.onerror = () => reject(new Error(`Script load error for ${bs_form_ajax_obj.assets_url}`));
            //create CSS
            let cssFile = document.createElement('link');
            cssFile.rel = 'stylesheet';
            cssFile.type = 'text/css';
            cssFile.href = bs_form_ajax_obj.assets_url + 'css/filepond/filepond.min.css';

            document.head.appendChild(cssFile);
            document.body.appendChild(script);
        });
    }
    let i = 0;
    if (bsFormFiles) {
        let bsFileNode = Array.prototype.slice.call(bsFormFiles, 0);
        bsFileNode.forEach(function (bsFileNode) {
            if (typeof FilePond === 'undefined') {
                let pondBtn = document.createElement('button');
                pondBtn.classList.add('btn');
                pondBtn.classList.add('btn-outline-secondary');
                pondBtn.innerHTML = 'Datei auswählen';
                pondBtn.type = 'button';
                pondBtn.classList.add('filepond-browse-files')

                bsFileNode.insertAdjacentElement('afterend', pondBtn);

                let form = pondBtn.form;
                let formId = form.querySelector("[name='formId']");
                let form_id = form.querySelector("[name='id']");

                let loadFilePondScript = loadFilPondScript();
                loadFilePondScript.then(
                    script => {

                        const bsFilepond = FilePond.create(
                            bsFileNode,
                            {
                                maxFiles: bs_form_ajax_obj.max_files,
                                labelIdle: 'Datei hier per Drag & Drop ablegen.',
                                labelFileProcessingError: 'Fehler beim Upload',
                                labelTapToRetry: 'erneut versuchen',
                                labelTapToCancel: 'zum Abbrechen antippen',
                                Remove: 'entfernen',
                                credits: false,
                                maxParallelUploads: 2,
                                checkValidity: true,
                                itemInsertLocation:'before'
                            }
                        );

                        let pondRoot = pondBtn.form.querySelector('.filepond--root');

                        bsFilepond.setOptions({
                            server: {
                                revert: null,
                                process: (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                                    console.log(i)
                                    console.log(bsFilepond.getFiles())
                                    const formData = new FormData();
                                    formData.append(fieldName, file, file.name);
                                    let mimes = bsFileNode.getAttribute('accept');
                                    mimes = mimes.replace(/\./g, '');
                                    formData.append('input_field', bsFileNode.id);
                                    formData.append('formId', formId.value);
                                    formData.append('pond_id', bsFilepond.getFiles()[i].id);
                                    formData.append('id', form_id.value);
                                    formData.append('accept_mimes', mimes);
                                    formData.append('method', 'add_file');
                                    formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
                                    formData.append('action', 'BsFormularFileUploadNoAdmin');
                                    i++;

                                    const request = new XMLHttpRequest();
                                    request.open('POST', bs_form_ajax_obj.ajax_url);

                                    request.upload.onprogress = (e) => {
                                        progress(e.lengthComputable, e.loaded, e.total);
                                    };

                                    request.onload = function () {
                                        if (request.status >= 200 && request.status < 300) {
                                            let data = JSON.parse(request.responseText);
                                            if (data.status) {

                                                load(data.file_id);
                                            } else {
                                                error(data.msg);
                                                bsFilepond.labelTapToRetry = data.msg;
                                            }
                                        } else {
                                            error('oh no');
                                        }
                                    };

                                      request.send(formData);

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

                        pondBtn.addEventListener('click', () => {
                            this.blur();
                            bsFilepond.browse();
                        });

                        pondRoot.addEventListener('FilePond:processfiles', (e) => {
                            i=0;
                        });

                        pondRoot.addEventListener('FilePond:removefile', (e) => {
                            let xhr = new XMLHttpRequest();
                            let formData = new FormData();
                            xhr.open('POST', bs_form_ajax_obj.ajax_url, true);

                            formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
                            formData.append('action', 'BsFormularFileUploadNoAdmin');
                            formData.append('pond_id', e.detail.file.id);
                            formData.append('input_field', bsFileNode.id);
                            formData.append('formId', formId.value);
                            formData.append('id', form_id.value);
                            formData.append('method', 'delete_file');
                            //i--;
                            xhr.send(formData);
                             xhr.onreadystatechange = function () {
                                if (this.readyState === 4 && this.status === 200) {
                                    let data = JSON.parse(this.responseText);
                                    console.log(data);
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

