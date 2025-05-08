function Mask(action) {
    switch (action) {
        case "Login":
            const Mask = document.getElementById("Mask-login")
            if (Mask.style.display == "block") Mask.style.display = "none"
            else Mask.style.display = "block"
        break;
    
        default:
            break;
    }
}