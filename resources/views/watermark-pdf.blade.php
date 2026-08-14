<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Watermark PDF - AI PDF Tools</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f6fb;
            color: #111827;
            min-height: 100vh;
        }

        .header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 25px;
        }

        .header-inner {
            max-width: 1100px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            color: #2563eb;
        }

        .logo span {
            color: #111827;
        }

        .header-right {
            color: #374151;
            font-size: 14px;
        }

        .container {
            width: 100%;
            max-width: 850px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .card {
            background: #ffffff;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid #e5e7eb;
        }

        .title {
            text-align: center;
            font-size: 32px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            font-size: 15px;
            color: #6b7280;
            margin-bottom: 35px;
        }

        .field {
            margin-bottom: 24px;
        }

        .field label {
            display: block;
            margin-bottom: 9px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }

        input[type="file"],
        input[type="text"] {
            width: 100%;
            padding: 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #ffffff;
            font-size: 15px;
        }

        input[type="file"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        /* FONT SIZE */

        .font-size-section {
            width: 100%;
            background: #eff6ff;
            border: 3px solid #2563eb;
            border-radius: 15px;
            padding: 22px;
            margin-bottom: 25px;
        }

        .font-size-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .font-size-heading label {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        .font-size-current {
            background: #2563eb;
            color: white;
            padding: 8px 13px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 800;
        }

        .font-size-select {
            display: block;
            width: 100%;
            height: 52px;
            padding: 0 15px;
            background: #ffffff;
            color: #111827;
            border: 2px solid #2563eb;
            border-radius: 10px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
        }

        .font-size-select:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        .font-size-help {
            margin-top: 9px;
            color: #4b5563;
            font-size: 13px;
        }

        /* DARKNESS */

        .opacity-section {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 15px;
            padding: 22px;
            margin-bottom: 25px;
        }

        .opacity-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .opacity-heading label {
            font-size: 17px;
            font-weight: 800;
        }

        .opacity-value {
            color: #2563eb;
            font-weight: 800;
            font-size: 16px;
        }

        .opacity-slider {
            width: 100%;
            height: 7px;
            cursor: pointer;
        }

        /* BUTTON */

        .submit-button {
            width: 100%;
            padding: 17px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: #ffffff;
            font-size: 17px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.2s;
        }

        .submit-button:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        /* BACK */

        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #2563eb;
            text-decoration: none;
            font-weight: 700;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* ERROR */

        .error-box {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        /* FOOTER */

        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            padding: 25px;
        }

        @media (max-width: 600px) {

            .header-inner {
                flex-direction: column;
                gap: 8px;
            }

            .container {
                margin: 25px auto;
            }

            .card {
                padding: 22px;
            }

            .title {
                font-size: 27px;
            }

            .font-size-heading {
                align-items: flex-start;
                gap: 12px;
            }

            .font-size-heading label {
                font-size: 18px;
            }

            .font-size-current {
                font-size: 13px;
            }
        }
    </style>
</head>

<body>

<header class="header">

    <div class="header-inner">

        <div class="logo">
            AI <span>PDF Tools</span>
        </div>

        <div class="header-right">
            Free Online Tools
        </div>

    </div>

</header>

<main class="container">

    <div class="card">

        <h1 class="title">
            Watermark PDF
        </h1>

        <p class="subtitle">
            Add a professional watermark to every page of your PDF.
        </p>

        @if ($errors->any())

            <div class="error-box">

                @foreach ($errors->all() as $error)

                    <div>{{ $error }}</div>

                @endforeach

            </div>

        @endif

        <form
            action="{{ route('watermark-pdf.process') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            <div class="field">

                <label for="pdf">
                    📄 Select PDF File
                </label>

                <input
                    type="file"
                    name="pdf"
                    id="pdf"
                    accept=".pdf,application/pdf"
                    required
                >

            </div>

            <div class="field">

                <label for="watermark">
                    ✏️ Watermark Text
                </label>

                <input
                    type="text"
                    name="watermark"
                    id="watermark"
                    placeholder="Enter watermark text"
                    maxlength="200"
                    required
                >

            </div>

            <div class="font-size-section">

                <div class="font-size-heading">

                    <label for="font_size">
                        🔠 Font Size
                    </label>

                    <span class="font-size-current">
                        <span id="fontSizeValue">30</span> pt
                    </span>

                </div>

                <select
                    name="font_size"
                    id="font_size"
                    class="font-size-select"
                    onchange="changeFontSize()"
                >

                    <option value="12">12 - Small</option>
                    <option value="16">16</option>
                    <option value="20">20</option>
                    <option value="24">24</option>
                    <option value="30" selected>30 - Normal</option>
                    <option value="40">40</option>
                    <option value="50">50 - Large</option>
                    <option value="60">60</option>
                    <option value="70">70</option>
                    <option value="80">80 - Very Large</option>
                    <option value="100">100 - Maximum</option>

                </select>

                <div class="font-size-help">
                    Select how large you want the watermark text to appear.
                </div>

            </div>

            <div class="opacity-section">

                <div class="opacity-heading">

                    <label for="opacity">
                        🌑 Darkness / Visibility
                    </label>

                    <span class="opacity-value">
                        <span id="opacityValue">45</span>
                    </span>

                </div>

                <input
                    type="range"
                    name="opacity"
                    id="opacity"
                    class="opacity-slider"
                    min="20"
                    max="255"
                    value="45"
                    step="5"
                    oninput="changeOpacity()"
                >

            </div>

            <button
                type="submit"
                class="submit-button"
            >
                🔵 Add Watermark
            </button>

        </form>

        <a
            href="{{ url('/') }}"
            class="back-link"
        >
            ← Back to PDF Tools
        </a>

    </div>

</main>

<footer class="footer">
    © 2026 AI PDF Tools. Free PDF & Document Tools.
</footer>

<script>

function changeFontSize() {

    const select = document.getElementById('font_size');

    const value = document.getElementById('fontSizeValue');

    value.textContent = select.value;

}

function changeOpacity() {

    const slider = document.getElementById('opacity');

    const value = document.getElementById('opacityValue');

    value.textContent = slider.value;

}

</script>

</body>
</html>