const cLTClabel = document.getElementById("Wallet-cltc").children[1].children[1].children[0].children[1]
const cLTCaddress = document.getElementById("Wallet-cltc").children[1].children[1].children[0].children[3]

function LTCgenesis() {
    _Litecoin.get(_AUTHKEY).then(ltc => {
        Terminal(ltc)
        console.log(ltc)
    
        _Wallet.Litecoin = ltc.litecoin
    
        cLTClabel.innerHTML = ""

    
        const labels = Object.keys(_Wallet.Litecoin)
        labels.forEach((label, key) => {
            const option = document.createElement("option")
            option.value = label
            option.innerHTML = label
            cLTClabel.appendChild(option)
        })

        cLTClabel.onchange()
    })
}

cLTClabel.onchange = () => {
    const addresses = _Wallet.Litecoin[cLTClabel.value]

    cLTCaddress.innerHTML = ""
    let index = 0

    Object.keys(addresses).forEach(address => {
        const option = document.createElement("option")
        option.value = address
        option.innerHTML = address
        cLTCaddress.appendChild(option)
    })

    cLTCaddress.onchange()
}

cLTCaddress.onchange = () => {
    const addresses = _Wallet.Litecoin[cLTClabel.value]
    console.log(addresses, cLTCaddress.value)

    const utxos = addresses[cLTCaddress.value]
    console.log(utxos)

    console.log(_Omnilite)

    _Omnilite.AdrGet(cLTCaddress.value).then(omni => {
        console.log(omni)
    })

    const utxo_card = document.getElementById("Wallet-cltc").children[1].children[1].children[1]
    utxo_card.innerHTML = ""
    utxo_card.classList.add("card-item")

    const bal = document.createElement("span")
    bal.innerText = "0"
    bal.classList.add("card-key")

    utxo_card.appendChild(bal)

    Object.values(utxos).forEach(utxo => {
        const txidv = document.createElement("span")
        const amount = document.createElement("span")

        txidv.classList.add("card-value")
        amount.classList.add("card-key")

        txidv.style.cursor = "crosshair"
        txidv.style.textAlign = "right"

        utxo_card.appendChild(amount)
        utxo_card.appendChild(txidv)

        txidv.innerText = utxo.txid.substring(0, 18) + "..." + utxo.txid.substring(utxo.txid.length - 18, utxo.txid.length) + ":" + utxo.vout
        amount.innerText = parseFloat(utxo.amount) + " LTC"
        bal.innerText = parseFloat((parseFloat(bal.innerText) + parseFloat(utxo.amount)).toFixed("8"))

        txidv.onclick = () => {
            const a = document.createElement("a")
            a.href = "https://litecoinspace.org/tx/" + utxo.txid
            a.target = "_blank"
            a.click()
        }
    })

    bal.innerText = bal.innerText + " LTC"
}