<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Aratio - Portal de Líderes' ?></title>
    
    <!-- Google Fonts: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'aratio-blue': '#002244',
                        'aratio-navy': '#004488',
                        'aratio-gold': '#FFD700',
                    }
                }
            }
        }
    </script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Outfit', sans-serif; }
        [x-cloak] { display: none !important; }
        .hero-gradient {
            background: linear-gradient(135deg, #002244 0%, #004488 100%);
        }
        .btn-premium-gold {
            background: linear-gradient(135deg, #FFD700 0%, #DAA520 100%);
            color: #002244;
            font-weight: 800;
            transition: all 0.3s ease;
        }
        .btn-premium-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(218, 165, 32, 0.4);
        }
    </style>
</head>
<body class="bg-slate-50">
    
    <?= $content ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
