async function changePassword() {
    const pass = await sha512(document.getElementById("new-password").value)
    const pass1 = await sha512(document.getElementById("confirm-password").value)

    if (pass == pass1) {
        _User.update(_AUTHKEY, "pass", pass).then(update=>{
            console.log(update)
            Terminal(update)
        })
    } else {
        alert("Passwords are not matching")
    }
}
function changeEmail() {
    const email = document.getElementById("new-email").value

    _User.update(_AUTHKEY, "email", email).then(update=>{
        console.log(update)
        Terminal(update)
    })
}
function changeTelegram() {
    const telegram = document.getElementById("new-telegram").value.replaceAll("@", "")

    _User.update(_AUTHKEY, "telegram", telegram).then(update=>{
        console.log(update)
        Terminal(update)
    })
}
function changeSecurity() {
    const security = document.getElementById("new-security").value

    _User.update(_AUTHKEY, "security", security).then(update=>{
        console.log(update)
        Terminal(update)
    })
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

function formatTime(seconds) {
    let years = Math.floor(seconds / 31557600); // 365.25 Tage
    let days = Math.floor((seconds % 31557600) / 86400);
    let hours = Math.floor((seconds % 86400) / 3600);
    let minutes = Math.floor((seconds % 3600) / 60);
    let secs = seconds % 60;

    let parts = [];
    if (years) parts.push(`${years}y`);
    if (days) parts.push(`${days}d`);
    if (hours) parts.push(`${hours}h`);
    if (minutes) parts.push(`${minutes}m`);
    if (secs || parts.length === 0) parts.push(`${secs}s`); // Falls alles 0 ist, zumindest die Sekunden anzeigen

    return parts.join(" ");
}

// Startet das Update
setInterval(updateTimestamps, 1000);
updateTimestamps();


function updateUserData() {
    document.getElementById('name').textContent = _User.name;
    document.getElementById('language').textContent = _User.language;
    document.getElementById('security').textContent = _User.security;
    document.getElementById('pairingomnilite').textContent = _User.pairingomnilite;
}