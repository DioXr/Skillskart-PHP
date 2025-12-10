<?php
$page_title = "Go Premium";
require 'includes/header.php';
?>

<div class="container" style="max-width: 900px; text-align: center; margin-top: 50px;">
    
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 10px;">Upgrade your Learning</h1>
        <p style="color: var(--text-secondary); font-size: 1.1rem;">
            Get unlimited access to all roadmaps, expert notes, and progress tracking.
        </p>
    </div>

    <div class="dashboard-grid">
        
        <div class="card" style="text-align: center; padding: 40px; border-top: 4px solid var(--text-secondary);">
            <h3 style="color: var(--text-secondary);">Free Plan</h3>
            <h1 style="font-size: 3rem; margin: 20px 0;">₹0</h1>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Forever free</p>
            
            <ul style="list-style: none; padding: 0; text-align: left; margin-bottom: 30px; line-height: 2;">
                <li><i class="fa-solid fa-check" style="color: #2ecc71;"></i> Access Basic Roadmaps</li>
                <li><i class="fa-solid fa-check" style="color: #2ecc71;"></i> Track Progress</li>
                <li style="color: var(--text-secondary); opacity: 0.5;"><i class="fa-solid fa-xmark"></i> Premium Topics</li>
                <li style="color: var(--text-secondary); opacity: 0.5;"><i class="fa-solid fa-xmark"></i> Request Expert Notes</li>
                <li style="color: var(--text-secondary); opacity: 0.5;"><i class="fa-solid fa-xmark"></i> Downloadable Resources</li>
            </ul>

            <button class="button" disabled style="background: var(--background-color); color: var(--text-secondary); cursor: default; width: 100%;">Current Plan</button>
        </div>

        <div class="card" style="text-align: center; padding: 40px; border-top: 4px solid var(--primary-color); transform: scale(1.05); box-shadow: 0 8px 30px rgba(0,0,0,0.2);">
            <div style="position: absolute; top: 10px; right: 10px;">
                <span class="badge badge-premium">Recommended</span>
            </div>
            
            <h3 style="color: var(--primary-color);">Pro Learner</h3>
            <h1 style="font-size: 3rem; margin: 20px 0;">₹499<span style="font-size: 1rem; color: var(--text-secondary);">/mo</span></h1>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Cancel anytime</p>
            
            <ul style="list-style: none; padding: 0; text-align: left; margin-bottom: 30px; line-height: 2;">
                <li><i class="fa-solid fa-check" style="color: #2ecc71;"></i> <strong>Everything in Free</strong></li>
                <li><i class="fa-solid fa-check" style="color: #2ecc71;"></i> Unlock Premium Topics</li>
                <li><i class="fa-solid fa-check" style="color: #2ecc71;"></i> <strong>Request Expert Notes</strong></li>
                <li><i class="fa-solid fa-check" style="color: #2ecc71;"></i> Priority Support</li>
                <li><i class="fa-solid fa-check" style="color: #2ecc71;"></i> Certificate of Completion</li>
            </ul>

            <a href="payment.php" class="button" style="display: block; width: 100%; font-size: 1.1rem; padding: 15px; text-decoration: none;">
                Upgrade Now <i class="fa-solid fa-rocket"></i>
            </a>
        </div>

    </div>
</div>

<?php require 'includes/footer.php'; ?>