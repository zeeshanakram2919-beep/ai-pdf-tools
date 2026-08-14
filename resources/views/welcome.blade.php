<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AI PDF Tools - Free Online PDF Tools</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #111827;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 25px;
            font-weight: 800;
            color: #2563eb;
        }

        .logo span {
            color: #111827;
        }

        .nav-text {
            color: #6b7280;
            font-size: 14px;
            font-weight: 600;
        }

        /* =========================
           HERO
        ========================= */

        .hero {
            background: #ffffff;
            text-align: center;
            padding: 80px 20px 70px;
            border-bottom: 1px solid #eef0f4;
        }

        .hero-badge {
            display: inline-block;
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero h1 {
            font-size: 50px;
            line-height: 1.15;
            color: #111827;
            margin-bottom: 20px;
            font-weight: 800;
        }

        .hero h1 span {
            color: #2563eb;
        }

        .hero p {
            max-width: 700px;
            margin: auto;
            font-size: 18px;
            line-height: 1.7;
            color: #6b7280;
        }

        /* =========================
           TOOLS SECTION
        ========================= */

        .tools-section {
            max-width: 1200px;
            margin: 55px auto;
            padding: 0 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 35px;
        }

        .section-title h2 {
            font-size: 32px;
            color: #111827;
            margin-bottom: 8px;
        }

        .section-title p {
            color: #6b7280;
            font-size: 15px;
        }

        /* =========================
           GRID
        ========================= */

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

        /* =========================
           TOOL CARD
        ========================= */

        .tool-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 30px 24px;
            text-align: center;
            transition: all 0.2s ease;
        }

        .tool-card:hover {
            transform: translateY(-5px);
            border-color: #2563eb;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        .tool-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 18px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #eff6ff;
            border-radius: 16px;
            font-size: 34px;
        }

        .tool-card h3 {
            font-size: 20px;
            color: #111827;
            margin-bottom: 10px;
        }

        .tool-card p {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
            min-height: 45px;
            margin-bottom: 22px;
        }

        .tool-button {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            padding: 11px 22px;
            border-radius: 9px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            transition: 0.2s;
        }

        .tool-button:hover {
            background: #1d4ed8;
        }

        /* =========================
           FEATURES
        ========================= */

        .features {
            max-width: 1050px;
            margin: 70px auto;
            padding: 0 20px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .feature {
            text-align: center;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 25px 20px;
        }

        .feature-icon {
            font-size: 30px;
            margin-bottom: 12px;
        }

        .feature h3 {
            font-size: 17px;
            margin-bottom: 7px;
        }

        .feature p {
            color: #6b7280;
            font-size: 13px;
            line-height: 1.5;
        }

        /* =========================
           FOOTER
        ========================= */

        .footer {
            background: #111827;
            color: #9ca3af;
            text-align: center;
            padding: 38px 20px;
            margin-top: 80px;
        }

        .footer strong {
            color: #ffffff;
        }

        .footer-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .footer p {
            font-size: 13px;
        }

        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 900px) {

            .tools-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero h1 {
                font-size: 42px;
            }
        }

        @media (max-width: 600px) {

            .navbar {
                padding: 15px 5%;
            }

            .nav-text {
                display: none;
            }

            .hero {
                padding: 55px 20px 50px;
            }

            .hero h1 {
                font-size: 34px;
            }

            .hero p {
                font-size: 16px;
            }

            .tools-section {
                margin-top: 40px;
            }

            .section-title h2 {
                font-size: 27px;
            }

            .tools-grid {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .tool-card {
                padding: 28px 20px;
            }
        }
    </style>
</head>

<body>

<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <div class="logo">
        AI <span>PDF Tools</span>
    </div>

    <div class="nav-text">
        Free Online PDF Tools
    </div>

</nav>


<!-- =========================
     HERO
========================= -->

<section class="hero">

    <div class="hero-badge">
        ✨ Simple • Fast • Free
    </div>

    <h1>
        Free <span>PDF & Document</span><br>
        Tools
    </h1>

    <p>
        Manage your PDF files quickly and easily.
        Merge, split, compress, convert, rotate and
        watermark your documents — all in one place.
    </p>

</section>


<!-- =========================
     TOOLS
========================= -->

<section class="tools-section">

    <div class="section-title">

        <h2>
            PDF Tools
        </h2>

        <p>
            Choose a tool to get started
        </p>

    </div>


    <div class="tools-grid">


        <!-- MERGE -->

        <div class="tool-card">

            <div class="tool-icon">
                📑
            </div>

            <h3>
                Merge PDF
            </h3>

            <p>
                Combine multiple PDF files into one document.
            </p>

            <a
                href="{{ route('merge-pdf') }}"
                class="tool-button"
            >
                Open Tool
            </a>

        </div>


        <!-- SPLIT -->

        <div class="tool-card">

            <div class="tool-icon">
                ✂️
            </div>

            <h3>
                Split PDF
            </h3>

            <p>
                Split a PDF into separate pages or files.
            </p>

            <a
                href="{{ route('split-pdf') }}"
                class="tool-button"
            >
                Open Tool
            </a>

        </div>


        <!-- COMPRESS -->

        <div class="tool-card">

            <div class="tool-icon">
                🗜️
            </div>

            <h3>
                Compress PDF
            </h3>

            <p>
                Reduce PDF file size while keeping good quality.
            </p>

            <a
                href="{{ route('compress-pdf') }}"
                class="tool-button"
            >
                Open Tool
            </a>

        </div>


        <!-- JPG TO PDF -->

        <div class="tool-card">

            <div class="tool-icon">
                🖼️
            </div>

            <h3>
                JPG → PDF
            </h3>

            <p>
                Convert your JPG images into a PDF document.
            </p>

            <a
                href="{{ route('jpg-to-pdf') }}"
                class="tool-button"
            >
                Open Tool
            </a>

        </div>


        <!-- PDF TO JPG -->

        <div class="tool-card">

            <div class="tool-icon">
                📄
            </div>

            <h3>
                PDF → JPG
            </h3>

            <p>
                Convert PDF pages into high-quality JPG images.
            </p>

            <a
                href="{{ route('pdf-to-jpg') }}"
                class="tool-button"
            >
                Open Tool
            </a>

        </div>


        <!-- ROTATE PDF -->

        <div class="tool-card">

            <div class="tool-icon">
                🔄
            </div>

            <h3>
                Rotate PDF
            </h3>

            <p>
                Rotate PDF pages and save the corrected document.
            </p>

            <a
                href="{{ route('rotate-pdf') }}"
                class="tool-button"
            >
                Open Tool
            </a>

        </div>


        <!-- WATERMARK PDF -->

        <div class="tool-card">

            <div class="tool-icon">
                💧
            </div>

            <h3>
                Watermark PDF
            </h3>

            <p>
                Add a professional watermark to every PDF page.
            </p>

            <a
                href="{{ route('watermark-pdf') }}"
                class="tool-button"
            >
                Open Tool
            </a>

        </div>


    </div>

</section>


<!-- =========================
     FEATURES
========================= -->

<section class="features">

    <div class="section-title">

        <h2>
            Why Use AI PDF Tools?
        </h2>

        <p>
            Everything you need for everyday PDF tasks.
        </p>

    </div>


    <div class="features-grid">


        <div class="feature">

            <div class="feature-icon">
                ⚡
            </div>

            <h3>
                Fast Processing
            </h3>

            <p>
                Process your PDF files quickly without unnecessary steps.
            </p>

        </div>


        <div class="feature">

            <div class="feature-icon">
                🔒
            </div>

            <h3>
                Simple & Secure
            </h3>

            <p>
                Easy-to-use tools designed for everyday document work.
            </p>

        </div>


        <div class="feature">

            <div class="feature-icon">
                💰
            </div>

            <h3>
                Free to Use
            </h3>

            <p>
                Use the available PDF tools without expensive software.
            </p>

        </div>


    </div>

</section>


<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <div class="footer-title">
        AI PDF Tools
    </div>

    <p>
        © 2026
        <strong>AI PDF Tools</strong>.
        Free PDF & Document Tools.
    </p>

</footer>


</body>
</html>