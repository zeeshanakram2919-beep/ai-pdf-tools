<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Merge PDF - AI PDF Tools</title>

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
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 7%;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }

        .logo span {
            color: #111827;
        }

        .container {
            max-width: 850px;
            margin: 60px auto;
            padding: 20px;
        }

        .back {
            display: inline-block;
            margin-bottom: 25px;
            color: #2563eb;
            text-decoration: none;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 40px;
            border: 1px solid #e5e7eb;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.04);
        }

        h1 {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .description {
            color: #6b7280;
            margin-bottom: 30px;
        }

        .upload-area {
            border: 2px dashed #2563eb;
            border-radius: 12px;
            padding: 45px 20px;
            background: #f8faff;
            margin-bottom: 25px;
        }

        .upload-icon {
            font-size: 50px;
            margin-bottom: 15px;
        }

        input[type="file"] {
            margin-top: 15px;
            max-width: 100%;
        }

        .button {
            border: none;
            background: #2563eb;
            color: white;
            padding: 13px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .note {
            margin-top: 20px;
            color: #6b7280;
            font-size: 13px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        @media (max-width: 600px) {
            .card {
                padding: 25px 18px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="logo">
        AI <span>PDF Tools</span>
    </div>
</nav>

<div class="container">

    <a href="/" class="back">← Back to Home</a>

    <div class="card">

        <h1>Merge PDF</h1>

        <p class="description">
            Combine multiple PDF files into one PDF document.
        </p>

        @if ($errors->any())
            <div class="error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="/merge-pdf" method="POST" enctype="multipart/form-data">

            @csrf

            <div class="upload-area">

                <div class="upload-icon">
                    📑
                </div>

                <h3>Select PDF Files</h3>

                <p>
                    Select at least 2 PDF files to merge.
                </p>

                <input
                    type="file"
                    name="pdfs[]"
                    accept=".pdf,application/pdf"
                    multiple
                    required
                >

            </div>

            <button type="submit" class="button">
                Merge PDF
            </button>

        </form>

        <div class="note">
            Maximum file size: 20 MB per PDF
        </div>

    </div>

</div>

</body>
</html>