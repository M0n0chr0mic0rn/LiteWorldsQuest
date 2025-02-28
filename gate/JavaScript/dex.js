async function DEXgenesis()
{
    await DEXget()
    
    DEXprint("home")
    DEXprintFull()
    

    // Füge den Tooltip automatisch hinzu, wenn der Text abgeschnitten wird
    document.querySelectorAll('.truncate-tooltip').forEach(function (element) {
        // Prüfen, ob der Text abgeschnitten wird
        if (element.scrollWidth > element.clientWidth) {
            // Setze den vollen Text als Tooltip
            element.setAttribute('title', element.innerText)
        }
    })
}

async function DEXget()
{
    const url = API + "ltc-dex-get"
    const response = await (await fetch(url)).json()
    DEX = response

    for (let a = 0; a < response.length; a++)
    {
        const position = response[a]

        if (DEXbyProperty.hasOwnProperty(position.propertyid))
        {
            DEXbyProperty[position.propertyid][position.seller] = position
        }
        else
        {
            DEXbyProperty[position.propertyid] = {}
            DEXbyProperty[position.propertyid][position.seller] = position
        }   
    }
}

async function DEXlist()
{
    const origin = document.getElementById("MASKdexListorigin").innerText
    const propertyid = document.getElementById("MASKdexListtoken").innerText
    const amount = document.getElementById("MASKdexListamount").value
    const desire = document.getElementById("MASKdexListdesire").value

    console.log(propertyid)

    const url = API + "ltcomni-token-list&authkey=" + AUTHKEY + "&origin=" + origin + "&token=" + propertyid + "&amount=" + amount + "&desire=" + desire
    console.log(url)

    if (confirm("WARNING!\nEXPERIMENTAL STATE!\nThis action will perform a transaction.\n Only perform it once and wait till your transaction is confirmed, then refresh your Wallet.\n A better solution will be added soon."))
    {
        const response = await (await fetch(url)).json()
        response.name = USER.name
        response.action = "ltcomni-dex"
        Terminal(response)
    }
}

async function DEXcancel()
{
    const origin = document.getElementById("MASKdexListorigin").innerText
    const propertyid = document.getElementById("MASKdexListtoken").innerText

    console.log(propertyid)

    const url = API + "ltcomni-token-cancel&authkey=" + AUTHKEY + "&origin=" + origin + "&token=" + propertyid
    console.log(url)

    if (confirm("WARNING!\nEXPERIMENTAL STATE!\nThis action will perform a transaction.\n Only perform it once and wait till your transaction is confirmed, then refresh your Wallet.\n A better solution will be added soon."))
    {
        const response = await (await fetch(url)).json()
        response.name = USER.name
        response.action = "ltcomni-dex"
        Terminal(response)
    }
}

async function DEXrequest()
{
    const origin = document.getElementById("MASKdexRequestorigin").innerText
    const propertyid = document.getElementById("MASKdexRequesttoken").innerText
    const amount = document.getElementById("MASKdexRequestamount").value
    const destination = document.getElementById("MASKdexRequestdestination").innerText

    const url = API + "ltcomni-token-request&authkey=" + AUTHKEY + "&origin=" + origin + "&token=" + propertyid + "&amount=" + amount + "&destination=" + destination
    console.log(url)

    if (confirm("WARNING!\nEXPERIMENTAL STATE!\nThis action will perform a transaction.\n Only perform it once and wait till your transaction is confirmed!\nAfter a second Transaktion will be released to complete your purchase\n A better solution will be added soon."))
    {
        const response = await (await fetch(url)).json()
        response.name = USER.name
        response.action = "ltcomni-dex"
        Terminal(response)
    }
}

async function DEXprint(where)
{
    switch (where) {
        case "home":
            const d = DEX
            shuffle(d)

            for (let a = 0; a < 11; a++)
            {
                DEXprintHome(d[a])
            }

            /*await Promise.all(DEX.map(async(task) => 
            {
                console.log(printed)
                if (printed < end)
                {
                    DEXprintHome(task)
                    printed++
                }
            }))*/
        break;
    
        default:
            break;
    }
}

async function DEXprintFull()
{
    console.log(DEXbyProperty)

    Object.values(DEXbyProperty).forEach(async function(listing, DEXindex, arr)
    {
        const div = document.createElement("div")
        _LitecoinOmniliteDEXlist.appendChild(div)

        const span = document.createElement("span")
        _LitecoinOmniliteDEXlist.appendChild(span)
        span.innerText = "PropertyID: " + Object.keys(DEXbyProperty)[DEXindex]
        span.onclick = async function()
        {
            _LitecoinOmniliteView.innerHTML = ""

            const property = await OMNILITEgetProperty(Object.keys(DEXbyProperty)[DEXindex])
            span.innerText = property.name

            Object.values(listing).forEach(async function(position, index, arr)
            {
                console.log(position)
                Object.values(position).forEach(async function(item, index, arr)
                {
                    if (Object.keys(position)[index] == "seller" || Object.keys(position)[index] == "amountavailable" || Object.keys(position)[index] == "litecoindesired")
                    {
                        const div = document.createElement("div")
                        _LitecoinOmniliteView.appendChild(div)

                        const span = document.createElement("span")
                        _LitecoinOmniliteView.appendChild(span)
                        span.innerText = Object.keys(position)[index] + ": " + item

                        if (Object.keys(position)[index] == "amountavailable")
                        {
                            span.onclick = async function()
                            {
                                _LitecoinOmniliteOptions.innerHTML = ""

                                const button = document.createElement("button")
                                _LitecoinOmniliteOptions.appendChild(button)
                                button.classList.add("ButtonRed")
                                button.innerText = "Request"
                                button.onclick = async function()
                                {
                                    console.log(WALLET)
                                    document.getElementById("MASKdexRequestorigin").innerText = Object.keys(WALLET.litecoin["default"])[0]
                                    document.getElementById("MASKdexRequesttoken").innerText = Object.keys(DEXbyProperty)[DEXindex]
                                    document.getElementById("MASKdexRequestname").innerText = property.name
                                    document.getElementById("MASKdexRequestdestination").innerText = position["seller"]

                                    Mask("dexRequest")
                                }
                            }
                        }
                    }
                    
                })
            })

            Object.values(property).forEach(async function(item, index, arr)
            {
                const div = document.createElement("div")
                _LitecoinOmniliteView.appendChild(div)

                const span = document.createElement("span")
                _LitecoinOmniliteView.appendChild(span)
                span.innerText = Object.keys(property)[index] + ": " + item
            })
        }

    })
}

async function DEXprintHome(position)
{
    //console.log(position)

    const url = API + "ltc-property-get&property=" + position.propertyid
    const property = await (await fetch(url)).json()
    //console.log(property)

    const name = property.name

    let available = parseFloat(position.amountavailable)
    let total = parseFloat(property.totaltokens)

    if (position.amountavailable >= 1000) available = parseFloat((parseFloat(position.amountavailable) / 1000).toFixed(2)) + "K"
    if (position.amountavailable >= 1000000) available = parseFloat((parseFloat(position.amountavailable) / 1000000).toFixed(2)) + "M"
    if (position.amountavailable >= 1000000000) available = parseFloat((parseFloat(position.amountavailable) / 1000000000).toFixed(2)) + "B"
    if (position.amountavailable >= 1000000000000) available = parseFloat((parseFloat(position.amountavailable) / 1000000000000).toFixed(2)) + "T"

    if (property.totaltokens >= 1000) total = parseFloat((parseFloat(property.totaltokens) / 1000).toFixed(2)) + "K"
    if (property.totaltokens >= 1000000) total = parseFloat((parseFloat(property.totaltokens) / 1000000).toFixed(2)) + "M"
    if (property.totaltokens >= 1000000000) total = parseFloat((parseFloat(property.totaltokens) / 1000000000).toFixed(2)) + "B"
    if (property.totaltokens >= 1000000000000) total = parseFloat((parseFloat(property.totaltokens) / 1000000000000).toFixed(2)) + "T"

    const amount = available + "/" + total
    
    const desire = "@" + (parseFloat(position.litecoindesired) / parseFloat(position.amountavailable)).toFixed(8) + " LTC"

    const div = document.createElement("div")
    div.classList.add("position-container")
    _HomeDEX.appendChild(div)

    const fill = document.createElement("div")
    fill.classList.add("fillbar")
    div.appendChild(fill)

    const logo = document.createElement("img")
    var data = false
    var addLogo = false

    try {
        data = JSON.parse(property.data)
    } catch (error) {
        
    }

    if (data && data.hasOwnProperty("structure"))
    {
        if (data.structure == "epic" && data.source == "ipfs")
        {
            logo.src = IPFS + data.content
            addLogo = true
        }
    }

    if (addLogo) div.appendChild(logo)

    
    //logo.src = IPFS + property.data.

    //console.log(data)

    const spanName = document.createElement("span")
    const spanAmount = document.createElement("span")
    const spanDesire = document.createElement("span")

    spanName.classList.add("truncate-tooltip")
    spanAmount.classList.add("truncate-tooltip")
    spanDesire.classList.add("truncate-tooltip")
    
    div.appendChild(spanName)
    div.appendChild(spanAmount)
    div.appendChild(spanDesire)

    spanName.style.width = "80%"
    spanName.innerText = name
    spanAmount.innerText = amount
    spanDesire.innerText = desire

    fill.style.width = "0%"

    let targetWidth = parseFloat(position.amountavailable) / parseFloat(property.totaltokens) * 100

    setTimeout(() => {
        fill.style.width = `${targetWidth}%`; // Setze die gewünschte Breite
    }, 10); // Ein kurzer Delay (10ms), damit der Browser die Initialisierung der width erkennt
}