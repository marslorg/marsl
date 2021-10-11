new SmartBanner({
    daysHidden: 15,   // days to hide banner after close button is clicked (defaults to 15)
    daysReminder: 1, // days to hide banner after "VIEW" button is clicked (defaults to 90)
    appStoreLanguage: 'de', // language code for the App Store (defaults to user's browser language)
    title: 'Music2Web.de',
    author: 'Music2Web.de e.V.',
    button: 'Anzeigen',
    store: {
        android: 'Im Google Play Store',
    },
    price: {
        android: 'FREE',
    }
    // , theme: '' // put platform type ('ios', 'android', etc.) here to force single theme on all device
    // , icon: '' // full path to icon image if not using website icon image
    // , force: 'ios' // Uncomment for platform emulation
});