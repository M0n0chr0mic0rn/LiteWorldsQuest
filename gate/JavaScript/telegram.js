async function TestTelegram()
{
    const url = API + "telegram-link&authkey=123&handle=tbuuol"
    console.log(url)
    const response = await (await fetch(url)).json()

    console.log(response)
}

function TestTelegram1()
{
    const url = API + "telegram-sign"
    fetch(url)
}