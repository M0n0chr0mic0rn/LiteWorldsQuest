let currentInterval = null // Speichert das aktuelle Intervall
let Terminal_hideout = null
let isError = false // Flag, um den Fehlerstatus zu verfolgen
let activeTypeWriter = null // Speichert das laufende Typewriter-Interval
const type_speed = 37

function Terminal(logData) {
    // ALLE laufenden Prozesse beenden
    clearTimeout(Terminal_hideout)
    if (currentInterval) clearInterval(currentInterval)
    if (activeTypeWriter) clearInterval(activeTypeWriter)
    
    // Terminal leeren und neu initialisieren
    const terminal = document.getElementById("terminal")
    const terminalContent = document.getElementById("terminal-content")
    terminal.style.display = "flex"
    terminal.innerHTML = ""
    terminalContent.innerHTML = ""
    terminal.appendChild(terminalContent)

    // Neue Log-Gruppe erstellen
    const actionLog = document.createElement("div")
    actionLog.style.color = "#FFD700"
    actionLog.style.fontWeight = "bold"
    actionLog.textContent = `>>> ${logData.action}`
    actionLog.style.position = "sticky"
    terminal.appendChild(actionLog)

    let Signature = false
    let time_left = 180

    if (logData.bool) {
        logData.response.push("Request successful")
        logData.response.push("Connection closed")

        switch (logData.action) {
            case "get":
                logData.response.push("You are logged in")
            break
            case "login":
                Signature = true
                Mask("Login")
            break
            case "update":
                Signature = true
            break
        }
    }

    if (Signature) {
        logData.response.push("Waiting for Signature")
    }

    // Jede Response-Zeile separat hinzufügen
    let lineIndex = 0 
    const addLine = () => {
        if (isError) return // Stoppe das Schreiben, wenn ein Fehler aufgetreten ist

        // Fehlerbehandlung zuerst
        if (logData.hasOwnProperty("error")) {
            const entry = logData.error
            const newLog = document.createElement("div")
            newLog.textContent = `error > `
            newLog.style.color = "red"
            newLog.style.paddingLeft = "10px"
            terminalContent.appendChild(newLog)

            // Typing-Effekt mit Abbruchschutz
            activeTypeWriter = typeWriter(newLog, entry, type_speed, () => {
                Terminal_hideout = setTimeout(() => {
                    terminal.style.display = "none"
                }, 3700)
            })

            isError = true 
            setTimeout(() => { isError = false }, 2000)
            return 
        }

        // Normale Response-Zeilen anzeigen
        if (lineIndex < logData.response.length && !logData.hasOwnProperty("error")) {
            const entry = logData.response[lineIndex]
            const newLog = document.createElement("div")
            newLog.textContent = `> `
            newLog.style.paddingLeft = "10px"
            terminalContent.appendChild(newLog)
            terminalContent.scrollTop = terminalContent.scrollHeight

            // Typing-Effekt mit Abbruchschutz
            activeTypeWriter = typeWriter(newLog, entry, type_speed, () => {
                lineIndex++
                addLine()
            })

            if (lineIndex + 1 == logData.response.length) {
                if (Signature) {
                    setTimeout(() => {
                        currentInterval = setInterval(() => {
                            time_left--
                            newLog.textContent = "> Waiting for Signature " + time_left

                            if (time_left <= 0) {
                                clearInterval(currentInterval)
                                terminal.style.display = "none"
                            }

                            if (logData.action == "login") {
                                _User.get(logData.authkey).then(response => {
                                    console.log(response)
                                    if (response.bool) {
                                        Terminal(response)
                                        clearInterval(currentInterval)
                                        localStorage.setItem("authkey", logData.authkey)
                                        location.reload()
                                    }
                                })
                            }
                        }, 1000)
                    }, 500)
                } else {
                    Terminal_hideout = setTimeout(() => { terminal.style.display = "none" }, 3700)
                }
            }
        }
    }

    addLine()
}

// Optimierte Typewriter-Funktion mit Abbruchmöglichkeit
function typeWriter(element, text, speed, callback) {
    let i = 0
    const interval = setInterval(() => {
        element.textContent += text[i]
        i++

        if (i === text.length) {
            clearInterval(interval)
            if (callback) callback()
        }
    }, speed)

    return interval // Rückgabe, um das Interval abbrechen zu können
}
