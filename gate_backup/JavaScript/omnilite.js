async function OMNILITEget(address)
{
    const container = document.createElement("div")
    _LitecoinWalletDetails.appendChild(container)
    container.style.height = "50%"

    const headline = document.createElement("h2")
    container.appendChild(headline)
    headline.innerText = "Omnilite"

    OMNILITEtokens(address, container)
}

async function OMNILITEtokens(address, container)
{
    const url = API + "ltcomni-get-balance-address&address=" + address
    let tokens

    const headline = document.createElement("h3")
    container.appendChild(headline)
    headline.innerText = "Token"

    const tokencontainer = document.createElement("div")
    container.appendChild(tokencontainer)

    try {
        tokens = await (await fetch(url)).json()

        console.log(tokens);
        
        tokens.forEach(token => {
            console.log(token)

            const name = document.createElement("span")
            name.innerText = token.name
            name.style.cursor = "pointer"
            name.onclick = function()
            {
                OMNILITEtokenOptions(address, token)
            }

            const balance = document.createElement("span")
            balance.innerText = token.balance
            balance.style.float = "right"

            tokencontainer.appendChild(name)
            tokencontainer.appendChild(balance)
            tokencontainer.appendChild(document.createElement("br"))
        })
    } catch (error) {
        console.log("No Tokens here", error)
        headline.innerText = "No Tokens here"
    }
}

async function OMNILITEtokenOptions(address, token)
{
    _LitecoinWalletOptions.innerHTML = ""

    const headline = document.createElement("h2")
    _LitecoinWalletOptions.appendChild(headline)
    headline.innerText = "Options"

    const send = document.createElement("button")
    _LitecoinWalletOptions.appendChild(send)
    send.classList.add("ButtonRed")
    send.innerText = "send to address"
    send.onclick = function () {
        document.getElementById("MASKsendltcomnitokenorigin").innerText = address
        document.getElementById("MASKsendltcomnitokenid").innerText = token.propertyid
        document.getElementById("MASKsendltcomnitokenname").innerText = token.name
        Mask("sendltcomnitoken")
    }

    const list = document.createElement("button")
    _LitecoinWalletOptions.appendChild(list)
    list.classList.add("ButtonRed")
    list.innerText = "list on DEX"
    list.onclick = function()
    {
        console.log(token)
        document.getElementById("MASKdexListorigin").innerText = address
        document.getElementById("MASKdexListtoken").innerText = token.propertyid
        document.getElementById("MASKdexListname").innerText = token.name
        Mask("dexList")
    }

    const cancel = document.createElement("button")
    _LitecoinWalletOptions.appendChild(cancel)
    cancel.classList.add("ButtonRed")
    cancel.innerText = "cancel Listing"
    cancel.onclick = function()
    {
        console.log(token)
        document.getElementById("MASKdexListorigin").innerText = address
        document.getElementById("MASKdexListtoken").innerText = token.propertyid
        document.getElementById("MASKdexListname").innerText = token.name
        DEXcancel()
    }
}





function getProperties()
{
    let url = API + "omnilite-get-properties&authkey=" + AUTHKEY

    fetch(url).then((response) => response.json()).then(function(data)
    {
        WALLET = data
        console.log(WALLET)
        WalletFill()
        MENUchange("litecoin")
    })
}

async function OMNILITEgetProperty(propertyid)
{
    let url = API + "ltc-property-get&property=" + propertyid

    let property = await (await fetch(url)).json()
    console.log(property)
    return property
}

function PropertiesFill()
{
    const section_properties = document.getElementById("omnilite")
    section_properties.innerHTML = "" 

    const properties_div1 = document.createElement("div")
    const properties_div2 = document.createElement("div")
    const properties_div3 = document.createElement("div")

    properties_div1.classList.add("WalletDiv")
    properties_div2.classList.add("WalletDiv")
    properties_div3.classList.add("WalletDiv")

    properties_div1.classList.add("swapdiv1")
    properties_div2.classList.add("swapdiv2")
    properties_div3.classList.add("swapdiv3")

    section_properties.appendChild(properties_div1)
    section_properties.appendChild(properties_div2)
    section_properties.appendChild(properties_div3)

    const div1_header = document.createElement("h3")
    const div2_header = document.createElement("h3")
    const div3_header = document.createElement("h3")

    div1_header.innerHTML = "List"
    div2_header.innerHTML = "Details"
    div3_header.innerHTML = "All NFT's"

    properties_div1.appendChild(div1_header)
    properties_div2.appendChild(div2_header)
    properties_div3.appendChild(div3_header)
}


function WalletFill()
{
    const section_wallet = document.getElementById("litecoin")
    section_wallet.innerHTML = "" 

    const wallet_div1 = document.createElement("div")
    const wallet_div2 = document.createElement("div")
    const wallet_div3 = document.createElement("div")

    wallet_div1.classList.add("main-div")
    wallet_div2.classList.add("main-div")
    wallet_div3.classList.add("main-div")

    section_wallet.appendChild(wallet_div1)
    section_wallet.appendChild(wallet_div2)
    section_wallet.appendChild(wallet_div3)

    const div1_header = document.createElement("h3")
    div1_header.style.width = "70%"
    const div2_header = document.createElement("h3")
    const div3_header = document.createElement("h3")

    div1_header.innerHTML = "Labels"
    div2_header.innerHTML = "Details"
    div3_header.innerHTML = "All UTXO's"

    wallet_div1.appendChild(div1_header)
    wallet_div2.appendChild(div2_header)
    wallet_div3.appendChild(div3_header)

    const newaddress = document.createElement("button")
    newaddress.innerHTML = "+"
    newaddress.style.padding = "9px"
    newaddress.style.fontSize = "0.73rem"
    newaddress.style.fontWeight = "bolder"
    newaddress.onclick = function()
    {
        Mask('newaddress')
    }

    wallet_div1.appendChild(newaddress)

    const labels = Object.keys(WALLET.addresses)

    const label_addresses = []

    for (let a = 0; a < labels.length; a++)
    {
        const label = labels[a]
        const addresses = WALLET.addresses[label]
        
        const label_div = document.createElement("div")
        const labelHead = document.createElement("span")
        label_div.appendChild(labelHead)
        wallet_div1.appendChild(label_div)
        label_div.style.border = "1px solid white"
        label_div.style.padding = "10px"
        label_div.classList.add("expandBox-item")
        label_div.classList.add("position-container")
        label_div.onclick = function()
        {
            toggleDetails(this)
        }

        const outer_details = document.createElement("div")
        outer_details.classList.add("expandBox-details")
        label_div.appendChild(outer_details)

        var labelBalance = 0

        for (let b = 0; b < addresses.length; b++)
        {
            const address_object = addresses[b]
            
            const container = document.createElement("div")
            //container.classList.add("expandBox-item")
            container.classList.add("position-container")
            container.style.marginLeft = "1px"
            container.onclick = function(event)
            {
                event.stopPropagation(); // Verhindert das Schließen der ExpandBox

                const details = document.createElement("div")
                //details.classList.add("expandBox-details")

                const button_qr = document.createElement("button")
                button_qr.innerHTML = "QR Code"
                button_qr.onclick = function(event)
                {
                    ShowQrCode(event, address_object.address)
                }

                const button_send = document.createElement("button")
                button_send.innerHTML = "Send LTC"
                button_send.onclick = function(event)
                {
                    SendNativeLTC(event, address_object.address)
                }

                const button_merge = document.createElement("button")
                button_merge.innerHTML = "Merge UTXO"
                button_merge.onclick = function(event)
                {
                    MergeUTXO(event, address_object.address)
                }

                details.appendChild(button_qr)
                details.appendChild(button_send)
                details.appendChild(button_merge)

                const utxo_div = document.createElement("div")
                const utxo_headline = document.createElement("b")
                utxo_headline.innerHTML = "UTXO<br>"
                utxo_div.appendChild(utxo_headline)

                for (let c = 0; c < address_object.utxo.length; c++)
                {
                    const element = address_object.utxo[c]
                    const info = document.createElement("b")

                    info.innerHTML = element.amount + " LTC<br>"
                    utxo_div.appendChild(info)
                }

                const omni_div = document.createElement("div")
                const omni_headline = document.createElement("b")
                omni_headline.innerHTML = "OmniToken<br>"
                omni_div.appendChild(omni_headline)

                for (let c = 0; c < address_object.properties.length; c++)
                    {
                        const element = address_object.properties[c]
                        const info = document.createElement("b")

                        info.innerHTML = element.propertyid + " " + element.balance
                        omni_div.appendChild(info)
                    }

                if (address_object.utxo.length == 0) utxo_headline.innerHTML = "no utxo here<br>"

                details.appendChild(utxo_div)
                details.appendChild(omni_div)
                wallet_div2.innerHTML = ""
                wallet_div2.appendChild(details)
                //toggleDetails(this)
            }

            

            const info = document.createElement("div")
            info.classList.add("expandBox-info")

            const address_span = document.createElement("span")
            address_span.classList.add("expandBox-label")
            address_span.style.width = "50%"
            address_span.style.fontSize = "0.73rem"

            const balance_span = document.createElement("span")
            balance_span.classList.add("expandBox-value")

            address_span.innerHTML = address_object.address
            balance_span.innerHTML = parseFloat(address_object.balance) + " LTC"
            labelBalance += parseFloat(address_object.balance)

            info.appendChild(address_span)
            info.appendChild(balance_span)
            container.appendChild(info)
            outer_details.appendChild(container)
        }

        labelHead.innerHTML = label + " - " + labelBalance + "LTC"

        label_addresses[label] = document.createElement("div")
        if (label != "default") label_addresses[label].style.display = "none"
        label_addresses[label].id = label
        
        wallet_div2.appendChild(label_addresses[label])
    }

    const all_utxo = Object.values(WALLET.allutxo)

    const balance = document.createElement("b")
    balance.innerHTML = 0
    wallet_div3.appendChild(balance)

    for (let a = 0; a < all_utxo.length; a++)
    {
        const element = all_utxo[a]

        const div = document.createElement("div")
        const address = document.createElement("b")
        const amount = document.createElement("b")

        address.innerHTML = element.address.substring(0,9) + ".." + element.address.substring(element.address.length -3, element.address.length) + " "
        amount.innerHTML = element.amount + " LTC"
        balance.innerHTML = parseFloat(balance.innerHTML) + element.amount

        div.appendChild(address)
        div.appendChild(amount)
        wallet_div3.appendChild(div)

        //console.log(element)
    }

    balance.innerHTML = parseFloat(balance.innerHTML).toFixed(8)
    balance.innerHTML = " " + balance.innerHTML + " LTC"

    PropertiesFill()
}


function ScanOmni(address)
{
    const url = API + "omnilite-get"
}


function switchLabel(div, label)
{
    for (let a = 1; a < div.children.length; a++) 
    {
        div.children[a].style.display = "none"
        if (label == div.children[a].id) div.children[a].style.display = "block"

        //console.log(label, div.children[a].id)
    }
}

function toggleDetails(element) {
    element.classList.toggle('expanded');
}

function ShowQrCode(event, adr) {
    event.stopPropagation(); // Verhindert das Schließen der ExpandBox
    // Hier kannst du die Logik für die Buttons hinzufügen

    //console.log(adr)

    const canvas = document.createElement("canvas")
    canvas.style.position = "absolute"
    canvas.style.zIndex = "10"
    canvas.style.top = "50%"
    canvas.style.left = "50%"
    canvas.style.transform = "translate(-50%, -50%)"

    document.body.appendChild(canvas)

    var qr = new QRious({
        element: canvas, // Das Canvas-Element
        value: adr, // Der Text oder die URL
        size: 300 // Größe des QR-Codes
    })

    canvas.onclick = function()
    {
        canvas.remove()
    }
}

async function OMNILITEtokenSend()
{
    const origin = document.getElementById("MASKsendltcomnitokenorigin").innerHTML
    const destination = document.getElementById("MASKsendltcomnitokendestination").value
    const amount = document.getElementById("MASKsendltcomnitokenamount").value
    const token = document.getElementById("MASKsendltcomnitokenid").innerHTML

    const url = API + "ltcomni-token-send&token=" + token + "&amount=" + amount + "&origin=" + origin + "&destination=" + destination + "&authkey=" + AUTHKEY
    console.log(url)
    const response = await (await fetch(url)).json()

    console.log(response)
    response.name = USER.name
    response.action = "ltcsend"
    Terminal(response)
}