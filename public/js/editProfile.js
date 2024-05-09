const updateProfilePicture = document.querySelector("#updateProfilePicture");
const previewProfilePicture = document.querySelector("#previewProfilePicture");
const formUpdateProfilePicture = document.querySelector("#formUpdateProfilePicture");
const formUpdateUsername = document.querySelector("#formUpdateUsername");
const formUpdateUser = document.querySelector("#formUpdateUser");
const inputFormUpdateUsername = formUpdateUsername.querySelectorAll("input");
const inputFormUpdateUser = formUpdateUser.querySelectorAll("input");
let cropper;

formUpdateUsername.addEventListener("submit", (event) => {
    event.preventDefault();

    data = {};
    inputFormUpdateUsername.forEach(e => {
        data[e.name] = e.value;
    });
    updateUsername(data);
});

formUpdateUser.addEventListener("submit", (event) => {
    event.preventDefault();

    data = {};
    inputFormUpdateUser.forEach(e => {
        data[e.name] = e.value;
    });
    updateUser(data);
});

async function updateUsername(data) {
    fetch('/updateUsername', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
            return response.json();
        })
        .then(function (data) {
            if (data.status == 'error') {
                validateForm(data.data, inputFormUpdateUsername)
                alertify.notify(data.message, 'error', 7);
            } else {
                alertify.notify(data.message, 'success', 7);
            }
            setTimeout(function () {
                window.location.href = 'editProfile';
              }, 1000);
        })
        .catch(function (error) {
            console.error('Erro:', error);
        });
}

async function updateUser(data) {
    fetch('/updateUser', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function (response) {
        if (!response.ok) {
            throw new Error('Erro na requisição: ' + response.status);
        }
        return response.json();
    })
        .then(function (data) {
            console.log(data)
            if (data.status == 'error') {
                validateForm(data.data, inputFormUpdateUser)
                alertify.notify(data.message, 'error', 7);
            } else {
                alertify.notify(data.message, 'success', 7);
            }
        })
        .catch(function (error) {
            console.error('Erro:', error);
        });
}

updateProfilePicture.addEventListener("change", (event) => {
    const arquivo = event.target.files[0];

    if (arquivo && isValidImageFile(arquivo)) {
        const urlImagem = URL.createObjectURL(arquivo);
        previewProfilePicture.innerHTML = "";
        const newImage = document.createElement("img");
        newImage.id = "newProfilePicture";
        newImage.src = urlImagem;
        previewProfilePicture.appendChild(newImage);

        const image = document.getElementById("newProfilePicture");
        activeCropper(image, 4 / 4);
    } else {
        previewProfilePicture.innerHTML = "";
        alertify.notify("Por favor, selecione um arquivo de imagem válido.", 'error', 7);
        updateProfilePicture.value = "";
    }

    previousFile = arquivo;

});

formUpdateProfilePicture.addEventListener("submit", (event) => {
    event.preventDefault();
    if (!cropper || typeof cropper.getCroppedCanvas !== 'function') {
        alertify.notify("Selecione uma imagem", 'error', 7);
        return;
    }
    const userImage = cropper.getCroppedCanvas().toDataURL('image/jpeg');

    data = {
        userImage: userImage,
    };

    sendNewUserImage(data);

});


function sendNewUserImage(data) {
    fetch('/newUserImage', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Erro na requisição: ' + response.status);
        }
        return response.json();
      })
      .then(function (data) {
        if (data.status == 'error') {
          alertify.notify(data.message, 'error', 7);
          validateForm(data.data);
        } else {
          alertify.notify(data.message, 'success', 7);
          setTimeout(function () {
            window.location.href = window.location.href;
          }, 1500);
        }
      })
      .catch(function (error) {
        console.error('Erro:', error);
      });
  }
