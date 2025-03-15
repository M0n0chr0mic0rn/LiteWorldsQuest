function Mask(action) {
    switch (action) {
        case "Login":
            const Mask = document.getElementById("Mask-login")
            if (Mask.style.display == "block") Mask.style.display = "none"
            else Mask.style.display = "block"
        break;
    
        default:
            break;
    }
}

async function Login() {
    const name = document.getElementById("Mask-login").children[1].value
    const pass = await sha512(document.getElementById("Mask-login").children[2].value)

    _Connect.login(name, pass).then(login => {
        Terminal(login)
    })
}