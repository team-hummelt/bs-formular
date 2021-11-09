(function () {
    'use strict'
    let forms = document.querySelectorAll('.send-bs-formular.needs-validation');
    Array.prototype.slice.call(forms)
        .forEach(function (form) {

            let dscheck = form.querySelector('.dscheck');
            if (dscheck) {
                form.querySelector('button').classList.add('disabled');
            }

            form.addEventListener('submit', function (event) {
                let divBox = document.createElement("div");
                divBox.classList.add('add_repeat');
                let input = document.createElement("input");
                input.setAttribute('type', 'text');
                input.setAttribute('name', 'repeat_email');


                if (form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()

                    divBox.appendChild(input);
                    form.prepend(divBox);

                    send_xhr_bs_forms_data(form);
                }
                /*let inputMimes = document.querySelector("[name='accept_mimes']");
                if (inputMimes) {
                    inputMimes.remove();
                }*/

                let child = form.childNodes[0];
                if (child) {
                    if (child.className === 'add_repeat') {
                        child.remove();
                    }
                }
                event.preventDefault()
                form.classList.add('was-validated');
            }, false)
        })
})()

let closeAlert = document.querySelectorAll('.bs-form-alert');
if (closeAlert) {
    let alertNode = Array.prototype.slice.call(closeAlert, 0);
    alertNode.forEach(function (alertNode) {
        alertNode.addEventListener('click', function (event) {
            alertNode.classList.add('d-none');
        });
    });
}

let dsCheck = document.querySelectorAll('.dscheck input');
if (dsCheck) {
    let dsNode = Array.prototype.slice.call(dsCheck, 0);
    dsNode.forEach(function (dsNode) {
        dsNode.addEventListener('change', function (event) {
            dsNode.blur();
            let button = dsNode.form.querySelector('button');
            let errWrapper = dsNode.form.querySelector('.file-error-wrapper li');
            if (dsNode.checked && !errWrapper) {
                button.classList.remove('disabled');
            } else {
                button.classList.add('disabled');
            }
        });
    });
}


let showMessageTimeOut;
function validate_change_files(e) {

    e.blur();
    let mimes = e.getAttribute('accept');

    mimes = mimes.replace(/\./g, '');
    const mimeArray = mimes.split(',');

    let max_files = bs_form_ajax_obj.max_files;

    let errMsg = '';
    let curFiles = e.files;
    let newList;
    if (curFiles.length > max_files - 1) {
        newList = Array.from(curFiles);
        newList.splice(max_files, curFiles.length);
        e.files = FileListItems(newList);
    }

    let form = e.form;
    let button = form.querySelector('button');
    let dscheck = form.querySelector('.dscheck input');
    let dsChecked = true;
    let ulNodes = [];
    let random = '';
    let errorElement = e.nextElementSibling;
    let error = form.querySelectorAll('.file-feedback-wrapper');

    if (error) {
        if (error[0]) {
            // error[0].remove();
        }
        ulNodes.push(error);
        ulNodes.forEach(function (item) {
            if (item[0]) {
                item[0].remove();
            }
        });
    }

    let repeatBlind = form.querySelector('.add_repeat');
    if (repeatBlind) {
        repeatBlind.remove();
    }

    let repeatBlindBox = document.createElement("div");
    repeatBlindBox.classList.add('add_repeat');
    let repeatEmail = document.createElement("input");
    repeatEmail.setAttribute('type', 'text');
    repeatEmail.setAttribute('name', 'repeat_email');
    repeatBlindBox.appendChild(repeatEmail);
    form.prepend(repeatBlindBox);


    let input = document.createElement("input");
    input.setAttribute('type', 'hidden');
    input.setAttribute('name', 'accept_mimes');
    input.value = mimes;
    repeatBlindBox.appendChild(input);

    let errorWrapper = document.createElement('ul');
    errorWrapper.classList.add('file-feedback-wrapper');
    errorWrapper.classList.add('list-unstyled');

    errorElement.insertAdjacentElement('afterend', errorWrapper);


    if (dscheck) {
        dsChecked = dscheck.checked;
    }
    let succLi = '';
    if (curFiles.length !== 0) {
        let i = 1;
        let x = 0;
        let countSuccess = 0;
        for (const file of curFiles) {

            succLi = document.createElement('li');

            if (i > max_files) {
                succLi.classList.add('li-file-max-error');
                succLi.classList.add(`fileError`)
                errMsg = `<span class="d-block"> <b>Datei:</b> <i class="text-danger">${file.name}</i> | max. Anzahl <b>(${max_files})</b> pro E-Mail überschritten!</span>`;
                succLi.innerHTML = errMsg;
            }

            if (file.size > bs_form_ajax_obj.file_size) {
                succLi.classList.add('li-file-error-size');
                succLi.classList.add(`fileError`)
                errMsg = `<span class="d-block"> <b>Datei:</b> <i class="text-danger">${file.name}</i> zu groß! ( <b class="text-danger">${returnFileSize(file.size)}</b> ) <small> Dateigröße max: ${returnFileSize(bs_form_ajax_obj.file_size)}</small></span>`;
                succLi.innerHTML = errMsg;
            }

            let fileType = file.type.replace(/(.+\/)/g, '');
            if (!mimeArray.includes(fileType)) {
                succLi.classList.add('li-file-error');
                succLi.classList.add(`fileError`)
                errMsg = `<span class="d-block"> <b>Datei:</b> <i class="text-danger">${file.name}</i> ( <b class="text-danger">${fileType}</b> ) nicht zulässig!</span>`;
                succLi.innerHTML = errMsg;
            }

            if (errMsg) {
                errorWrapper.insertAdjacentElement('beforeend', succLi);
            }

            if (i <= max_files) {
                random = createBSRandomInteger(8);
                let out = `<span>${file.name}&nbsp; <i class="font-blue">
                           <small>(${returnFileSize(file.size)})</small></i>
                           </span>`;

                succLi.classList.add('li-add-file');
                succLi.classList.add('file' + random);
                succLi.innerHTML = out;
                errorWrapper.insertAdjacentElement('afterbegin', succLi);
                let wrapperLi = errorWrapper.querySelectorAll('.li-add-file');
                wrapperLi[0].classList.add('spin');

                let formData = new FormData();
                formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
                formData.append('action', 'BsFormularFileUploadNoAdmin');
                formData.append('file', file);
                formData.append('id', form.id.value);
                formData.append('formId', form.formId.value);
                formData.append('container_id', random);
                formData.append('accept_mimes', form.accept_mimes.value);
                formData.append('terms', form.terms.checked);
                formData.append('repeat_email', form.repeat_email.value);
                formData.append('upload_count', `${curFiles.length}`);
                formData.append('upload_number', `${i}`);
                formData.append('input_field', `${e.name}`);

                uploadCall(formData).then(data => {
                    let komplett = form.querySelector('.file' + data.container_id);
                    if (data.status) {
                        komplett.classList.remove('spin');
                        komplett.classList.add('li-success-file');
                        /*newList = Array.from(curFiles);
                        newList.splice(0, curFiles.length);
                        e.files = FileListItems(newList);*/
                        let filesId = document.querySelector("[name='files_id']");
                        if (!filesId) {
                            let fileInputId = document.createElement("input");
                            fileInputId.setAttribute('type', 'hidden');
                            fileInputId.setAttribute('name', 'files_id');
                            fileInputId.value = data.input_id;
                            form.insertAdjacentElement('afterbegin', fileInputId);
                        }

                        form.querySelector('.add_repeat').remove();
                        let fileError = document.querySelectorAll('.fileError');
                        if (fileError) {
                            let errNodes = Array.prototype.slice.call(fileError, 0);
                            errNodes.forEach(function (errNodes) {
                                errNodes.classList.add('removed');
                                setTimeout(function () {
                                    errNodes.remove();
                                }, 4000);
                            })
                        }

                    } else {
                        komplett.classList.remove('spin');

                    }
                }).catch(error => {
                    console.log(error);
                });
                // form.querySelector('.terms').remove();
                // form.querySelector('.add_repeat').remove();
            }
            i++;
        }

        if (errMsg) {

            button.classList.add('disabled');
        } else {
            // TODO JOB WARNING FILE UPLOAD

            if (dsChecked) {
                button.classList.remove('disabled');
            }
        }
    }
}

function FileListItems(files) {
    let b = new ClipboardEvent("").clipboardData || new DataTransfer()
    for (let i = 0, len = files.length; i < len; i++) b.items.add(files[i])
    return b.files
}

function returnFileSize(number) {

    if (number < 1024) {
        return number + 'bytes';
    } else if (number >= 1024 && number < 1048576) {
        return (number / 1024).toFixed(1) + 'KB';
    } else if (number >= 1048576) {
        return (number / 1048576).toFixed(1) + 'MB';
    }
}

const uploadFile = (formData) => {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();

        xhr.onload = () => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let data = JSON.parse(xhr.response);
                resolve(data);
            }
        };

        xhr.onerror = () => {
            console.error('Upload failed.');
            reject(error);
        }

        xhr.open('POST', bs_form_ajax_obj.ajax_url, true);
        xhr.send(formData);
    });
}

const uploadCall = async (data) => {
    return await uploadFile(data);
}

function send_xhr_bs_forms_data(data) {

    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    let input = new FormData(data);
    for (let [name, value] of input) {
        formData.append(name, value);
    }

    formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
    formData.append('action', 'BsFormularNoAdmin');

    xhr.open('POST', bs_form_ajax_obj.ajax_url, true);

    xhr.send(formData);
    //Response
    xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            let data = JSON.parse(this.responseText);
            let err = document.getElementById('error' + data.formId);
            let success = document.getElementById('success' + data.formId);
            let formParent = document.querySelectorAll(".send-bs-formular");
            let formInputName;
            let fileWrapper;

            if (data.status) {
                if (formParent) {
                    let formNode = Array.prototype.slice.call(formParent, 0);
                    formNode.forEach(function (formNode) {
                        formInputName = formNode.querySelector("[name='formId']");
                        if (formInputName.value == data.formId) {
                            fileWrapper = formNode.querySelector('.file-feedback-wrapper');
                            if (fileWrapper) {
                                fileWrapper.remove();
                            }
                        }
                    });
                }

                if (data.show_success) {
                    err.classList.add('d-none');
                    success.parentNode.firstChild.remove();
                    success.parentNode.classList.remove('was-validated');
                    success.parentNode.reset();
                    success.innerHTML = data.msg;
                    success.classList.remove('d-none');
                }
            } else {
                if (data.show_error) {
                    err.parentNode.firstChild.remove();
                    err.parentNode.classList.remove('was-validated');
                    err.parentNode.reset();
                    err.innerHTML = data.msg;
                    err.classList.remove('d-none');
                }
            }
        }
    }
}

/**=====================================
 ========== HELPER RANDOM KEY ===========
 ========================================
 */
function createBSRandomCode(length) {
    let randomCodes = '';
    let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
        randomCodes += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return randomCodes;
}

function createBSRandomInteger(length) {
    let randomCodes = '';
    let characters = '0123456789';
    let charactersLength = characters.length;
    for (let i = 0; i < length; i++) {
        randomCodes += characters.charAt(Math.floor(Math.random() * charactersLength));
    }
    return randomCodes;
}