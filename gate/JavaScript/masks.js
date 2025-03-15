async function Login() {
    const name = document.getElementById("login-mask").children[1].value
    const pass = await sha512(document.getElementById("login-mask").children[2].value)

    _Connect.login(name, pass).then(login => {
        console.log(login)
    })
}