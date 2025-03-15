const _IPFS = "https://ipfs.io/ipfs/"
const _Ordinal = "https://ordinalslite.com/content/"

const _Main = document.getElementById("Main")
const _Home = document.getElementById("Home")

const _Guard = document.getElementById("Guard")

const _Connect = new Connect()
const _Omnilite = new Omnilite()


_Home.style.display = "flex"

function Menu(point) {
    for (let a = 0; a < _Main.children.length; a++) _Main.children[a].style.display = "none"
    document.getElementById(point).style.display = "flex"
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