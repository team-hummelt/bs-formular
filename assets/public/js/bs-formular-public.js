(function () {
    'use strict'
    let forms = document.querySelectorAll('.send-bs-formular.needs-validation');
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
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

function send_xhr_bs_forms_data(data) {
    let xhr = new XMLHttpRequest();
    let formData = new FormData();
    xhr.open('POST', bs_form_ajax_obj.ajax_url, true);

    let input = new FormData(data);
    for (let [name, value] of input) {
        formData.append(name, value);
    }

    formData.append('_ajax_nonce', bs_form_ajax_obj.nonce);
    formData.append('action', 'BsFormularNoAdmin');
    xhr.send(formData);
    //Response
    xhr.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            let data = JSON.parse(this.responseText);
            let err = document.getElementById('error' + data.formId);
            let success = document.getElementById('success' + data.formId);
            if (data.status) {
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