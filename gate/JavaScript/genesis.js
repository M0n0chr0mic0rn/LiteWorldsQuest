const API = "https://liteworlds.quest/?method="
const ORDINAL = "https://ordinalslite.com/content/"
const IPFS = "https://ipfs.io/ipfs/"

const _Terminal = document.getElementById("terminal")
const _Menu = document.getElementById("menu")
const _Campfire = document.getElementById("campfire")

const _Home = document.getElementById("home")
const _HomeDEX = document.getElementById("home").children[0]
const _HomeTraderBot = document.getElementById("home").children[2]

const _Settings = document.getElementById("settings")
const _SettingsDisplay = document.getElementById("settings").children[0]
const _SettingsMenu = document.getElementById("settings").children[1]
const _SettingsInput = document.getElementById("settings").children[2]

const _LitecoinWallet = document.getElementById("litecoin-wallet")
const _LitecoinWalletLabels = document.getElementById("litecoin-wallet").children[0]
const _LitecoinWalletDetails = document.getElementById("litecoin-wallet").children[1]
const _LitecoinWalletOptions = document.getElementById("litecoin-wallet").children[2]

const _LitecoinOmniliteDEX = document.getElementById("litecoin-omnilite-dex")
const _LitecoinOmniliteDEXlist = document.getElementById("litecoin-omnilite-dex").children[0]

const _MASKsignUp = document.getElementById("MASKsignup")
const _MASKlogin = document.getElementById("MASKlogin")
const _MASKnewAddress = document.getElementById("MASKnewaddress")

var DEX = []
var DEXbyProperty = {}
var TRADERBOT

var USER
var WALLET = new Object()
var OMNI

var AUTHKEY = localStorage.getItem("AuthKey")

document.addEventListener('contextmenu', function(event)
{
    event.preventDefault()
})

document.getElementById("HEADlogo").onmouseover = function()
{
    _Campfire.style.display = "block"
}

document.getElementById("HEADlogo").onmouseleave = function()
{
    _Campfire.style.display = "none"
}

// Selektiere alle Buttons mit der Klasse 'collapseButton'
document.querySelectorAll('.collapseSingleButton').forEach(button => {
    button.addEventListener('click', function()
    {
        this.classList.add('collapsed') // Fügt die Klasse hinzu, um die Animation zu starten
        this.disabled = true // Deaktiviert den Button, um erneuten Klick zu verhindern
    })
})

document.getElementById("MASKsendltcfeerate").oninput = function()
{
    document.getElementById("MASKsendltcfeeratelabel").innerText = "Networkfee: " + document.getElementById("MASKsendltcfeerate").value + "lit/vbyte"
}

document.getElementById("MASKsendltcall").onchange = function()
{
    if (document.getElementById("MASKsendltcall").checked) document.getElementById("MASKsendltcamount").style.display = "none"
    else document.getElementById("MASKsendltcamount").style.display = "inline-block"
}

// Selektiere alle Buttons mit der Klasse 'collapseButton'
const collapseButtons = document.querySelectorAll('.collapseButtonGroup0')

collapseButtons.forEach(button => {
    button.addEventListener('click', function() {
        collapseButtons.forEach(btn => {
            btn.classList.add('collapsed') // Fügt die Klasse hinzu, um die Animation zu starten
            btn.disabled = true // Deaktiviert alle Buttons, um erneuten Klick zu verhindern

            setTimeout(() => {
                btn.style.opacity = 0
                btn.style.animation = "none"
            }, 500);
        })
    })
})

document.getElementById("2fatype").onchange = function()
{
    if (document.getElementById("2fatype").value == "email")
    {
        document.getElementById("regtelegram").style.display = "none"
        document.getElementById("regemail").style.display = "block"
    }

    if (document.getElementById("2fatype").value == "telegram")
    {
        document.getElementById("regemail").style.display = "none"
        document.getElementById("regtelegram").style.display = "block"
    }
}

// SHA-512 Converter
async function sha512(message)
{
    // encode as UTF-8
    const msgBuffer = new TextEncoder('utf-8').encode(message)
    // hash the message
    const hashBuffer = await crypto.subtle.digest('SHA-512', msgBuffer)
    // convert ArrayBuffer to Array
    const hashArray = Array.from(new Uint8Array(hashBuffer))
    // convert bytes to hex string
    return hashArray.map(b => ('00' + b.toString(16)).slice(-2)).join('')
}


function shuffle(array)
{
    let currentIndex = array.length;
  
    // While there remain elements to shuffle...
    while (currentIndex != 0) {
  
      // Pick a remaining element...
      let randomIndex = Math.floor(Math.random() * currentIndex);
      currentIndex--;
  
      // And swap it with the current element.
      [array[currentIndex], array[randomIndex]] = [
        array[randomIndex], array[currentIndex]];
    }
}