const postsContainer = document.querySelector("#home-tab-pane");

loadPosts(0);

async function followUser(userId) {
    fetch('/followUser', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ userId: userId })
    })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
        })
        .then(function () {
            location.reload();
        })
        .catch(function (error) {
            console.error('Erro:', error);
        });
}

async function unfollowUser(userId) {
    fetch('/unfollowUser', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ userId: userId })
    })
        .then(function (response) {
            console.log(response)
            if (!response.ok) {
                throw new Error('Erro na requisição: ' + response.status);
            }
        })
        .then(function () {
            console.log('teste')
            location.reload();
        })
        .catch(function (error) {
            console.error('Erro:', error);
        });
}

async function loadPosts(count) {

    var currentUrl = window.location.href;
    var urlParts = currentUrl.split("/");
    var username = urlParts[urlParts.length - 1];

    fetch('/loadPostsUser', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ count: count, username: username })
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('Erro na requisição: ' + response.status);
        }
        return response.json();
      })
      .then(function (data) {
        console.log(data)
        htmlPosts(data, postsContainer);
      })
      .catch(function (error) {
        console.error('Erro:', error);
      });
  }