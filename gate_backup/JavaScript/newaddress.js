function LitecoinNewAddress()
{
    const label = document.getElementById("newaddresslabel").value
    const type = document.getElementById("newaddresstype").value

    const url = API + "ltc-new-address&authkey=" + AUTHKEY + "&label=" + label + "&type=" + type
    fetch(url).then(r => r.json()).then(function(data)
    {
        LITECOINgenesis()
        Mask("newaddress")
        TerminalEasy(data)
    })
}