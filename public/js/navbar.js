const inputSearchUsers = document.querySelector("#searchUsers input");

inputSearchUsers.addEventListener("input", debounce((event) => {
    const name = event.target.value;
    searchUsers(name);
}, 500));

document.addEventListener('click', function (event) {
    const usersList = document.getElementById('usersList');

    if (!inputSearchUsers.contains(event.target) && !usersList.contains(event.target)) {
        usersList.style.display = 'none';
    }
});

async function searchUsers(data) {
    data = {
        username: data,
        name: data
    };

    console.log(data);

    fetch('/searchUsers', {
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
        .then(function (users) {
            updateDropdownUsers(users);
        })
        .catch(function (error) {
            console.error('Erro:', error);
        });
}


function updateDropdownUsers(users) {
    const userListElement = document.getElementById('usersList');
    userListElement.style.display = 'block';

    userListElement.innerHTML = '';

    users.forEach(function (user) {
        const li = document.createElement('li');
        const a = document.createElement('a');

        a.href = '/profile/' + user.username;
        a.textContent = user.name;

        li.appendChild(a);
        userListElement.appendChild(li);
    });


    userListElement.style.display = users.length > 0 ? 'block' : 'none';
}

