let currentInterval = null; // Speichert das aktuelle Interval
let isError = false; // Flag, um den Fehlerstatus zu verfolgen
const type_speed = 37

function Terminal(logData) {
    const terminal = document.getElementById("terminal");
    const terminalContent = document.getElementById("terminal-content");
    let time_left = 180;

    // Clear existing timeout, falls das Terminal schon mal ausgeblendet wurde
    clearTimeout(Terminal_hideout);

    // Stoppe den aktuellen Interval, falls er existiert
    if (currentInterval) {
        clearInterval(currentInterval);
        currentInterval = null; // Setze das Interval auf null
    }

    terminal.style.display = "flex";
    terminal.innerHTML = "";
    terminalContent.innerHTML = "";
    terminal.appendChild(terminalContent);

    // Neue Log-Gruppe erstellen
    const actionLog = document.createElement("div");
    actionLog.style.color = "#FFD700";
    actionLog.style.fontWeight = "bold";
    actionLog.textContent = `>>> ${logData.action}`;
    actionLog.style.position = "sticky";
    terminal.appendChild(actionLog);

    let Signature = false;

    if (logData.bool) {
        logData.response.push("Request successful");
        logData.response.push("Connection closed");

        switch (logData.action) {
            case "get":
                logData.response.push("You are logged in");
                break;

            case "login":
                Signature = true;
                Mask("Login");
                break;

            default:
                break;
        }
    }

    if (Signature) {
        logData.response.push("Waiting for Signature");
    }

    // Jede Response-Zeile separat hinzufügen
    let lineIndex = 0; // Index für die aktuelle Zeile
    const addLine = () => {

        if (isError) return; // Stoppe den Schreibprozess, wenn ein Fehler aufgetreten ist

        if (logData.hasOwnProperty("error")) {
            const entry = logData.error;
            const newLog = document.createElement("div");
            newLog.textContent = `error > `;
            newLog.style.color = "red";
            newLog.style.paddingLeft = "10px"; // Einrückung für bessere Übersicht
            terminalContent.appendChild(newLog);

            // Typing-Effekt für die Zeile
            typeWriter(newLog, entry, type_speed, () => {
                Terminal_hideout = setTimeout(() => {
                    terminal.style.display = "none";
                }, 3700);
            });

            isError = true; // Fehlerstatus setzen, um den weiteren Schreibprozess zu stoppen
            return; // Verhindere, dass die Erfolgsnachrichten weitergeschrieben werden
        }

        if (lineIndex < logData.response.length && !logData.hasOwnProperty("error")) {
            const entry = logData.response[lineIndex];
            const newLog = document.createElement("div");
            newLog.textContent = `> `;
            newLog.style.paddingLeft = "10px"; // Einrückung für bessere Übersicht
            terminalContent.appendChild(newLog);
            terminalContent.scrollTop = terminalContent.scrollHeight;

            // Typing-Effekt für die Zeile
            typeWriter(newLog, entry, type_speed, () => {
                lineIndex++; // Nächste Zeile
                addLine(); // Nächste Zeile nach Abschluss der aktuellen
            });

            if (lineIndex + 1 == logData.response.length) {
                if (Signature) {
                    setTimeout(() => {
                        currentInterval = setInterval(() => {
                            time_left--;
                            newLog.textContent = "> Waiting for Signature " + time_left;

                            if (time_left <= 0) {
                                clearInterval(currentInterval);
                                terminal.style.display = "none";
                            }

                            if (logData.action == "login") {
                                _User.get(logData.authkey).then(response => {
                                    console.log(response);
                                    if (response.bool) {
                                        Terminal(response);
                                        clearInterval(currentInterval);
                                        localStorage.setItem("authkey", logData.authkey);
                                        location.reload();
                                    }
                                });
                            }
                        }, 1000);
                    }, 500);
                } else {
                    Terminal_hideout = setTimeout(() => { terminal.style.display = "none" }, 3700);
                }
            }
        }
    };

    // Startet das Hinzufügen der Zeilen
    addLine();
}

// Typing-Effekt Funktion
function typeWriter(element, text, speed, callback) {
    let i = 0;
    const interval = setInterval(() => {
        element.textContent += text[i];
        i++;

        if (i === text.length) {
            clearInterval(interval); // Stoppt den Interval, wenn der Text fertig ist
            if (callback) callback(); // Ruft den Callback auf, wenn die Zeile fertig ist
        }
    }, speed);
}
