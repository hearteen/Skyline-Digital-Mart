<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Merchant Flow</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Popup Overlay Style */
        .overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); display: flex; justify-content: center; align-items: center; z-index: 1000;
        }
        .lang-popup {
            background: white; padding: 30px; border-radius: 15px; text-align: center;
            box-shadow: 0 0 20px var(--cyan-glow); border: 2px solid var(--cyan-glow);
        }
        .lang-btn {
            padding: 10px 20px; margin: 10px; cursor: pointer; border: 1px solid var(--primary-blue);
            border-radius: 5px; background: white; font-weight: 600; transition: 0.3s;
        }
        .lang-btn:hover { background: var(--primary-blue); color: white; }
        .hidden { display: none; }
    </style>
</head>
<body class="light-theme">

    <div id="langOverlay" class="overlay">
        <div class="lang-popup">
            <h3>Select Your Language / மொழியைத் தேர்ந்தெடுக்கவும்</h3>
            <button class="lang-btn" onclick="setLanguage('en')">English</button>
            <button class="lang-btn" onclick="setLanguage('ta')">தமிழ்</button>
        </div>
    </div>

    <section class="login-container">
        <div class="login-box hidden" id="signupForm">
            <h2 id="formTitle">Create Account</h2>
            <form action="process_signup.php" method="POST">
                <input type="hidden" name="language" id="selectedLang" value="en">

                <div class="input-group">
                    <label id="lblShopName">Shop Name</label>
                    <input type="text" name="shop_name" required>
                </div>
                <div class="input-group">
                    <label id="lblOwner">Shopkeeper Name</label>
                    <input type="text" name="owner_name" required>
                </div>
                <div class="input-group">
                    <label id="lblLocation">Location</label>
                    <input type="text" name="location" required>
                </div>
                <div class="input-group">
                    <label id="lblWhatsApp">WhatsApp Number</label>
                    <input type="text" name="whatsapp_no" placeholder="91XXXXXXXXXX" required>
                </div>
                <button type="submit" class="btn-primary" id="btnSubmit">Register & Go Home</button>
            </form>
        </div>
    </section>

    <script>
        function setLanguage(lang) {
            document.getElementById('selectedLang').value = lang;
            document.getElementById('langOverlay').classList.add('hidden');
            document.getElementById('signupForm').classList.remove('hidden');

            // Dynamic Label Translation Logic
            if(lang === 'ta') {
                document.getElementById('formTitle').innerText = "கணக்கை உருவாக்கவும்";
                document.getElementById('lblShopName').innerText = "கடையின் பெயர்";
                document.getElementById('lblOwner').innerText = "கடைக்காரர் பெயர்";
                document.getElementById('lblLocation').innerText = "இடம்";
                document.getElementById('lblWhatsApp').innerText = "வாட்ஸ்அப் எண்";
                document.getElementById('btnSubmit').innerText = "பதிவு செய்து முகப்புக்குச் செல்லவும்";
            }
        }
    </script>
</body>
</html>