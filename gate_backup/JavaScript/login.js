/*fetch(API + "get&authkey=" + AUTHKEY).then(response => response.json()).then(data =>
{
    console.log(data, AUTHKEY)
    if (data.bool)
    {
        USER = data.user
        MENUlogged("in")
        SETTINGSgenesis()
        LITECOINgenesis()
        //getProperties()
    }
})*/
async function Relogin()
{
    const response = await (await fetch(API + "get", {
        "method": "POST",
        "headers": {
            "Content-Type": "application/json"
        },
        "body": JSON.stringify({
            "authkey": AUTHKEY
        })
    })).json()
    //console.log(response)
    if (response.bool)
    {
        USER = response.user
        MENUlogged("in")
        SETTINGSgenesis()
        LITECOINgenesis()
        //getProperties()
    }

    /*const response = await (await fetch(API + "get&authkey=" + AUTHKEY)).json()
    console.log(response, AUTHKEY)
    */
}

async function Login()
{
    const user = document.getElementById("loginuser").value
    const pass = await sha512(document.getElementById("loginpass").value)

    const response = await (await fetch(API + "login", {
        "method": "POST",
        "headers": {
            "Content-Type": "application/json"
        },
        "body": JSON.stringify({
            "name": user,
            "pass": pass
        })
    })).json()

    console.log(response)
    
    response.name = user
    response.action = "login"
    Terminal(response)

    if (response.bool) _MASKlogin.style.display = "none"
}


function Logout()
{
    localStorage.removeItem("AuthKey")
    location.reload()
}

async function SignUp()
{
    const name = document.getElementById("reguser").children[0].value
    const pass = await sha512(document.getElementById("regpass").children[0].value)
    const pass1 = await sha512(document.getElementById("regpass1").children[0].value)
    const sec = document.getElementById("2fatype").value
    

    if (pass != pass1 && pass != "")
    {
        alert("Password missmatch")
    }
    else
    {
        //let twofa
        const body = {"name": name, "pass": pass}
        switch (sec)
        {
            case "telegram":
                //twofa = "&telegram=" + document.getElementById("regtelegram").children[document.getElementById("regtelegram").children.length -1].value.replaceAll("@", "")
                body.telegram = document.getElementById("regtelegram").children[document.getElementById("regtelegram").children.length -1].value.replaceAll("@", "")
            break;

            case "email":
                //twofa = "&email=" + encodeURIComponent(document.getElementById("regemail").children[document.getElementById("regemail").children.length -1].value)
                body.email = document.getElementById("regemail").children[document.getElementById("regemail").children.length -1].value
            break;
        
            default:
            break;
        }

        //const url = API + "register&name=" + name + "&pass=" + pass + twofa

        console.log(JSON.stringify(body))

        Mask("signup")
        const response = await (await fetch(API + "register", {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify(body)
        })).json()

        console.log(response)
        response.name = name
        response.action = "register"
        Terminal(response)
    }
}