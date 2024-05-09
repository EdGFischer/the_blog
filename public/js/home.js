const inputUploadNewImage = document.querySelector("#uploadNewPostImage");
const imageNewPostArea = document.querySelector("#imageNewPostArea");
const formSendNewPost = document.querySelector("#formNewPost");
const postsHome = document.querySelector("#postsHome");
const postsContainer = document.querySelector("#postsContainer");
let cropper;

loadPosts(0);

inputUploadNewImage.addEventListener("change", (event) => {
  const arquivo = event.target.files[0];

  if (arquivo && isValidImageFile(arquivo)) {
    const urlImagem = URL.createObjectURL(arquivo);
    imageNewPostArea.innerHTML = "";
    const imageNewPost = document.createElement("img");
    imageNewPost.id = "previewImageNewpost";
    imageNewPost.src = urlImagem;
    imageNewPostArea.appendChild(imageNewPost);

    const image = document.getElementById("previewImageNewpost");
    activeCropper(image, 4 / 3);
  } else {
    imageNewPostArea.innerHTML = "";
    alertify.notify("Por favor, selecione um arquivo de imagem válido.", 'error', 7);
    inputUploadNewImage.value = "";
  }

  previousFile = arquivo;

});

formSendNewPost.addEventListener("submit", (event) => {
  event.preventDefault();
  if (!cropper || typeof cropper.getCroppedCanvas !== 'function') {
    alertify.notify("Erro: Selecione uma imagem", 'error', 7);
    return;
  }

  const textPost = formNewPost.querySelector('#textNewPost').value;
  const imagePost = cropper.getCroppedCanvas().toDataURL('image/jpeg');

  data = {
    postContent: textPost,
    imagePost: imagePost,
  };

  sendNewPost(data);

});

async function loadPosts(count) {
  fetch('/loadPostsHome', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ count: count })
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('Erro na requisição: ' + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      htmlPosts(data, postsContainer);
    })
    .catch(function (error) {
      console.error('Erro:', error);
    });
}

async function sendNewPost(data) {
  fetch('/newPost', {
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
          window.location.href = 'home';
        }, 1500);
      }
    })
    .catch(function (error) {
      console.error('Erro:', error);
    });
}