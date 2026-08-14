<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Compress PDF - AI PDF Tools</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f7f9fc;
            color: #1f2937;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 18px 7%;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
            max-width: 700px;
            margin: 70px auto;
            padding: 20px;
        }

        .card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .icon {
            font-size: 55px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 34px;
            color: #111827;
            margin-bottom: 12px;
        }

        .description {
            color: #6b7280;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .file-input {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            background: #f9fafb;
        }

        .file-input input {
            width: 100%;
            padding: 12px;
        }

        .button {
            display: inline-block;
            width: 100%;
            background: #2563eb;
            color: white;
            border: none;
            padding: 13px 20px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .button:hover {
            background: #1d4ed8;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }

        .success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            color: #2563eb;
            text-decoration: none;
        }

        .back:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            padding: 35px 20px;
            background: #111827;
            color: #9ca3af;
            margin-top: 70px;
        }

        .footer strong {
            color: white;
        }

        @media (max-width: 600px) {
            .container {
                margin: 30px auto;
            }

            .card {
                padding: 25px 20px;
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

        <div>
            Free Online Tools
        </div>
    </nav>


    <div class="container">

        <div class="card">

            <div class="icon">🗜️</div>

            <h1>Compress PDF</h1>

            <p class="description">
                Upload your PDF and reduce its file size while
                maintaining good document quality.
            </p>


            @if ($errors->any())
                <div class="error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif


            @if (session('success'))
                <div class="success">
                    {{ session('success') }}
                </div>
            @endif


            <form action="{{ route('compress-pdf.process') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="file-input">

                    <input
                        type="file"
                        name="pdf"
                        accept=".pdf,application/pdf"
                        required
                    >

                </div>


                <button type="submit" class="button">
                    Compress PDF
                </button>

            </form>


            <a href="{{ url('/') }}" class="back">
                ← Back to PDF Tools
            </a>

        </div>

    </div>


    <footer class="footer">

        <p>
            © 2026 <strong>AI PDF Tools</strong>.
            Free PDF & Document Tools.
        </p>

    </footer>

</body>
</html>