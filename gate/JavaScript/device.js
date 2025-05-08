// Funktion zur Erkennung des Geräts
function isMobileDevice() {
    const userAgent = navigator.userAgent.toLowerCase();
    return /mobile|android|iphone|ipad|ipod|windows phone/.test(userAgent);
}

// Dynamisches Laden des Inhalts
window.onload = function() {
    console.log(isMobileDevice())

    if (isMobileDevice()) window.location.href = 'mobile.html';
    else window.location.href = 'desktop.html';
}