MENUgenesis()
function MENUgenesis()
{
    _Home.style.display = "flex"
}

function MENUchange(content)
{
    switch (content) {
        case "home":
            MENUhideAll()
            _Home.style.display = "flex"
        break;

        case "litecoin":
            MENUhideAll()
            _LitecoinWallet.style.display = "flex"
        break;

        case "settings":
            MENUhideAll()
            _Settings.style.display = "flex"
        break;

        case "info-litecoin": 
            const linkLitecoin = document.createElement("a")
            linkLitecoin.href = "https://litecoin.com"
            linkLitecoin.target = "_blank"
            linkLitecoin.rel = "noopener noreferrer"
            linkLitecoin.click()
        break;

        case "info-kotia": 
            const linkKotia = document.createElement("a")
            linkKotia.href = "https://kotia.cash"
            linkKotia.target = "_blank"
            linkKotia.rel = "noopener noreferrer"
            linkKotia.click()
        break;

        case "info-devground": 
            
        break;
    
        default: break;
    }
}

function MENUhideAll()
{
    _Home.style.display = "none"
    _LitecoinWallet.style.display = "none"
    _Settings.style.display = "none"
}

function MENUlogged(type)
{
    let display = [
        ["block", "block", "block", "none", "none"], // Main
        ["block", "none", "none", "none", "none", "none", "none"], // Litecoin
        ["block", "none"] // Kotia
    ] 
    if (type == "in") display = [
        ["block", "none", "none", "block", "block"],
        ["block", "block", "none", "none", "none", "none", "none"], // Litecoin
        ["block", "none"], // Kotia
    ]
    // unlock DevArea with DiveToken

    /*for (let a = 0; a < _MENUmain.children.length; a++)
    {
        const element = _MENUmain.children[a]
        element.style.display = display[0][a]
    }*/

    for (let a = 0; a < display.length; a++)
    {
        const dropdown = _Menu.children[a].children[1]
        //console.log("Aloop", dropdown, display)

        for (let b = 0; b < display[a].length; b++)
        {
            const button = dropdown.children[b]
            //console.log("Bloop", button)
            button.style.display = display[a][b]
        }
    }    
}