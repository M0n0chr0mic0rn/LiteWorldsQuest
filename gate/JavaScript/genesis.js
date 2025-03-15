const _IPFS = "https://ipfs.io/ipfs/"
const _Ordinal = "https://ordinalslite.com/content/"

const _Main = document.getElementById("Main")
const _Home = document.getElementById("Home")
const _Dashboard = document.getElementById("Dashboard")

const _Guard = document.getElementById("Guard")

const _Connect = new Connect()
const _User = new User()
const _Omnilite = new Omnilite()

var Terminal_hideout

const _AUTHKEY = localStorage.getItem("authkey")
if (_AUTHKEY != null) {
    _User.get(_AUTHKEY).then(response=>{
        if (response.bool) {
            _User.save(response.user)
            Terminal(response)
            _Dashboard.style.display = "flex"
            console.log(_User)
            updateUserData()
            MenuDisplay(true)
        }
    })
} else {
    _Home.style.display = "flex"
    MenuDisplay(false)
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
        console.log(element)

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

function updateTimestamps() {
    const now = Math.floor(Date.now() / 1000); // Aktuelle Unix-Zeit

    let timestamps = {
        createtime: _User.createtime,  
        faucetkotia: _User.faucetkotia, 
        faucetlitecoin: _User.faucetlitecoin, 
        lastaction: _User.lastaction
    };

    for (let key in timestamps) {
        let timestamp = timestamps[key];
        if (!timestamp) continue;

        let diff = timestamp - now;
        let element = document.getElementById(key);

        let newValue = diff < 0 ? formatTime(-diff) : formatTime(diff);
        element.style.color = diff > 0 ? "#ff4444" : "#00ff00"; // Farbe anpassen

        updateFlip(element, newValue);
    }
}

// Nur die geänderten Stellen flippen
function updateFlip(element, newValue) {
    let oldValue = element.textContent.trim();
    
    if (oldValue === newValue) return; // Keine Änderung → Kein Flip

    let newChars = newValue.split("");
    let oldChars = oldValue.split("");

    element.innerHTML = ""; // Inhalt leeren

    newChars.forEach((char, i) => {
        let span = document.createElement("span");
        span.textContent = char;
        
        if (oldChars[i] !== char) {
            span.classList.add("flip-active"); // Flip nur für geänderte Zeichen
            setTimeout(() => span.classList.remove("flip-active"), 200);
        }
        
        element.appendChild(span);
    });
}

// Countdown-Zeit formatieren
function formatTime(seconds) {
    let days = Math.floor(seconds / 86400);
    let hours = Math.floor((seconds % 86400) / 3600);
    let minutes = Math.floor((seconds % 3600) / 60);
    let secs = seconds % 60;
    return `${days}d ${hours}h ${minutes}m ${secs}s`;
}

// Startet das Update
setInterval(updateTimestamps, 1000);
updateTimestamps();


function updateUserData() {
    document.getElementById('name').textContent = _User.name;
    document.getElementById('language').textContent = _User.language;
    document.getElementById('pairingomnilite').textContent = _User.pairingomnilite;
}


