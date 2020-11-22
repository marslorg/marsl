function registerForPush(pushManager) {
    const options = {
        userVisibleOnly: true,
        applicationServerKey: new Uint8Array([4, 191, 35, 159, 12, 167, 199, 212, 92, 79, 193, 68, 136, 84, 154, 108, 91, 167, 15, 108, 191, 226, 239, 179, 24, 232, 18, 195, 114, 242, 101, 151, 190, 148, 55, 119, 240, 103, 126, 143, 234, 149, 131, 15, 0, 80, 202, 113, 88, 214, 197, 200, 42, 23, 53, 13, 214, 20, 207, 153, 37, 228, 233, 229, 250])
    };

    pushManager.subscribe(options)
        .then(subscription => console.log(JSON.stringify(subscription)));
}

function registerSW() { 
    if ('serviceWorker' in navigator) { 
        navigator.serviceWorker.register('./sw.js');
        navigator.serviceWorker.ready.then(registration => {
            if ('PushManager' in window) {
                //registerForPush(registration.pushManager);
            }
        });
    }
}

window.addEventListener('load', e => {
    registerSW();
});