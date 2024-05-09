function isValidImageFile(arquivo) {
  return arquivo && arquivo.type && (arquivo.type.startsWith('image/jpeg') || arquivo.type.startsWith('image/jpg') || arquivo.type.startsWith('image/png'));
}

function debounce(func, delay) {
  let timeoutId;
  return function () {
    const args = arguments;
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => func.apply(this, args), delay);
  };
}

function activeCropper(image, prop) {

  cropper = new Cropper(image, {
    viewMode: 1,
    minCropBoxHeight: 100,
    maxCropBoxHeight: 1200,
    aspectRatio: prop,
  });
}

async function likePost(postId, element) {
  element.disabled = true;

  fetch('/likePost', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ postId: postId })
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('Erro na requisição: ' + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      element.classList.toggle('likedPost');
      element.classList.toggle('notLikedPost');
      const isLiked = element.classList.contains('likedPost');
      element.onclick = isLiked ? function () { unlikePost(postId, element); } : function () { likePost(postId, element); };
      element.disabled = false;
    })
    .catch(function (error) {
      element.disabled = false;
      console.error('Erro:', error);
    });
}

async function unlikePost(postId, element) {
  element.disabled = true;

  fetch('/unlikePost', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ postId: postId })
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('Erro na requisição: ' + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      element.classList.toggle('likedPost');
      element.classList.toggle('notLikedPost');
      const isLiked = element.classList.contains('likedPost');
      element.onclick = isLiked ? function () { unlikePost(postId, element); } : function () { likePost(postId, element); };
      element.disabled = false;
    })
    .catch(function (error) {
      element.disabled = false;
      console.error('Erro:', error);
    });
}

async function deletePost(postId) {

  fetch('/deletePost', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ postId: postId })
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error('Erro na requisição: ' + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      alertify.warning("Postagem deletada");
      location.reload();
    })
    .catch(function (error) {
      console.error('Erro:', error);
    });

}

async function sendComment(postId, element) {
  element.disabled = true;
  let commentContentElement = document.querySelector(`#commentContent${postId}`);
  let commentContent = commentContentElement.value;

  if (commentContent.length <= 0) {
    alertify.notify("Escreva o comentário antes de enviar", 'error', 7);
    element.disabled = false;
    return;
  }

  const obj = {
    'postId': postId,
    'commentContent': commentContent
  };
  fetch('/sendComment', {
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

      element.disabled = false;
      updateComment(postId);
    })
    .catch(function (error) {
      element.disabled = false;
      console.error('Erro:', error);
    });
}

async function updateComment(postId) {

  const obj = {
    'postId': postId
  };

  fetch('/listPostComment', {
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
      console.log(data)
      comments = htmlComment(data.comments, data.sessionId);

      const postCommentsContainer = document.querySelector(`.postCommentsContainer${postId}`);
      postCommentsContainer.innerHTML = comments;
    })
    .catch(function (error) {
      console.error('Erro:', error);
    });
}

async function deleteComment(commentId) {

  const obj = {
    'commentId': commentId
  };

  fetch('/deleteComment', {
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
      alertify.warning("Comentário deletado");
      commentContainer = document.querySelector(`#commentContainer${commentId}`);
      if (commentContainer) {
        commentContainer.remove();
      }
      updateComment(postId)
    })
    .catch(function (error) {
      console.error('Erro:', error);
    });
}

async function validateForm(data, element) {
  element.forEach(input => {
    if (data.length == 0) {
      input.classList.remove('is-invalid');
      input.classList.remove('is-valid');
    } else if (data.includes(input.id)) {
      input.classList.add('is-invalid');
      input.classList.remove('is-valid');
    } else {
      input.classList.add('is-valid');
      input.classList.remove('is-invalid');
    }
  });
}

function htmlPosts(data, postsContainer) {

  data.forEach(post => {
    const postDiv = document.createElement("div");
    postDiv.classList.add("divPost", "w-100", "mb-3");

    const postContentDiv = document.createElement("div");
    postContentDiv.classList.add("d-flex", "flex-column");

    let profileImage = '../data/images/noProfilePic.jpeg';
    if (post.user_image) {
      profileImage = '../data/images/' + post.user_id + '/profile/' + post.user_image;
    }
    commentHTML = htmlComment(post.comments, post.sessionId);

    postContentDiv.innerHTML = `
          <div class="w-100 mb-3 d-flex justify-content-between align-items-center">
              <a href="/profile/${post.username}" class="dataPost text-decoration-none">
                  <img class="avatarPost" src="${profileImage}">
                  <strong>${post.name}</strong>
              </a>
              <small>
                  <time dateTime='${post.publish_date}'>
                      ${moment(post.publish_date, "YYYY-MM-DDTHH:mm:ss").locale("pt-br").fromNow()}
                  </time>
              </small>
              
          </div>
          <div class="d-flex">
              <a href="data/images/${post.user_id}/${post.image_path}" data-lightbox="image-2" data-title="My caption" class="col-6">
                  <img class="imgPost" src="../data/images/${post.user_id}/${post.image_path}">
              </a>
              <p class="textPost col-6">${post.post_content}</p>
          </div>
          <div class="border-bottom mb-2 ">
              <div class="py-2 d-flex buttonPots">
                  <button onclick="${post.has_liked == 0 ? 'likePost(' + post.post_id + ', this)' : 'unlikePost(' + post.post_id + ', this)'}" class="${post.has_liked == 0 ? 'notLikedPost' : 'likedPost'}">
                      <i class="bi bi-hand-thumbs-up"></i>
                  </button>
                  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                      <i class="bi bi-chat"></i>
                  </button>
                  ${post.sessionId == post.user_id ? `
                    <button type="button" onclick="confirmPostDelete(${post.post_id})">
                    <i class="bi bi-trash"></i>
                    </button>` : ``}
              </div>
              <div class="collapse containerNewComment" id="collapseExample">
                  <small class="">
                      <form class="mb-2 d-flex flex-column">
                          <label for="commentContent${post.post_id}">Seu Comentário:</label>
                          <textarea id="commentContent${post.post_id}" name="commentContent${post.post_id}" class="w-100" style="max-height: 10rem; min-height: 5rem"></textarea>
                          <div>
                              <button type="button" class="btn" onclick="${'sendComment(' + post.post_id + ', this)'}">Enviar</button>
                          </div>
                      </form>
                  </small>
              </div>
          </div>
          <div class="w-100 postCommentsContainer${post.post_id}">
            ${commentHTML}
          </div>
          `;

    postDiv.appendChild(postContentDiv);
    postsContainer.appendChild(postDiv);
  });
}

function htmlComment(comments, sessionId = null) {

  let commentHTML = '';

  comments.forEach(comment => {
    console.log(sessionId, comment.user_id);
    commentHTML += `
        <small class="w-100 mt-2 commentDiv" id="commentContainer${comment.comment_id}">
            <div class="w-100 d-flex justify-content-between flex-nowrap commentHeader">
                <a href="/profile/${comment.username}" target="_blank">
                    ${comment.name}
                </a>
                <div>
                    ${sessionId == comment.user_id ? `<button class="btn text-white bg-transparent px-1 py-0" onClick="deleteComment(${comment.comment_id})"><i class=" bi bi-trash"></i></button>` : ``}
                    <time dateTime='${comment.comment_date}'>
                      Publicado há ${moment(comment.comment_date).locale("pt-br").fromNow()}
                  </time>
                </div>
                
            </div>
            <div class="w-100 d-flex postComment">
                <p>${comment.comment_content}</p>
            </div>
        </small>`;
  });

  returnComment =
    `<div class="w-100 text-center">
      ${comments.length > 0 ? "<strong class='text-center'>Comentários</strong>" : ""}
    </div>
    <div class="w-100 d-flex flex-column text-center">
      ${commentHTML}
    </div>`;
  return returnComment;
}

function confirmPostDelete(postId) {
  alertify.confirm("Você realmente deseja deletar a postagem?",
    function () {
      deletePost(postId);
    },
    function () {
    });
} 
