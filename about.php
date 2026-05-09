<?php require_once 'includes/header_includes.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Campus Placement Hub</title>
    <style>
        html, body { height: 100%; margin: 0; overflow: hidden; }

        .split-layout { display: flex; height: 100%; }
        .brand-panel   { flex: 0 0 42%; }

        .about-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1cm;
            background: #f8fafc;
        }

        .team-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 1.75rem 1.5rem;
            width: 270px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .team-avatar {
            width: 68px; height: 68px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.15rem; color: #fff;
            margin-bottom: 0.85rem; letter-spacing: 0.05em;
        }

        .team-name  { font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 0.35rem; }
        .team-role  { display: inline-block; font-size: 0.74rem; font-weight: 600; padding: 0.25rem 0.75rem; border-radius: 999px; margin-bottom: 1rem; }
        .team-hr    { width: 100%; border: none; border-top: 1px solid #f1f5f9; margin: 0 0 0.85rem; }

        .team-row   { display: flex; align-items: center; gap: 0.5rem; width: 100%; margin-bottom: 0.55rem; }

        .team-icon  { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .team-icon i { width: 15px; height: 15px; }

        .team-lbl   { display: block; font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #94a3b8; }
        .team-val   { display: block; font-size: 0.8rem; color: #475569; word-break: break-all; text-align: left; }
        .team-val a { color: inherit; text-decoration: none; }
        .team-val a:hover { text-decoration: underline; }
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

        <div class="about-right">

            <!-- Armandeep Singh -->
            <div class="team-card">
                <div class="team-avatar" style="background:#1e293b;">AS</div>
                <p class="team-name">Armandeep Singh</p>
                <span class="team-role" style="background:#f1f5f9;color:#1e293b;">Lead Developer</span>
                <hr class="team-hr">
                <div class="team-row">
                    <div class="team-icon" style="background:#f1f5f9;">
                        <i data-lucide="mail" style="color:#475569;"></i>
                    </div>
                    <div>
                        <span class="team-lbl">Email</span>
                        <span class="team-val"><a href="mailto:armandeep0088@gmail.com">armandeep0088@gmail.com</a></span>
                    </div>
                </div>
                <div class="team-row">
                    <div class="team-icon" style="background:#f1f5f9;">
                        <i data-lucide="graduation-cap" style="color:#475569;"></i>
                    </div>
                    <div>
                        <span class="team-lbl">Education</span>
                        <span class="team-val">CGC University</span>
                    </div>
                </div>
            </div>

            <!-- Anurag Shukla -->
            <div class="team-card">
                <div class="team-avatar" style="background:#2563eb;">AN</div>
                <p class="team-name">Anurag Shukla</p>
                <span class="team-role" style="background:#dbeafe;color:#1d4ed8;">QA Tester</span>
                <hr class="team-hr">
                <div class="team-row">
                    <div class="team-icon" style="background:#dbeafe;">
                        <i data-lucide="mail" style="color:#2563eb;"></i>
                    </div>
                    <div>
                        <span class="team-lbl">Email</span>
                        <span class="team-val"><a href="mailto:anuragshukla0005@gmail.com">anuragshukla0005@gmail.com</a></span>
                    </div>
                </div>
                <div class="team-row">
                    <div class="team-icon" style="background:#dbeafe;">
                        <i data-lucide="graduation-cap" style="color:#2563eb;"></i>
                    </div>
                    <div>
                        <span class="team-lbl">Education</span>
                        <span class="team-val">CGC University</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>
