<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>JPG to PDF - AI PDF Tools</title>

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
            margin-bottom: 20px;
            background: #f9fafb;
        }

        .file-input input {
            width: 100%;
            padding: 12px;
        }

        .selected-files {
            display: none;
            text-align: left;
            background: #f3f4f6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .selected-files strong {
            display: block;
            margin-bottom: 8px;
        }

        .selected-files ul {
            padding-left: 20px;
            color: #6b7280;
            font-size: 14px;
            line-height: 1.7;
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

        .button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
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

    <!-- Navbar -->
    <nav class="navbar">

        <div class="logo">
            AI <span>PDF Tools</span>
        </div>

        <div>
            Free Online Tools
        </div>

    </nav>


    <!-- Main -->
    <div class="container">

        <div class="card">

            <div class="icon">
                🖼️
            </div>

            <h1>
                JPG → PDF
            </h1>

            <p class="description">
                Upload one or multiple JPG images and convert them
                into a single PDF document.
            </p>


            <!-- Errors -->
            @if ($errors->any())

                <div class="error">

                    @foreach ($errors->all() as $error)

                        <div>
                            {{ $error }}
                        </div>

                    @endforeach

                </div>

            @endif


            <!-- Form -->
            <form
                action="{{ route('jpg-to-pdf.process') }}"
                method="POST"
                enctype="multipart/form-data"
                id="jpgToPdfForm"
            >

                @csrf


                <!-- File Upload -->
                <div class="file-input">

                    <input
                        type="file"
                        name="images[]"
                        id="images"
                        accept=".jpg,.jpeg,image/jpeg"
                        multiple
                        required
                    >

                </div>


                <!-- Selected Files -->
                <div
                    class="selected-files"
                    id="selectedFiles"
                >

                    <strong>
                        Selected Images:
                    </strong>

                    <ul id="fileList"></ul>

                </div>


                <!-- Button -->
                <button
                    type="submit"
                    class="button"
                    id="convertButton"
                >
                    Convert JPG to PDF
                </button>

            </form>


            <!-- Back -->
            <a
                href="{{ url('/') }}"
                class="back"
            >
                ← Back to PDF Tools
            </a>

        </div>

    </div>


    <!-- Footer -->
    <footer class="footer">

        <p>
            © 2026
            <strong>AI PDF Tools</strong>.
            Free PDF & Document Tools.
        </p>

    </footer>


    <!-- JavaScript -->
    <script>

        const imageInput = document.getElementById('images');
        const selectedFiles = document.getElementById('selectedFiles');
        const fileList = document.getElementById('fileList');
        const convertButton = document.getElementById('convertButton');

        imageInput.addEventListener('change', function () {

            fileList.innerHTML = '';

            if (this.files.length === 0) {

                selectedFiles.style.display = 'none';

                return;
            }


            selectedFiles.style.display = 'block';


            for (let i = 0; i < this.files.length; i++) {

                const li = document.createElement('li');

                li.textContent = this.files[i].name;

                fileList.appendChild(li);
            }


            convertButton.textContent =
                'Convert ' + this.files.length + ' JPG to PDF';

        });

    </script>

</body>
</html>