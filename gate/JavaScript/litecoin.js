async function LITECOINgenesis()
{
    const url = API + "ltc-get&authkey=" + AUTHKEY
    WALLET.litecoin = (await (await fetch(url)).json()).litecoin

    console.log(WALLET)
    LITECOINlabels()
}

function LITECOINlabels()
{
    _LitecoinWalletLabels.innerHTML = ""

    const labels = Object.keys(WALLET.litecoin)
    const headline = document.createElement("h2")
    headline.innerText = "Labels"
    _LitecoinWalletLabels.appendChild(headline)

    const select = document.createElement("select")
    const doption = document.createElement("option")
    doption.innerText = "Choose a label"
    doption.value = "default"
    select.appendChild(doption)
    doption.disabled = true
    select.onchange = () => { LITECOINaddress(select.value) }

    _LitecoinWalletLabels.appendChild(select)

    const addresses = document.createElement("div")
    _LitecoinWalletLabels.appendChild(addresses)

    labels.forEach(label =>
    {
        const option = document.createElement("option")
        option.value = label
        option.id = "LitecoinWalletLabel#" + label
        option.innerText = label
        select.appendChild(option)

        LITECOINaddress(label)
    })

    const createadr = document.createElement("button")
    createadr.innerText = "New Address"
    createadr.onclick = function()
    {
        Mask("newaddress")
    }
    createadr.style.position = "absolute"
    createadr.style.right = 0
    createadr.style.top = 0
    _LitecoinWalletLabels.appendChild(createadr)
}

function LITECOINaddress(label)
{
    _LitecoinWalletLabels.children[2].innerHTML = ""
    const option = document.getElementById("LitecoinWalletLabel#" + label)
    let label_total = 0

    const addresses = Object.keys(WALLET.litecoin[label])
    addresses.forEach(address => 
    {
        //console.log(address)

        const container = document.createElement("div")
        container.classList.add("expandBox-item")
        container.onclick = function()
        {
            toggleDetails(this)

            _LitecoinWalletDetails.innerHTML = ""

            OMNILITEget(address)
            ORDINALget(address)
        }

        const info = document.createElement("div")
        info.classList.add("expandBox-info")

        const address_span = document.createElement("span")
        address_span.classList.add("expandBox-label")

        const balance_span = document.createElement("span")
        balance_span.classList.add("expandBox-value")

        address_span.innerHTML = address
        balance_span.innerHTML = 0

        info.appendChild(address_span)
        info.appendChild(balance_span)
        container.appendChild(info)

        const details = document.createElement("div")
        details.classList.add("expandBox-details")
        details.onclick = function(event)
        {
            event.stopPropagation()
        }

        const utxos = WALLET.litecoin[label][address]
        utxos.forEach(utxo => 
        {
            console.log(utxo)
            const utxo_span_id = document.createElement("span")
            const utxo_span_amount = document.createElement("span")

            utxo_span_id.innerHTML = utxo.txid.substring(0, 9) + "..." + utxo.txid.substring(utxo.txid.length -9, utxo.txid.length) + ":" + utxo.vout
            utxo_span_id.style.float = "left"

            utxo_span_amount.innerHTML = utxo.amount + " LTC"
            utxo_span_amount.style.float = "right"

            label_total += parseFloat(utxo.amount)

            balance_span.innerHTML = parseFloat((parseFloat(balance_span.innerHTML) + parseFloat(utxo.amount)).toFixed(8)) + " LTC"

            details.appendChild(utxo_span_id)
            details.appendChild(utxo_span_amount)
            details.appendChild(document.createElement("br"))
        })

        const button_qr = document.createElement("button")
        button_qr.innerHTML = "QR Code"
        button_qr.onclick = function(event)
        {
            //event.stopPropagation()
            ShowQrCode(event, address)
        }

        const button_send = document.createElement("button")
        button_send.innerHTML = "Send LTC"
        button_send.onclick = function(event)
        {
            //event.stopPropagation()
            //SendNativeLTC(event, address_object.address)
        }

        const button_merge = document.createElement("button")
        button_merge.innerHTML = "Merge UTXO"
        button_merge.onclick = function(event)
        {
            //event.stopPropagation()
            //MergeUTXO(event, address_object.address)
        }

        details.appendChild(button_qr)
        details.appendChild(button_send)
        details.appendChild(button_merge)
        container.appendChild(details)

        _LitecoinWalletLabels.children[2].appendChild(container)
    })
    
    option.innerHTML = label + " " + parseFloat(label_total.toFixed(8)) + " LTC"
}