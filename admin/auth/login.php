<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        overflow: hidden;
    }

    .container {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        width: 400px;
        position: relative;
        overflow: hidden;
        transform: scale(0.95);
        animation: containerPopIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    }

    @keyframes containerPopIn {
        0% {
            transform: scale(0.95);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .tabs {
        display: flex;
        margin-bottom: 2rem;
        position: relative;
    }

    .tab {
        flex: 1;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        color: #666;
    }

    .tab::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0%;
        height: 3px;
        background-color: #2196F3;
        transition: 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        transform: translateX(-50%);
    }

    .tab.active {
        color: #2196F3;
        font-weight: 600;
    }

    .tab.active::after {
        width: 100%;
    }

    .tab:hover {
        color: #2196F3;
        transform: translateY(-2px);
    }

    .form-group {
        margin-bottom: 1.5rem;
        position: relative;
    }

    .form-group:nth-child(1) {
        animation: formSlideIn 0.5s ease 0.1s forwards;
        opacity: 0;
    }
    .form-group:nth-child(2) {
        animation: formSlideIn 0.5s ease 0.2s forwards;
        opacity: 0;
    }
    .form-group:nth-child(3) {
        animation: formSlideIn 0.5s ease 0.3s forwards;
        opacity: 0;
    }
    .form-group:nth-child(4) {
        animation: formSlideIn 0.5s ease 0.4s forwards;
        opacity: 0;
    }

    @keyframes formSlideIn {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        color: #555;
        font-weight: 500;
        transform-origin: left center;
    }

    input {
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: #f9f9f9;
    }

    input:focus {
        outline: none;
        border-color: #2196F3;
        box-shadow: 0 0 0 3px rgba(33,150,243,0.2);
        transform: scale(1.02);
        background-color: white;
    }

    .btn {
        width: 100%;
        padding: 1rem;
        background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(33,150,243,0.2);
        position: relative;
        overflow: hidden;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(33,150,243,0.3);
    }

    .btn:active {
        transform: translateY(1px);
        box-shadow: 0 2px 4px rgba(33,150,243,0.2);
    }

    .btn::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.5);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
    }

    .btn:focus:not(:active)::after {
        animation: ripple 0.6s ease-out;
    }

    @keyframes ripple {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }
        100% {
            transform: scale(20, 20);
            opacity: 0;
        }
    }

    .form {
        display: none;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.6s ease, transform 0.6s ease;
    }

    .form.active {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    /* Floating animation for background elements */
    .floating-bg {
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(33, 150, 243, 0.1);
        z-index: -1;
    }

    .floating-bg:nth-child(1) {
        top: -100px;
        left: -100px;
        animation: float 15s infinite ease-in-out;
    }

    .floating-bg:nth-child(2) {
        bottom: -150px;
        right: -100px;
        animation: float 18s infinite ease-in-out reverse;
    }

    @keyframes float {
        0%, 100% {
            transform: translate(0, 0) rotate(0deg);
        }
        25% {
            transform: translate(10px, 10px) rotate(5deg);
        }
        50% {
            transform: translate(-10px, 15px) rotate(-5deg);
        }
        75% {
            transform: translate(15px, -10px) rotate(5deg);
        }
    }
</style>
</head>
<body>
    <div class="floating-bg"></div>
    <div class="floating-bg"></div>
    
    <div class="container">
        <div class="tabs">
            <div class="tab active" onclick="switchForm('login')">Login</div>
            <div class="tab" onclick="switchForm('register')">Register</div>
        </div>

        <!-- Login Form -->
        <form id="loginForm" class="form active" action="login_process.php" method="POST">
            <div class="form-group">
                <label for="loginUsername">Username</label>
                <input type="text" id="loginUsername" name="username" required>
            </div>
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <input type="password" id="loginPassword" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>

        <!-- Register Form -->
        <form id="registerForm" class="form" action="register_process.php" method="POST">
            <div class="form-group">
                <label for="regUsername">Username</label>
                <input type="text" id="regUsername" name="username" required>
            </div>
            <div class="form-group">
                <label for="regEmail">Email</label>
                <input type="email" id="regEmail" name="email" required>
            </div>
            <div class="form-group">
                <label for="regPassword">Password</label>
                <input type="password" id="regPassword" name="password" required>
            </div>
            <div class="form-group">
                <label for="regConfirmPassword">Confirm Password</label>
                <input type="password" id="regConfirmPassword" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">Register</button>
        </form>
    </div>

    <script>
        function switchForm(formType) {
            // Switch tabs with animation
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelector(`.tab[onclick="switchForm('${formType}')"]`).classList.add('active');

            // Switch forms with animation
            document.querySelectorAll('.form').forEach(form => {
                form.classList.remove('active');
                setTimeout(() => {
                    form.style.display = 'none';
                }, 300);
            });
            
            setTimeout(() => {
                const activeForm = document.getElementById(`${formType}Form`);
                activeForm.style.display = 'block';
                setTimeout(() => {
                    activeForm.classList.add('active');
                    
                    // Animate form groups sequentially
                    const formGroups = activeForm.querySelectorAll('.form-group');
                    formGroups.forEach((group, index) => {
                        group.style.animation = 'none';
                        void group.offsetWidth; // Trigger reflow
                        group.style.animation = `formSlideIn 0.5s ease ${index * 0.1}s forwards`;
                    });
                }, 10);
            }, 300);
        }
    </script>
</body>
</html>