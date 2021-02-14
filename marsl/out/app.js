var footerPermission = document.querySelector("#footer-permission");
var footerPermissionAccept = document.querySelector("#accept");
var footerPermissionReject = document.querySelector("#reject");

function registerForPush(pushManager) {
    const options = {
        userVisibleOnly: true,
        applicationServerKey: new Uint8Array([4, 191, 35, 159, 12, 167, 199, 212, 92, 79, 193, 68, 136, 84, 154, 108, 91, 167, 15, 108, 191, 226, 239, 179, 24, 232, 18, 195, 114, 242, 101, 151, 190, 148, 55, 119, 240, 103, 126, 143, 234, 149, 131, 15, 0, 80, 202, 113, 88, 214, 197, 200, 42, 23, 53, 13, 214, 20, 207, 153, 37, 228, 233, 229, 250])
    };

    pushManager.subscribe(options)
        .then(subscription => subscribeForPush(subscription));
}

function subscribeForPush(subscription) {
    jsonString = JSON.stringify(subscription);
    $.post("api.php?uri=1/pushtoken/create/webpush", jsonString);
}

function registerSW() { 
    if ('serviceWorker' in navigator) { 
        navigator.serviceWorker.register('./sw.js');
        navigator.serviceWorker.ready.then(function() {
            if ('PushManager' in window) {
                registerPushManager();
            }
        });
    }
}

function sleep(milliseconds) {
    return new Promise(resolve => setTimeout(resolve, milliseconds));
}

async function registerPushManager() {
    if (document.cookie.indexOf("pushNotificationBanner") == -1) {
        await sleep(10000);
        footerPermission.style.display = "block";
    }
    else {
        if (document.cookie.split(';').some(function(item) {
            return item.indexOf('pushNotificationBanner=1') >=0
        })) {
            navigator.serviceWorker.ready.then(registration => {
                registerForPush(registration.pushManager);
            });
        }
    }
}

window.addEventListener('load', e => {
    registerSW();
});

footerPermissionAccept.onclick = function(e) {
    footerPermission.style.display = "none";
    var cookieDate = new Date();
    cookieDate.setTime(new Date().getTime() + 315360000000);
    document.cookie = "pushNotificationBanner = 1; path=/; expires=" + cookieDate.toUTCString();
    navigator.serviceWorker.ready.then(registration => {
        registerForPush(registration.pushManager);
    });
}

footerPermissionReject.onclick = function(e) {
    var cookieDate = new Date();
    cookieDate.setTime(new Date().getTime() + 2592000000);
    document.cookie = "pushNotificationBanner = 2; path=/; expires=" + cookieDate.toUTCString();
    footerPermission.style.display = "none";
}