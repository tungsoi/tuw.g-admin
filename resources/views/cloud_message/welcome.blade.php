<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <div>
        Alilogi Welcome
    </div>

    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/9.6.7/firebase-app.js";
        import { getAnalytics } from "https://www.gstatic.com/firebasejs/9.6.7/firebase-analytics.js";
        import { getMessaging, getToken } from "https://cdnjs.cloudflare.com/ajax/libs/firebase/9.6.7/firebase-messaging.min.js";

        // TODO: Add SDKs for Firebase products that you want to use
        // https://firebase.google.com/docs/web/setup#available-libraries
    
        // Your web app's Firebase configuration
        // For Firebase JS SDK v7.20.0 and later, measurementId is optional
        const firebaseConfig = {
            apiKey: "AIzaSyCqYm1Ay-tR_0gLbbvf5sY-XVCe0q73oDc",
            authDomain: "alilogi-web.firebaseapp.com",
            projectId: "alilogi-web",
            storageBucket: "alilogi-web.appspot.com",
            messagingSenderId: "327482479519",
            appId: "1:327482479519:web:8920cf8f7c964a8e7423aa",
            measurementId: "G-90LM8PWF4L"
        };
    
        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);

        const messaging = getMessaging();
        getToken(messaging, { vapidKey: '1:327482479519:web:8920cf8f7c964a8e7423aa' }).then((currentToken) => {
        if (currentToken) {
            // Send the token to your server and update the UI if necessary
            // ...
        } else {
            // Show permission request UI
            console.log('No registration token available. Request permission to generate one.');
            // ...
        }
        }).catch((err) => {
        console.log('An error occurred while retrieving token. ', err);
        // ...
        });

        console.log(app, analytics);
    </script>
</body>
</html>