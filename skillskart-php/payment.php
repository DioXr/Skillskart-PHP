<?php
// Ensure user is logged in
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Robust Auth Check
$user_id = null;
if (isset($_SESSION['student_id'])) $user_id = $_SESSION['student_id'];
elseif (isset($_SESSION['user_id'])) $user_id = $_SESSION['user_id'];

if (!$user_id) { header("Location: login.php"); exit(); }

$page_title = "Checkout";
require 'includes/header.php';
?>

<div class="container" style="max-width: 500px; margin-top: 50px;">
    
    <div class="card" style="padding: 30px; border-top: 4px solid var(--primary-color);">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="margin: 0;">Secure Checkout</h2>
            <p style="color: var(--text-secondary); margin-top: 5px;">Complete your upgrade to Pro</p>
        </div>

        <div style="background: var(--background-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="margin: 0;">Pro Learner Plan</h4>
                <small style="color: var(--text-secondary);">Monthly Subscription</small>
            </div>
            <h3 style="margin: 0;">₹499.00</h3>
        </div>

        <div id="error-msg" style="display: none; background: rgba(220, 53, 69, 0.1); color: #dc3545; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 0.9rem;"></div>

        <form action="subscribe.php" method="post" id="paymentForm">
            
            <div class="form-group">
                <label>Cardholder Name</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-user" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" id="cardName" required placeholder="Rahul Sharma" style="padding-left: 40px;">
                </div>
            </div>

            <div class="form-group">
                <label>Card Number</label>
                <div style="position: relative;">
                    <i class="fa-solid fa-credit-card" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                    <input type="text" id="cardNumber" required placeholder="0000 0000 0000 0000" maxlength="19" style="padding-left: 40px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label>Expiry Date</label>
                    <input type="text" id="cardExpiry" required placeholder="MM/YY" maxlength="5">
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <div style="position: relative;">
                        <i class="fa-solid fa-lock" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-secondary);"></i>
                        <input type="password" id="cardCvv" required placeholder="123" maxlength="3" style="padding-left: 40px;">
                    </div>
                </div>
            </div>

            <div style="margin-top: 10px; display: flex; align-items: center; gap: 10px; color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 20px;">
                <i class="fa-solid fa-shield-halved" style="color: #2ecc71;"></i> 
                <span>Payments are secure and encrypted.</span>
            </div>

            <button type="submit" class="button" style="width: 100%; font-size: 1.1rem; padding: 12px;">
                Pay ₹499.00
            </button>
            
            <div style="text-align: center; margin-top: 15px;">
                <a href="pricing.php" style="color: var(--text-secondary); font-size: 0.9rem;">Cancel</a>
            </div>

        </form>
    </div>
    
    <div style="text-align: center; margin-top: 20px; color: var(--text-secondary); opacity: 0.5; font-size: 2rem;">
        <i class="fa-brands fa-cc-visa" style="margin: 0 5px;"></i>
        <i class="fa-brands fa-cc-mastercard" style="margin: 0 5px;"></i>
        <i class="fa-brands fa-cc-amex" style="margin: 0 5px;"></i>
        <i class="fa-solid fa-building-columns" style="margin: 0 5px;"></i> </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById('paymentForm');
    const nameInput = document.getElementById('cardName');
    const numberInput = document.getElementById('cardNumber');
    const expiryInput = document.getElementById('cardExpiry');
    const cvvInput = document.getElementById('cardCvv');
    const errorBox = document.getElementById('error-msg');

    // 1. NAME VALIDATION (Block Numbers)
    nameInput.addEventListener('input', function(e) {
        // Regex: Replace anything that is NOT letters or space
        e.target.value = e.target.value.replace(/[^a-zA-Z\s]/g, '');
    });

    // 2. FORMAT CARD NUMBER (Adds spaces every 4 digits)
    numberInput.addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
    });

    // 3. FORMAT EXPIRY (Adds slash after 2 digits)
    expiryInput.addEventListener('input', function(e) {
        let input = e.target.value.replace(/\D/g, ''); // Remove non-digits
        if (input.length > 2) {
            input = input.substring(0, 2) + '/' + input.substring(2, 4);
        }
        e.target.value = input;
    });

    // 4. FORCE NUMBERS ONLY FOR CVV
    cvvInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // 5. VALIDATE ON SUBMIT
    form.addEventListener('submit', function(e) {
        let errors = [];
        
        // Name Validation (Must be at least 2 words)
        if (nameInput.value.trim().split(' ').length < 2) {
            errors.push("Please enter your full name (First & Last).");
        }

        // Validate Card Number Length
        if (numberInput.value.length < 19) {
            errors.push("Invalid Card Number (must be 16 digits)");
        }

        // Validate Expiry
        const expiryParts = expiryInput.value.split('/');
        if (expiryParts.length !== 2) {
            errors.push("Invalid Expiry Date format (MM/YY)");
        } else {
            const month = parseInt(expiryParts[0]);
            if (month < 1 || month > 12) {
                errors.push("Invalid Month (01-12)");
            }
        }

        // Validate CVV
        if (cvvInput.value.length < 3) {
            errors.push("CVV must be 3 digits");
        }

        // If errors exist, stop form and show message
        if (errors.length > 0) {
            e.preventDefault(); // STOP THE SUBMIT
            errorBox.style.display = 'block';
            errorBox.innerHTML = errors.join('<br>');
            // Shake effect
            form.classList.add('shake');
            setTimeout(() => form.classList.remove('shake'), 500);
        }
    });
});
</script>

<style>
@keyframes shake {
  0% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  50% { transform: translateX(5px); }
  75% { transform: translateX(-5px); }
  100% { transform: translateX(0); }
}
.shake {
  animation: shake 0.3s;
}
</style>

<?php require 'includes/footer.php'; ?>