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
    url = "https://liteworlds.quest/?method="

    constructor(authkey) {
        this.authkey = authkey
    }

    saveUser(user) {
        this.name = user.name
    }

    async get() {
        return await (await fetch(url + "get", {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify({
                "authkey": this.authkey
            })
        })).json()
    }

    async update(key, value) {
        return await (await fetch(url + "update", {
            "method": "POST",
            "headers": {
                "Content-Type": "application/json"
            },
            "body": JSON.stringify({
                "authkey": this.authkey,
                "key": key,
                "value": value
            })
        })).json()
    }
}

class Omnilite {
    url = "https://liteworlds.quest/?method="

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