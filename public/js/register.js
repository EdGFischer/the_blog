const formRegister = document.querySelector('#formRegister');
const inputRegister = document.querySelectorAll('#formRegister input');
const register = document.querySelector('#register');

register.addEventListener("click", e => {
    e.preventDefault();
    let obj = {};

    inputRegister.forEach(e => {
        obj[e.id] = e.value;
    });

    registerUser(obj);
});

async function registerUser(obj) {
    fetch('/newRegister', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(obj)
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            if (data.status == 'error') {
                console.log(data.message);
                alertify.notify(data.message, 'error', 7);
                validateForm(data.data, inputRegister);
            } else {
                alertify.notify(data.message, 'success', 7);
                validateForm(data.data, inputRegister);
                formRegister.reset();
                setTimeout(function () {
                    window.location.href = '/';
                }, 1000);
            }
        })
        .catch(function (error) {
            console.error('Erro:', error);
        });
}
