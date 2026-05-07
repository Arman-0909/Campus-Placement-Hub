<?php

require_once 'includes/header_includes.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Campus Placement Hub</title>
</head>

<body>
    <a href="javascript:history.back()" class="back-btn" title="Go Back">
        <i data-lucide="x"></i>
    </a>

    <div class="split-layout">
        <div class="brand-panel">
            <div class="brand-content">
                <h1>About the Creator</h1>
                <p>Crafted with precision to bridge the gap between academic potential and professional success.</p>
            </div>
        </div>
        <div class="content-panel">
            <div class="profile-card">
                <div class="avatar">
                    <span>AS</span>
                </div>

                <div class="profile-header">
                    <h2>Armandeep Singh</h2>
                    <div class="badge">Lead Developer</div>
                </div>

                <div class="contact-list">
                    <div class="contact-item">
                        <div class="icon-box">
                            <i data-lucide="mail"></i>
                        </div>
                        <div class="info">
                            <span class="label">Contact</span>
                            <a href="mailto:armandeep0088@gmail.com" class="value link">armandeep0088@gmail.com</a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="icon-box">
                            <i data-lucide="graduation-cap"></i>
                        </div>
                        <div class="info">
                            <span class="label">Education</span>
                            <span class="value">CGC University</span>
                        </div>
                    </div>
                </div>

                <div class="action-area">
                    <a href="https://armandeepsingh-dev.netlify.app/" target="_blank" class="btn btn-primary btn-block">
                        <i data-lucide="globe"></i>
                        View Portfolio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
