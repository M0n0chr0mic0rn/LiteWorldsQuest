function isMobileDevice() {
    const userAgent = navigator.userAgent.toLowerCase();
    return /mobile|android|iphone|ipad|ipod|windows phone/.test(userAgent);
}

window.onload = function() {
    if (isMobileDevice()) {
        window.location.href = 'mobile.html';
    } else {
        window.location.href = 'desktop.html';
    }
}