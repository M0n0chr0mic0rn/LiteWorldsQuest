function TRADERBOThomeview()
{
    const tb = TRADERBOT
    const array = []

    shuffle(tb)

    for (let a = 0; a < 12; a++)
    {
        shuffle(tb[a].tokens)
        array[a] = tb[a].propertyid + "#" + tb[a].tokens[0].tokenstart
    }

    return array
}
async function TRADERBOTprintHome(field)
{
    await Promise.all(field.map(async(task) => 
    {
        //console.log(task)
        const a = task.split("#")
        TRADERBOTcreateCard(a[0], a[1])
        
    }))
    /*for (let a = 0; a < 12; a++)
    {
        TRADERBOTcreateCard(Object.keys(field)[a], Object.values(field)[a])
    }*/
}
async function TRADERBOTgenesis()
{
    const url = API + "ltc-trader-get"
    TRADERBOT = await (await fetch(url)).json()

    //console.log(TRADERBOT)

    const homeview = TRADERBOThomeview()
    //console.log(homeview)


    TRADERBOTprintHome(homeview)
}

async function TRADERBOTcreateCard(property, token)
{
    const seal = property + "#" + token
    const url = API + "ltc-nft-get&property=" + property+ "&token=" + token
    const nft = (await (await fetch(url)).json())[0]
    const card = document.createElement("div")
    card.classList.add("nft-card")
    _HomeTraderBot.appendChild(card)

    nft.grantdata = nft.grantdata.replaceAll("{'", "{\"")
    nft.grantdata = nft.grantdata.replaceAll("'}", "\"}")
    nft.grantdata = nft.grantdata.replaceAll("':'", "\":\"")
    nft.grantdata = nft.grantdata.replaceAll("','", "\",\"")

    try {
        const grantdata = JSON.parse(nft.grantdata)
        const holderdata = JSON.parse(nft.holderdata)

        if (grantdata.hasOwnProperty("structure")) // LiteWorlds
        {
            TRADERBOTliteworlds(card, seal, grantdata, holderdata)
        }
        else if (grantdata.hasOwnProperty("image")) // LiteVerse
        {
            TRADERBOTliteverse(card, seal, grantdata, holderdata)
        }
        else // Custom
        {
            
        }
    } catch (error) {
        console.log("TRADERBOTcraeteCARD: JSON decode Error")
    }
    
}

async function TRADERBOTliteworlds(card, seal, grantdata, holderdata)
{
    const card_image = document.createElement("div")
    const image = document.createElement("img")
    const audio = document.createElement("audio")
    const video = document.createElement("video")
    const source_wav = document.createElement("source")
    const source_mp3 = document.createElement("source")
    const source_mp4 = document.createElement("source")

    const card_detail = document.createElement("div")
    const sealspan = document.createElement("span")
    const name = document.createElement("span")
    const description = document.createElement("span")
    const desire = document.createElement("span")

    card.appendChild(card_image)
    card_image.appendChild(image)

    card.appendChild(card_detail)
    card_detail.appendChild(sealspan)
    card_detail.appendChild(document.createElement("br"))
    card_detail.appendChild(name)
    card_detail.appendChild(document.createElement("br"))
    card_detail.appendChild(description)
    card_detail.appendChild(document.createElement("br"))
    card_detail.appendChild(desire)

    audio.controls = true
    audio.style.width = "95%"

    video.controls = true
    video.style.width = "95%"
    video.style.height = "95%"

    source_wav.type = "audio/wav"
    source_mp3.type = "audio/mp3"
    source_mp4.type = "video/mp4"

    card_image.classList.add("nft-image")
    
    image.alt = "Loading..."
    image.style.display = "inline-block"

    card_detail.classList.add("nft-details")
    sealspan.classList.add("nft-seal")
    desire.classList.add("desire")

    sealspan.innerText = seal
    sealspan.style.color = "deepskyblue"

    name.innerText = grantdata.name
    if (grantdata.hasOwnProperty("description")) description.innerText = grantdata.description
    desire.innerText = holderdata.desire + " LTC"

    let url
    let blob
    let blobURL

    if (grantdata.structure == "epic")
    {
        if (grantdata.source == "ordinal")
        {
            url = ORDINAL + grantdata.content
            
        }

        if (grantdata.source == "ipfs")
        {
            url = IPFS + grantdata.content
        }
    }

    if (grantdata.structure == "artefactual" || grantdata.structure == "artifactual")
    {
        url = ORDINAL + grantdata.json
        json = await (await fetch(url)).json()
        console.log("ARTEFACT", json)

        sealspan.style.color = "crimson"

        name.innerText = json.data.name
        if (json.data.hasOwnProperty("description")) description.innerText = json.data.description

        url = ORDINAL + json.content[0]
    }

    blob = await (await fetch(url)).blob()
    blobURL = URL.createObjectURL(blob)

    if (blob.type.includes("image"))
    {
        image.src = blobURL

        image.onload = function()
        {
            URL.revokeObjectURL(blobURL)
        }
    }
    if (blob.type.includes("audio") || blob.type.includes("video"))
    {
        image.remove()

        source_mp3.src = blobURL
        source_mp4.src = blobURL
        source_wav.src = blobURL

        if (blob.type.includes("mp3")) video.appendChild(source_mp3)
        if (blob.type.includes("mp4")) video.appendChild(source_mp4)
        if (blob.type.includes("wav")) video.appendChild(source_wav)

        card_image.appendChild(video)
        video.load()

        video.oncanplaythrough = function()
        {
            URL.revokeObjectURL(blobURL)
        }
    }
}

async function TRADERBOTliteverse(card, seal, grantdata, holderdata)
{
    const url = IPFS + grantdata.image.split("ipfs://")[1]
    const blob = await (await fetch(url)).blob()
    const blobURL = URL.createObjectURL(blob)
    const card_image = document.createElement("div")
    const image = document.createElement("img")
    const audio = document.createElement("audio")
    const video = document.createElement("video")
    const source_wav = document.createElement("source")
    const source_mp3 = document.createElement("source")
    const source_mp4 = document.createElement("source")

    audio.controls = true
    audio.style.width = "95%"

    video.controls = true
    video.style.width = "95%"
    video.style.height = "95%"

    source_wav.type = "audio/wav"
    source_mp3.type = "audio/mp3"
    source_mp4.type = "video/mp4"


    card_image.classList.add("nft-image")
    card.appendChild(card_image)
    card_image.appendChild(image)
    image.alt = "Loading..."
    image.style.display = "inline-block"

    const card_detail = document.createElement("div")
    const sealspan = document.createElement("span")
    const name = document.createElement("span")
    const description = document.createElement("span")
    const desire = document.createElement("span")

    card_detail.classList.add("nft-details")
    sealspan.classList.add("nft-seal")
    desire.classList.add("desire")

    card.appendChild(card_detail)
    card_detail.appendChild(sealspan)
    card_detail.appendChild(document.createElement("br"))
    card_detail.appendChild(name)
    card_detail.appendChild(document.createElement("br"))
    card_detail.appendChild(description)
    card_detail.appendChild(document.createElement("br"))
    card_detail.appendChild(desire)

    sealspan.innerText = seal
    sealspan.style.color = "gold"

    name.innerText = grantdata.name
    description.innerText = grantdata.description
    desire.innerText = holderdata.desire + " LTC"

    if (blob.type.includes("image"))
    {
        image.src = blobURL

        image.onload = function()
        {
            URL.revokeObjectURL(blobURL)
        }
    }
    if (blob.type.includes("audio"))
    {
        image.remove()

        source_mp3.src = blobURL
        source_wav.src = blobURL

        if (blob.type.includes("mp3")) audio.appendChild(source_mp3)
        if (blob.type.includes("wav")) audio.appendChild(source_wav)

        card_image.appendChild(audio)
        audio.load()

        audio.oncanplaythrough = function()
        {
            URL.revokeObjectURL(blobURL)
        }
    }
}