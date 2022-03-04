// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here. Other Firebase libraries
// are not available in the service worker.importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js');
/*
Initialize the Firebase app in the service worker by passing in the messagingSenderId.
*/
firebase.initializeApp({
    apiKey: 'AIzaSyCqYm1Ay-tR_0gLbbvf5sY-XVCe0q73oDc',
        authDomain: 'alilogi-web.firebaseapp.com',
        databaseURL: 'https://project-id.firebaseio.com',
        projectId: 'alilogi-web',
        storageBucket: 'alilogi-web.appspot.com',
        messagingSenderId: '327482479519',
        appId: '1:327482479519:web:8920cf8f7c964a8e7423aa',
        measurementId: 'G-90LM8PWF4L',
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function (payload) {
    const title = "Hello world is awesome";
    const options = {
        body: "Your notificaiton message .",
        icon: "/firebase-logo.png",
    };
    return self.registration.showNotification(
        title,
        options,
    );
});