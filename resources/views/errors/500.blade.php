<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>500 — Maaf, ada kesalahan teknis</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        main {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 2.5rem 2rem;
            max-width: 28rem;
            text-align: center;
        }

        .code {
            font-size: 3.5rem;
            font-weight: 800;
            color: #00236f;
            line-height: 1;
        }

        h1 {
            font-size: 1.25rem;
            margin-top: 0.75rem;
        }

        p {
            margin-top: 0.75rem;
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        a {
            display: inline-block;
            margin-top: 1.5rem;
            background: #00236f;
            color: #ffffff;
            text-decoration: none;
            padding: 0.6rem 1.4rem;
            border-radius: 0.6rem;
            font-weight: 600;
        }

        a:hover { background: #1e3a5f; }
    </style>
</head>
<body>
    <main>
        <p class="code">500</p>
        <h1>Maaf, ada kesalahan teknis</h1>
        <p>Terjadi kesalahan yang tidak terduga di sisi server. Tim kami akan segera memeriksanya — silakan coba lagi beberapa saat lagi.</p>
        <a href="{{ url('/') }}">Kembali ke Beranda</a>
    </main>
</body>
</html>
