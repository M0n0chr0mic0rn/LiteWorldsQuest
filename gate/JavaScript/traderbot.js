const dummy = document.getElementById("nft")
dummy.remove()
setTimeout(()=>{ TraderBotHome() }, 1500)

function TraderBotHome() {
    let index = 0

    _Omnilite.TraderBotGet().then(trader => {
        trader.forEach(property => {
            property.tokens.forEach(tokenrange => {
                for (let a = tokenrange.tokenstart; a <= tokenrange.tokenstart; a++) {
                    index++
                    setTimeout(() => {
                        _Omnilite.NFTget(property.propertyid, a).then(nft => {
                            TraderBotPin(dummy, nft[0], property.propertyid)
                        })
                    }, index * 100)
                }
            })
        })
    })
}

function TraderBotPin(dummy, nft, property) {
    let grantdata, holderdata, name, description, desire, ID, pass

    grantdata = nft.grantdata
    grantdata = grantdata.replaceAll("{'", '{"')
    grantdata = grantdata.replaceAll("'}", '"}')
    grantdata = grantdata.replaceAll("':'", '":"')
    grantdata = grantdata.replaceAll("','", '","')

    holderdata = nft.holderdata
    pass = false
    valid = true

    try {
        grantdata = JSON.parse(grantdata)
        if (holderdata != "") holderdata = JSON.parse(holderdata)
        else valid = false

        name = grantdata.name
        //description = grantdata.description
        desire = holderdata.desire
        ID = property + "#" + nft.index
        pass = true
    } catch (error) {
    }

    if (!valid) throw "missing holderdata"

    if (pass) {
        NFTcard = dummy.cloneNode(true)
        NFTcard.id = "nft#" + ID
        document.getElementById("traderbothome").appendChild(NFTcard)

        document.getElementById("nft#" + ID).children[1].children[0].innerHTML = ID
        document.getElementById("nft#" + ID).children[1].children[1].innerHTML = name + "<br>@ " + desire + "LTC"

        const video = document.createElement("video")
        const source = document.createElement("source")
        video.controls = true

        if (grantdata.hasOwnProperty("image") && grantdata.hasOwnProperty("name") && grantdata.hasOwnProperty("description") && grantdata.hasOwnProperty("attributes")) {
            fetch(_IPFS + grantdata.image.split("ipfs://")[1]).then(response => response.blob()).then(blob => {
                document.getElementById("nft#" + ID).children[0].src = URL.createObjectURL(blob)
                document.getElementById("nft#" + ID).style.boxShadow = "0 2px 4px rgba(21, 226, 49, 0.6)"

                if (blob.type.startsWith("audio/") || blob.type.startsWith("video/")) {
                    source.src = URL.createObjectURL(blob)
                    video.appendChild(source)
                    document.getElementById("nft#" + ID).replaceChild(video, document.getElementById("nft#" + ID).children[0])
                }
            })
        }

        if (grantdata.hasOwnProperty("structure") && grantdata.hasOwnProperty("source") && grantdata.hasOwnProperty("name") && grantdata.hasOwnProperty("type")) {
            if (grantdata.structure == "epic" && grantdata.source == "ipfs")
            {
                document.getElementById("nft#" + ID).children[0].src = _IPFS + grantdata.content
                document.getElementById("nft#" + ID).style.boxShadow = "0 3px 7px rgba(10, 191, 204, 0.6)"
                
                if (grantdata.type == "video" || grantdata.type == "audio") {
                    source.src = _IPFS + grantdata.content
                    video.appendChild(source)
                    document.getElementById("nft#" + ID).replaceChild(video, document.getElementById("nft#" + ID).children[0])
                }
            }
            
            if (grantdata.structure == "epic" && grantdata.source == "ordinal")
            {
                document.getElementById("nft#" + ID).children[0].src = _Ordinal + grantdata.content
                document.getElementById("nft#" + ID).style.boxShadow = "0 3px 12px rgba(216, 20, 223, 0.6)"
                
                if (grantdata.type == "video" || grantdata.type == "audio") {
                    source.src = _Ordinal + grantdata.content
                    video.appendChild(source)
                    document.getElementById("nft#" + ID).replaceChild(video, document.getElementById("nft#" + ID).children[0])
                }
            }
        }

        if (grantdata.hasOwnProperty("structure") && grantdata.hasOwnProperty("json"))
        {
            if (grantdata.structure == "artefactual" || grantdata.structure == "artifactual")
            {
                fetch(_Ordinal + grantdata.json).then((response) => response.json()).then(function(data){
                    document.getElementById("nft#" + ID).children[0].src = _Ordinal + data.content[0]
                    document.getElementById("nft#" + ID).children[1].children[0].innerHTML = ID + " @ " + desire + "LTC"
                    document.getElementById("nft#" + ID).children[1].children[1].innerHTML = data.data.name
                    document.getElementById("nft#" + ID).style.boxShadow = "0 4px 16px rgba(223, 66, 18, 0.7)"
                })
            }
        }
    }
}