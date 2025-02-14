function Mask(which)
{
    const mask = document.getElementById("MASK" + which)

    if (mask.style.display == "block") mask.style.display = "none"
    else mask.style.display = "block"
}

function TerminalEasy(content)
{
    const terminal = document.getElementById('terminal')
    const terminalcontent = document.getElementById('terminal-content')
    terminalcontent.innerHTML = "<p>contacting server...</p>"
    
    // Toggle die Klasse 'open' für das Terminal, um es zu öffnen oder zu schließen
    terminal.classList.toggle('open')

    // Neuen Inhalt einfügen
    for (let a = 0; a < content["response"].length; a++)
    {
        const element = document.createElement("p")
        element.innerText = "..." + content["response"][a]
        terminalcontent.appendChild(element)
    }

    if (content.hasOwnProperty("error"))
    {
        const element = document.createElement("p")
        element.innerText = "Error! " + content["error"]
        terminalcontent.appendChild(element)
    }

    setTimeout(() => {
        terminal.classList.toggle('open')
    }, 5000)
}

// Funktion, um das Terminal zu öffnen oder zu schließen
function Terminal(content)
{
    const terminal = document.getElementById('terminal')
    const terminalcontent = document.getElementById('terminal-content')
    terminalcontent.innerHTML = "<p>contacting server...</p>"
    
    // Toggle die Klasse 'open' für das Terminal, um es zu öffnen oder zu schließen
    terminal.classList.toggle('open')

    // Neuen Inhalt einfügen
    for (let a = 0; a < content["response"].length; a++)
    {
        const element = document.createElement("p")
        element.innerText = "..." + content["response"][a]
        terminalcontent.appendChild(element)
    }

    if (content.hasOwnProperty("error"))
    {
        const element = document.createElement("p")
        element.innerText = "Error! " + content["error"]
        terminalcontent.appendChild(element)

        setTimeout(() => {
            terminal.classList.toggle('open')
        }, 5000)
    }
    else
    {
        const element = document.createElement("p")
        element.innerText = "Success! Connetion closed..."
        terminalcontent.appendChild(element)

        const endline = document.createElement("p")
        terminalcontent.appendChild(endline)
        endline.innerText = "waiting for signature"

        terminalcontent.scrollTop = terminalcontent.scrollHeight

        const url = API + "progress&name=" + content.name + "&action=" + content.action
        console.log(url)
        let time = 60*3

        if (content.action == "register") time = 60*5

        const myinterval = setInterval(async function() {
            const data = await (await fetch(url)).json()

            if (data.bool)
            {
                clearInterval(myinterval)
                endline.innerText = "waiting for signature " + time
                const timeinterval = setInterval(async function(){
                    time--
                    if (time <= 0)
                    {
                        clearInterval(timeinterval)
                        clearInterval(myinterval)
                        endline.innerText = "time expired"

                        setTimeout(() => {
                            terminal.classList.toggle('open')
                        }, 5000)
                    }
                    else
                    {
                        endline.innerText = "waiting for signature " + time

                        const data1 = await (await fetch(url)).json()
                        if (!data1.bool)
                        {
                            endline.innerText = "action signed"
                            clearInterval(timeinterval)

                            setTimeout(() => {
                                terminal.classList.toggle('open')
                            }, 5000)

                            if (content.hasOwnProperty("authkey"))
                            {
                                localStorage.setItem("AuthKey", content.authkey)
                                location.reload()
                            }

                            if (content.hasOwnProperty("action"))
                            {
                                if (content.action == "register")
                                {
                                    Mask("login")
                                }
                            }
                        }
                    }
                }, 1000)
            }
            else
            {
                endline.innerText = "action not found"
                clearInterval(myinterval)

                setTimeout(() => {
                    terminal.classList.toggle('open')
                }, 5000)
            }
        }, 2000)
    }
}

function MASKsendltc(address)
{
    document.getElementById("MASKsendltcorigin").innerText = address

    const mask = document.getElementById("MASKsendltc")

    if (mask.style.display == "block") mask.style.display = "none"
    else mask.style.display = "block"
}