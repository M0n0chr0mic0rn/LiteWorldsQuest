fetch(API + "get&authkey=" + AUTHKEY).then(response => response.json()).then(data =>
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
})

async function Login()
{
    const user = document.getElementById("loginuser").value
    const pass = await sha512(document.getElementById("loginpass").value)

    const url = API + "login&name=" + user + "&pass=" + pass
    console.log(url)
    const login = await (await fetch(url)).json()
    
    login.name = user
    login.action = "login"
    console.log(login)
    Terminal(login)

    if (login.bool) _MASKlogin.style.display = "none"
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
        let twofa
        switch (sec)
        {
            case "telegram":
                twofa = "&telegram=" + document.getElementById("regtelegram").children[document.getElementById("regtelegram").children.length -1].value.replaceAll("@", "")
            break;

            case "email":
                twofa = "&email=" + encodeURIComponent(document.getElementById("regemail").children[document.getElementById("regemail").children.length -1].value)
            break;
        
            default:
            break;
        }

        const url = API + "register&name=" + name + "&pass=" + pass + twofa

        Mask("signup")
        let content = await (await fetch(url)).json()
        content.name = name
        content.action = "register"
        Terminal(content)
    }
}