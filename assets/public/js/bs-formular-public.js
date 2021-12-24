(function () {
    'use strict'
    let forms = document.querySelectorAll('.send-bs-formular.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {

            let dscheck = form.querySelector('.dscheck');
            if (dscheck) {
                form.querySelector('button').classList.add('disabled');
            }

            form.addEventListener('submit', function (event) {
                let showSending = form.querySelector('.bs-form-sending');

                this.blur();
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

                    showSending.classList.add('show-sending');
                    send_xhr_bs_forms_data(form);
                }

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

document.addEventListener("DOMContentLoaded", function (event) {
    if (bs_form_ajax_obj.bs_form_redirect_data['07107b9b03cb']) {
        let testDaten = bs_form_ajax_obj.bs_form_redirect_data['07107b9b03cb'];
      //  console.log(testDaten);
    }
});

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
    let ifFiles = '';
    dsNode.forEach(function (dsNode) {
        dsNode.addEventListener('change', function (event) {
            dsNode.blur();
            let button = dsNode.form.querySelector('button[type="submit"]');
            let filePondBtn = dsNode.form.querySelector('.filePondWrapper .invalid-feedback');

            if (dsNode.checked) {
                button.classList.remove('disabled');
            } else {
                button.classList.add('disabled');
            }

            let filepond = dsNode.form.querySelector('input.filepond--browser');
            let pondRoot = dsNode.form.querySelector('.bsFiles');
            if (filepond) {
                if (filepond.required) {
                    pondRoot.classList.add('fileError');
                    filePondBtn.style.display = 'block';
                    button.classList.add('disabled');
                } else {
                    pondRoot.classList.remove('fileError');
                    if (filePondBtn) {
                        filePondBtn.style.display = null;
                    }

                    if (dsNode.checked) {
                        button.classList.remove('disabled');
                    }
                }
            }

        });
    });
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
            let sendMsg = success.parentNode.querySelector('.bs-form-sending');
            sendMsg.classList.remove('show-sending');

            if (data.status) {

                if(data.redirect){
                    if(data.redirect_uri) {
                        location.href = data.redirect_uri;
                        return false;
                    }
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
                   // err.parentNode.firstChild.remove();
                   // err.parentNode.classList.remove('was-validated');
                   // err.parentNode.reset();

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