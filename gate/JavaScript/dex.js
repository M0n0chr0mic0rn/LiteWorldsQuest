async function DEXgenesis()
{
    await DEXget()
    
    DEXprint("home")

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
    //console.log(url)
    const response = await fetch(url)
    DEX = await response.json()

    //console.log(DEX)
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

    if (confirm("This action will perform a transaction, plz only perform it once. A better solution will be added soon."))
    {
        const response = await (await fetch(url)).json()
        response.name = USER.name
        response.action = "ltcomni-token-list"
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