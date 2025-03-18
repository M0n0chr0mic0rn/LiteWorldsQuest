const _IPFS = "https://ipfs.io/ipfs/"
const _Ordinal = "https://ordinalslite.com/content/"

const _Main = document.getElementById("Main")
const _Home = document.getElementById("Home")
const _Dashboard = document.getElementById("Dashboard")

const _Guard = document.getElementById("Guard")

const _Connect = new Connect()
const _User = new User()
const _Omnilite = new Omnilite()

Menu("Home")
MenuDisplay(false)

const _AUTHKEY = localStorage.getItem("authkey")
if (_AUTHKEY != null) {
    _User.get(_AUTHKEY).then(response=>{
        if (response.bool) {
            _User.save(response.user)
            Menu("Dashboard")
            console.log(_User)
            updateUserData()
            MenuDisplay(true)
            Terminal(response)
        }
    })
}

function Menu(point) {
    for (let a = 0; a < _Main.children.length; a++) _Main.children[a].style.display = "none"
    document.getElementById(point).style.display = "flex"
}

function MenuDisplay(logged) {
    let keymap
    if (logged) {
        keymap = [[
            true, true, false, false, true
        ],[
            true, true, true
        ],[
            true, true, true, true
        ],[
            true, true, true, true
        ]]
    } else {
        keymap = [[
            true, false, true, true, true
        ],[
            false, false, false
        ],[
            false, false, false, false
        ],[
            true, true, true, true
        ]]
    }

    const navbar = document.getElementsByClassName("navbar")[0]

    for (let index = 0; index < 4; index++) {
        const element = navbar.children[index].children[1]

        for (let a = 0; a < element.children.length; a++) {
            const option = element.children[a]
            
            if (keymap[index][a]) option.style.display = "inline-block"
            else option.style.display = "none"
        }
    }

    navbar.children[3].style.display = "none"
}

function ClearCache() {
    window.open(location.href, "_self");
}

async function sha512(text) {
    const buffer = new TextEncoder().encode(text);
    const hashBuffer = await crypto.subtle.digest("SHA-512", buffer);
    return Array.from(new Uint8Array(hashBuffer))
        .map(b => b.toString(16).padStart(2, "0"))
        .join("");
}

document.addEventListener('contextmenu', function(event)
{
    event.preventDefault()
})

document.getElementById("Headline").onmouseenter = () => {
    _Guard.style.display = "block"
}
document.getElementById("Headline").onmouseleave = () => {
    _Guard.style.display = "none"
}



