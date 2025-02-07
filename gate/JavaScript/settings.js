function SETTINGSgenesis()
{
    const headlines = []
    
    for (let a = 0; a < 3; a++)
    {
        headlines[a] = document.createElement("h2")
        headlines[a].classList.add("main-div-headline")
        _Settings.children[a].appendChild(headlines[a])
    }

    headlines[0].innerText = "Overview"
    headlines[1].innerText = "Options"
    headlines[2].innerText = "Inputs"

    SETTINGSoverview()
    SETTINGSoptions()
}

function SETTINGSoverview()
{
    const div = _Settings.children[0]
    const name = document.createElement("span")
    name.innerHTML = "Hey, " + USER.name + "<br> How are you today?<br>"

    const fauceteltc = document.createElement("span")
    const time = parseInt(Date.now() / 1000)
    console.log(USER.faucetlitecoin, time)

    if (USER.faucetlitecoin < time)
    {
        fauceteltc.innerHTML = "<br>Litecoin faucet ready"
        fauceteltc.style.color = "deepskyblue"
    }
    else
    {
        fauceteltc.innerHTML = "<br>Litecoin faucet ready in " + (time - USER.faucetlitecoin)
        fauceteltc.style.color = "crimson"
    }

    const faucetekot = document.createElement("span")
    console.log(USER.faucetkotia, time)

    if (USER.faucetkotia < time)
    {
        faucetekot.innerHTML = "<br>Kotia faucet ready"
        faucetekot.style.color = "deepskyblue"
    }
    else
    {
        faucetekot.innerHTML = "<br>Kotia faucet ready in " + (time - USER.faucetkotia)
        faucetekot.style.color = "crimson"
    }

    div.appendChild(name)
    div.appendChild(fauceteltc)
    div.appendChild(faucetekot)
}

function SETTINGSoptions()
{
    const buttons = _Settings.children[1]
    const display = _Settings.children[2]

    const changePW = document.createElement("button")
    changePW.innerText = "Change Password"
    changePW.classList.add("ButtonBlue")
    changePW.onclick = function()
    {
        SETTINGSinputs(display)

        const label1 = document.createElement("label")
        const input1 = document.createElement("input")
        const label2 = document.createElement("label")
        const input2 = document.createElement("input")
        const submit = document.createElement("button")

        label1.innerText = "new Password"
        label2.innerText = "repeat Password"
        submit.innerText = "Change Password"

        input1.type = "password"
        input2.type = "password"

        submit.classList.add("ButtonRed")

        label1.appendChild(input1)
        display.appendChild(label1)
        label2.appendChild(input2)
        display.appendChild(label2)
        display.appendChild(submit)

        submit.onclick = async function()
        {
            if (input1.value == input2.value)
            {
                const pass = await sha512(input1.value)
                const url = API + "update&key=pass&authkey=" + AUTHKEY + "&value=" + pass

                console.log(url)

                const r = await (await fetch(url)).json()
                r.name = USER.name
                r.action = "_update"
                Terminal(r)
            }
            else
            {
                alert("Passwords dont match, plz doublecheck")
            }
        }
    }

    const change2fa = document.createElement("button")
    change2fa.innerText = "Change Signature Method"
    change2fa.classList.add("ButtonBlue")
    change2fa.onclick = function()
    {
        SETTINGSinputs(display)

        const select = document.createElement("select")
        const telegram = document.createElement("option")
        const email = document.createElement("option")
        const submit = document.createElement("button")

        telegram.innerText = "Telegram"
        telegram.value = "telegram"

        email.innerText = "Email"
        email.value = "email"

        submit.innerHTML = "Change Signature Method"
        submit.classList.add("ButtonRed")
        submit.onclick = async function()
        {
            const url = API + "update&key=security&value=" + select.value + "&authkey=" + AUTHKEY
            console.log(url)

            const r = await (await fetch(url)).json()
            r.name = USER.name
            r.action = "_update"
            Terminal(r)
        }
        
        select.appendChild(telegram)
        select.appendChild(email)
        display.appendChild(select)
        display.appendChild(submit)
    }

    const changetelegram = document.createElement("button")
    changetelegram.innerText = "Change Telegram"
    changetelegram.classList.add("ButtonBlue")
    changetelegram.onclick = function()
    {
        SETTINGSinputs(display)

        const handle = document.createElement("input")
        const label = document.createElement("label")
        const submit = document.createElement("button")

        label.innerText = "Telegram handle"

        submit.innerText = "Change Telegram"
        submit.classList.add("ButtonRed")
        submit.onclick = async function()
        {
            const url = API + "update&key=telegram&value=" + handle.value + "&authkey=" + AUTHKEY
            console.log(url)

            const r = await (await fetch(url)).json()
            r.name = USER.name
            r.action = "_update"
            Terminal(r)
        }

        label.appendChild(handle)
        display.appendChild(label)
        display.appendChild(submit)
    }

    const changemail = document.createElement("button")
    changemail.innerText = "Change Email"
    changemail.classList.add("ButtonBlue")
    changemail.onclick = function()
    {
        SETTINGSinputs(display)

        const email = document.createElement("input")
        const label = document.createElement("label")
        const submit = document.createElement("button")

        email.type = "email"
        label.innerText = "Email"
        
        submit.innerText = "Change Email"
        submit.classList.add("ButtonRed")
        submit.onclick = async function()
        {
            const url = API + "update&key=email&value=" + email.value + "&authkey=" + AUTHKEY
            console.log(url)

            const r = await (await fetch(url)).json()
            r.name = USER.name
            r.action = "_update"
            Terminal(r)
        }

        label.appendChild(email)
        display.appendChild(label)
        display.appendChild(submit)
    }


    buttons.appendChild(changePW)
    buttons.appendChild(changetelegram)
    buttons.appendChild(changemail)
    buttons.appendChild(change2fa)
    
}

function SETTINGSinputs(display)
{
    display.innerHTML = ""
    const headline = document.createElement("h2")
    headline.classList.add("main-div-headline")
    headline.innerText = "Inputs"
    display.appendChild(headline)
}