setTimeout(()=>{ TraderBotHome() }, 1500)

function TraderBotHome() {
    const list = document.getElementById("Home").children[2].children[1]
    const dummy = list.children[0]
    dummy.remove()

    let index = 0
    _Omnilite.TraderBotGet().then(trader => {
        trader.forEach(property => {
            property.tokens.forEach(tokenrange => {
                for (let a = tokenrange.tokenstart; a <= tokenrange.tokenstart; a++) {
                    index++
                    setTimeout(() => {
                        _Omnilite.NFTget(property.propertyid, a).then(nft => {
                            TraderBotHomePin(dummy, nft[0], property.propertyid, list)
                        })
                    }, index * 100)
                }
            })
        })
    })
}

function TraderBotHomePin(dummy, nft, property, list) {
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
        list.appendChild(NFTcard)

        document.getElementById("nft#" + ID).children[0].children[0].innerHTML = "ID: " + ID
        document.getElementById("nft#" + ID).children[0].children[1].innerHTML = "Name: " + name + "<br>" + "Desire: " + desire + " LTC"

        const videoContainer = document.createElement("div")
        videoContainer.style.height = "10dvh"
        videoContainer.style.aspectRatio = "1/1"
        videoContainer.style.position = "relative"

        const video = document.createElement("video")
        video.id = "video#" + ID

        const button = document.createElement("button")
        button.classList.add("token-playpause")

        button.onclick = () => {
            if (document.getElementById("video#" + ID).paused) {
                document.getElementById("video#" + ID).play()
                button.style.opacity = 0
            } else {
                document.getElementById("video#" + ID).pause()
                button.style.opacity = 1
            }
        }

        videoContainer.appendChild(video)
        videoContainer.appendChild(button)

        const source = document.createElement("source")
        video.controls = false

        if (grantdata.hasOwnProperty("image") && grantdata.hasOwnProperty("name") && grantdata.hasOwnProperty("description") && grantdata.hasOwnProperty("attributes")) {
            fetch(_IPFS + grantdata.image.split("ipfs://")[1]).then(response => response.blob()).then(blob => {
                document.getElementById("nft#" + ID).children[1].src = URL.createObjectURL(blob)
                document.getElementById("nft#" + ID).style.boxShadow = "0 2px 4px rgba(21, 226, 49, 0.6)"

                if (blob.type.startsWith("audio/") || blob.type.startsWith("video/")) {
                    source.src = URL.createObjectURL(blob)
                    video.appendChild(source)
                    document.getElementById("nft#" + ID).replaceChild(videoContainer, document.getElementById("nft#" + ID).children[1])
                }
            })
        }

        if (grantdata.hasOwnProperty("structure") && grantdata.hasOwnProperty("source") && grantdata.hasOwnProperty("name") && grantdata.hasOwnProperty("type")) {
            if (grantdata.structure == "epic" && grantdata.source == "ipfs")
            {
                document.getElementById("nft#" + ID).children[1].src = _IPFS + grantdata.content
                document.getElementById("nft#" + ID).style.boxShadow = "0 3px 7px rgba(10, 191, 204, 0.6)"
                
                if (grantdata.type == "video" || grantdata.type == "audio") {
                    source.src = _IPFS + grantdata.content
                    video.appendChild(source)
                    document.getElementById("nft#" + ID).replaceChild(videoContainer, document.getElementById("nft#" + ID).children[1])
                }
            }
            
            if (grantdata.structure == "epic" && grantdata.source == "ordinal")
            {
                document.getElementById("nft#" + ID).children[1].src = _Ordinal + grantdata.content
                document.getElementById("nft#" + ID).style.boxShadow = "0 3px 12px rgba(216, 20, 223, 0.6)"
                
                if (grantdata.type == "video" || grantdata.type == "audio") {
                    source.src = _Ordinal + grantdata.content
                    video.appendChild(source)
                    document.getElementById("nft#" + ID).replaceChild(video, document.getElementById("nft#" + ID).children[1])
                }
            }
        }

        if (grantdata.hasOwnProperty("structure") && grantdata.hasOwnProperty("json"))
        {
            if (grantdata.structure == "artefactual" || grantdata.structure == "artifactual")
            {
                fetch(_Ordinal + grantdata.json).then((response) => response.json()).then(function(data){
                    document.getElementById("nft#" + ID).children[1].src = _Ordinal + data.content[0]
                    document.getElementById("nft#" + ID).children[0].children[0].innerHTML = "ID: " + ID
                    document.getElementById("nft#" + ID).children[0].children[1].innerHTML = "Name: " + data.data.name + "<br>" + "Desire: " + desire + " LTC"
                    document.getElementById("nft#" + ID).style.boxShadow = "0 4px 16px rgba(223, 66, 18, 0.7)"
                })
            }
        }
    }
}