<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>AI PDF Tools - Free Online PDF Tools</title>

    <meta
        name="description"
        content="Free online PDF tools to merge, split, compress, rotate, watermark, convert and manage PDF files easily."
    >

    <meta name="robots" content="index, follow">

    <link
        rel="canonical"
        href="{{ url('/') }}"
    >

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f9fc;
            color: #111827;
            line-height: 1.6;
        }

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 7%;
        }

        .navbar-inner {
            max-width: 1200px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #2563eb;
            text-decoration: none;
        }

        .logo span {
            color: #111827;
        }

        .nav-link {
            color: #374151;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .nav-link:hover {
            color: #2563eb;
        }

        .hero {
            max-width: 1000px;
            margin: 70px auto 50px;
            padding: 20px;
            text-align: center;
        }

        .hero h1 {
            font-size: 48px;
            line-height: 1.15;
            margin-bottom: 18px;
            color: #111827;
        }

        .hero h1 span {
            color: #2563eb;
        }

        .hero p {
            max-width: 720px;
            margin: auto;
            color: #6b7280;
            font-size: 18px;
        }

        .tools-container {
            max-width: 1200px;
            margin: 0 auto 70px;
            padding: 20px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 30px;
        }

        .section-title h2 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .section-title p {
            color: #6b7280;
        }

        .tools-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }

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
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.06);
        }

        .tool-icon {
            font-size: 42px;
            margin-bottom: 15px;
        }

        .tool-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .tool-card p {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .tool-button {
            display: inline-block;
            background: #2563eb;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        .tool-button:hover {
            background: #1d4ed8;
        }

        .info-section {
            max-width: 900px;
            margin: 0 auto 70px;
            padding: 20px;
        }

        .info-box {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 35px;
        }

        .info-box h2 {
            font-size: 27px;
            margin-bottom: 15px;
        }

        .info-box h3 {
            font-size: 19px;
            margin-top: 25px;
            margin-bottom: 8px;
        }

        .info-box p {
            color: #6b7280;
            margin-bottom: 12px;
        }

        .info-box ul {
            margin-left: 20px;
            color: #6b7280;
        }

        .info-box li {
            margin-bottom: 7px;
        }

        .footer {
            background: #111827;
            color: #9ca3af;
            text-align: center;
            padding: 40px 20px;
        }

        .footer-title {
            color: #ffffff;
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .footer p {
            font-size: 13px;
        }

        .copyright {
            margin-top: 10px;
            font-size: 13px;
            color: #9ca3af;
        }

        @media (max-width: 800px) {

            .tools-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .hero h1 {
                font-size: 38px;
            }
        }

        @media (max-width: 600px) {

            .navbar {
                padding: 15px 5%;
            }

            .hero {
                margin-top: 40px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .hero p {
                font-size: 16px;
            }

            .tools-grid {
                grid-template-columns: 1fr;
            }

            .info-box {
                padding: 25px 20px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">

    <div class="navbar-inner">

        <a href="{{ url('/') }}" class="logo">
            AI PDF <span>Tools</span>
        </a>

        <a href="{{ url('/merge-pdf') }}" class="nav-link">
            Merge PDF
        </a>

    </div>

</nav>

<section class="hero">

    <h1>
        Powerful <span>PDF Tools</span><br>
        Made Simple
    </h1>

    <p>
        Free online tools to merge, split, compress, rotate, watermark and
        convert your PDF files quickly and easily.
    </p>

</section>

<section class="tools-container">

    <div class="section-title">

        <h2>Our PDF Tools</h2>

        <p>
            Choose a tool and get your PDF work done in seconds.
        </p>

    </div>

    <div class="tools-grid">

        {{-- MERGE PDF --}}
        <div class="tool-card">

            <div class="tool-icon">📑</div>

            <h3>Merge PDF</h3>

            <p>
                Combine multiple PDF files into one document.
            </p>

            <a
                href="{{ url('/merge-pdf') }}"
                class="tool-button"
            >
                Use Tool
            </a>

        </div>

        {{-- SPLIT PDF --}}
        <div class="tool-card">

            <div class="tool-icon">✂️</div>

            <h3>Split PDF</h3>

            <p>
                Split PDF documents into separate files.
            </p>

            <a
                href="{{ url('/split-pdf') }}"
                class="tool-button"
            >
                Use Tool
            </a>

        </div>

        {{-- COMPRESS PDF --}}
        <div class="tool-card">

            <div class="tool-icon">🗜️</div>

            <h3>Compress PDF</h3>

            <p>
                Reduce PDF file size while keeping documents useful.
            </p>

            <a
                href="{{ url('/compress-pdf') }}"
                class="tool-button"
            >
                Use Tool
            </a>

        </div>

        {{-- ROTATE PDF --}}
        <div class="tool-card">

            <div class="tool-icon">🔄</div>

            <h3>Rotate PDF</h3>

            <p>
                Rotate PDF pages to the correct orientation.
            </p>

            <a
                href="{{ url('/rotate-pdf') }}"
                class="tool-button"
            >
                Use Tool
            </a>

        </div>

        {{-- JPG TO PDF --}}
        <div class="tool-card">

            <div class="tool-icon">🖼️</div>

            <h3>JPG to PDF</h3>

            <p>
                Convert JPG images into a PDF document.
            </p>

            <a
                href="{{ url('/jpg-to-pdf') }}"
                class="tool-button"
            >
                Use Tool
            </a>

        </div>

        {{-- PDF TO JPG --}}
        <div class="tool-card">

            <div class="tool-icon">📄</div>

            <h3>PDF to JPG</h3>

            <p>
                Convert PDF pages into JPG images.
            </p>

            <a
                href="{{ url('/pdf-to-jpg') }}"
                class="tool-button"
            >
                Use Tool
            </a>

        </div>

        {{-- WATERMARK PDF --}}
        <div class="tool-card">

            <div class="tool-icon">💧</div>

            <h3>Watermark PDF</h3>

            <p>
                Add a custom text watermark to your PDF pages.
            </p>

            <a
                href="{{ url('/watermark-pdf') }}"
                class="tool-button"
            >
                Use Tool
            </a>

        </div>

    </div>

</section>

<section class="info-section">

    <div class="info-box">

        <h2>Free Online PDF Tools</h2>

        <p>
            AI PDF Tools provides simple online PDF utilities for everyday
            document work. You can manage your PDF files directly from your
            browser without installing complicated software.
        </p>

        <h3>What You Can Do</h3>

        <ul>
            <li>Merge multiple PDF files into one.</li>
            <li>Split PDF documents.</li>
            <li>Compress PDF files.</li>
            <li>Rotate PDF pages.</li>
            <li>Add watermarks to PDF documents.</li>
            <li>Convert images to PDF.</li>
            <li>Convert PDF pages to images.</li>
        </ul>

    </div>

</section>

<footer class="footer">

    <div class="footer-title">
        AI PDF Tools
    </div>

    <p>
        Free online PDF tools for everyday document needs.
    </p>

    <p class="copyright">
        © {{ date('Y') }} AI PDF Tools. All rights reserved.
    </p>

</footer>

</body>
</html>