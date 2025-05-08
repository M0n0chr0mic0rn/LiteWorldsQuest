class Connect {
    url = "https://liteworlds.quest/?method="

    async register(name, pass, security, security_type) {
        return await (await fetch(this.url + "register", {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify({
                "name": name,
                "pass": pass,
                [security_type]: security
            })
        })).json()
    }

    async login(name, pass) {
        return await (await fetch(this.url + "login", {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify({
                "name": name,
                "pass": pass
            })
        })).json()
    }
}

class User {
    createtime
    faucetkotia
    faucetlitecoin
    language
    lastaction
    name
    pairingomnilite
    security

    constructor() {

    }

    save(user) {
        this.createtime = user.createtime
        this.faucetkotia = user.faucetkotia
        this.faucetlitecoin = user.faucetlitecoin
        this.language = user.language
        this.lastaction = user.lastaction
        this.name = user.name
        this.pairingomnilite = user.pairingomnilite
        this.security = user.security
    }

    async get(authkey, url = "https://liteworlds.quest/?method=") {
        return await (await fetch(url + "get", {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify({
                "authkey": authkey
            })
        })).json()
    }

    async update(authkey, key, value, url = "https://liteworlds.quest/?method=update") {
        return await (await fetch(url, {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify({
                "authkey": authkey,
                "key": key,
                "value": value
            })
        })).json()
    }
}

class Litecoin {
    async get(authkey, url = "https://liteworlds.quest/?method=ltc-get") {
        return await (await fetch(url, {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify({
                "authkey": authkey
            })
        })).json()
    }
}

class Omnilite {
    url = "https://liteworlds.quest/?method="

    async AdrGet(address) {
        console.log(this.url + "ltcomni-get-balance-address&address=" + address)
        return await (await fetch(this.url + "ltcomni-get-balance-address&address=" + address)).text()
    }

    async PropertyGet(id) {
        return await (await fetch(this.url + "ltc-property-get&property=" + id)).json()
    }

    async DEXget() {
        return await (await fetch(this.url + "ltc-dex-get")).json()
    }

    async TraderBotGet() {
        return await (await fetch(this.url + "ltc-trader-get")).json()
    }

    async NFTget(property, token) {
        return await (await fetch(this.url + "ltc-nft-get&property=" + property + "&token=" + token)).json()
    }
}