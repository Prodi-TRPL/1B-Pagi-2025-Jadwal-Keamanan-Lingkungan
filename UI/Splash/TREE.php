<?php
$go = $_GET['go'] ?? '../Error/NoRole.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>TREE</title>
    <link rel="icon" type="image/png" href="../../Asset/Image/Logo.png">
    <link rel="stylesheet" href="../../CSS/Main.css" />
    <style>
        * {
            user-select: none
        }

        html,
        body {
            margin: 0;
            width: 100%;
            height: 100%;
            background: var(--Text4);
            overflow: hidden;
            font-family: "Inter", "sans";
        }

        .splash {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo {
            width: 400px;
            height: auto;
            transform: scale(2.2);
            filter: drop-shadow(0 0 18px rgba(0,255,156,.25));
            animation: zoomOutStay .9s cubic-bezier(.2, 1.4, .3, 1) forwards;
            will-change: transform;
        }

        .tag {
            position: absolute;
            bottom: 90px;
            font-size: 13px;
            letter-spacing: 3px;
            color: var(--Text2);
            opacity: .28;
        }




        @keyframes zoomOutStay {
            from {
                transform: scale(2.2);
            }

            to {
                transform: scale(1);
            }
        }
    </style>


    </style>
</head>

<body>

    <body>

        <div class="splash">
            <img src="../../Asset/Image/LogoText.png" class="logo" alt="TREE">
            <div class="tag">Teknologi Ronda Efektif & Efisien</div>
        </div>

        <audio id="ting" src="../../Asset/Sounds/Jingle.mp3"></audio>

        <script>
            setTimeout(() => { document.getElementById("ting").play(); }, 150);
            setTimeout(() => { window.location.href = "<?= $go ?>"; }, 2900);

        </script>

    </body>

</html>