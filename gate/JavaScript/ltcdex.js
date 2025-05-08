DEXhome()
function DEXhome() {
    const list = document.getElementById("Home").children[0].children[1]
    const dummy = list.children[0]
    list.children[0].remove()

    _Omnilite.DEXget().then(dex => {
        for (let index = 0; index < dex.length; index++) {
            const element = dex[index]
            setTimeout(()=>{
                _Omnilite.PropertyGet(element.propertyid).then(property => {
                    const listing = dummy.cloneNode(true)
                    listing.id = element.seller
    
                    // Image
                    try {
                        let grantdata = JSON.parse(property.data)
                        if (grantdata.hasOwnProperty("structure") && grantdata.hasOwnProperty("source") && grantdata.hasOwnProperty("content") && grantdata.hasOwnProperty("type")) {
                            list.appendChild(listing)

                            if (grantdata.structure == "epic" && grantdata.source == "ipfs") {
                                document.getElementById(element.seller).children[0].src = _IPFS + grantdata.content
                                document.getElementById(element.seller).style.boxShadow = "0 3px 7px rgba(10, 191, 204, 0.6)"
                                if (grantdata.type == "video" || grantdata.type == "audio") {
                                    const video = document.createElement("video")
                                    const source = document.createElement("source")
                                    video.appendChild(source)
                                    video.loop = true
                                    video.muted = true
                                    source.src = _IPFS + grantdata.content
                                    document.getElementById(element.seller).replaceChild(video, document.getElementById(element.seller).children[0])
                                    video.play()
                                }
                            }
                        }

                        if (grantdata.hasOwnProperty("structure") && grantdata.hasOwnProperty("json")) {
                            fetch(_Ordinal + grantdata.json).then(response => response.json()).then(json => {
                                list.appendChild(listing)
                                
                                document.getElementById(element.seller).children[0].src = _Ordinal + json.content[0]
                                document.getElementById(element.seller).style.boxShadow = "0 4px 16px rgba(223, 66, 18, 0.7)"
                            })
                        }
                        
                    } catch (error) {
                        listing.children[0].src = "favicon.ico"
                    }

                    // ID
                    listing.children[1].children[0].children[0].innerHTML = "ID: " + element.propertyid + "#"
                    listing.children[1].children[0].children[1].innerHTML = "Name: " + property.name
    
                    // FIllbar
                    const fill = listing.children[1].children[1].children[0].cloneNode(true)
                    listing.children[1].children[1].children[0].remove()
                    listing.children[1].children[1].appendChild(fill)
    
                    setTimeout(() => {
                        fill.style.width = element.amountavailable / property.totaltokens *100 + "%"
                    }, 100)
                })                
            }, index *100)

        }
    })
}