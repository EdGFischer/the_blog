const formLogin = document.querySelector('#formLogin');
const inputLogin = document.querySelectorAll('#formLogin input');
const btnLogin = document.querySelector('#btnLogin');

btnLogin.addEventListener("click", e => {
    e.preventDefault();
    let obj = {}

    inputLogin.forEach(e => {
        obj[e.id] = e.value
    })

    fetch('/authenticate', {
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
                alertify.notify(data.message, 'error', 7)
                validateForm(data.data, inputLogin)
            } else {
                window.location.href = '/home';
            }
        })
        .catch(function (error) {
            console.error('Erro:', error);
        });
})