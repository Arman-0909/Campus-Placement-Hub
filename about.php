<?php

require_once 'includes/header_includes.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Campus Placement Hub</title>
    <style>
        html, body { height: 100%; overflow: hidden; }

        .about-cards-wrapper {
            display: flex;
            flex-direction: row;
            gap: 1cm;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .profile-card {
            padding: 1.25rem !important;
            width: 240px !important;
            min-width: unset !important;
            max-width: unset !important;
        }

        .avatar {
            width: 52px !important;
            height: 52px !important;
            font-size: 1rem !important;
            margin-bottom: 0.75rem !important;
        }

        .profile-header h2 { font-size: 1rem !important; }
        .badge { font-size: 0.7rem !important; padding: 0.2rem 0.6rem !important; }
        .contact-item { padding: 0.5rem 0 !important; }
        .icon-box { width: 28px !important; height: 28px !important; }
        .icon-box i { width: 13px !important; height: 13px !important; }
        .label { font-size: 0.65rem !important; }
        .value, .value.link { font-size: 0.72rem !important; }
        .action-area { margin-top: 0.75rem !important; }
        .btn-block { padding: 0.5rem 0.75rem !important; font-size: 0.78rem !important; }

        .avatar-black { background: #1e293b !important; }
        .avatar-blue  { background: #2563eb !important; }

        .content-panel {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            overflow: hidden !important;
        }
    </style>
</head>

<body>
    <a href="javascript:history.back()" class="back-btn" title="Go Back">
        <i data-lucide="x"></i>
    </a>

    <div class="split-layout">
        <div class="brand-panel">
            <div class="brand-content">
                <h1>About the Team</h1>
                <p>Crafted with precision to bridge the gap between academic potential and professional success.</p>
            </div>
        </div>

        <div class="content-panel">
            <div class="about-cards-wrapper">

                <div class="profile-card">
                    <div class="avatar avatar-black">
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

                <div class="profile-card">
                    <div class="avatar avatar-blue">
                        <span>AN</span>
                    </div>

                    <div class="profile-header">
                        <h2>Anurag Shukla</h2>
                        <div class="badge" style="background: #dbeafe; color: #1d4ed8;">QA Tester</div>
                    </div>

                    <div class="contact-list">
                        <div class="contact-item">
                            <div class="icon-box">
                                <i data-lucide="mail"></i>
                            </div>
                            <div class="info">
                                <span class="label">Contact</span>
                                <a href="mailto:anuragshukla0005@gmail.com" class="value link">anuragshukla0005@gmail.com</a>
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
                        <a href="mailto:anuragshukla0005@gmail.com" class="btn btn-primary btn-block" style="background: #2563eb;">
                            <i data-lucide="mail"></i>
                            Get in Touch
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
