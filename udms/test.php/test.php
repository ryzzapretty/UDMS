<?php
// UDMS WEBSITE DIAGNOSTIC
// St. Clare College of Caloocan

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function checkFile($path)
{
    $exists = file_exists(__DIR__ . '/' . $path);

    echo '<p>';
    echo $exists ? '✅ ' : '❌ ';
    echo htmlspecialchars($path);
    echo $exists ? ' — Found' : ' — Missing';
    echo '</p>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UDMS Website Diagnostic</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 15px;
            background: linear-gradient(135deg,
                    #05265d,
                    #0868d9);
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }

        .container {
            max-width: 650px;
            margin: 40px auto;
            background: white;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 10px 30px #001a4a55;
        }

        h1 {
            color: #05265d;
            text-align: center;
            font-size: 28px;
        }

        h2 {
            color: #05265d;
            font-size: 20px;
            margin-top: 25px;
        }

        p {
            color: #263d60;
            line-height: 1.7;
        }

        .success {
            background: #e2f7e8;
            border-left: 5px solid #20964b;
            padding: 15px;
            border-radius: 8px;
        }

        .info {
            background: #eaf3ff;
            padding: 15px;
            border-radius: 8px;
        }

        a {
            display: block;
            background: #05265d;
            color: white;
            text-align: center;
            padding: 14px;
            margin-top: 12px;
            border-radius: 10px;
            text-decoration: none;
        }

        a:hover {
            background: #0868d9;
        }
    </style>
</head>

<body>

    <div class="container">

        <h1>UDMS System Diagnostic</h1>

        <p style="text-align:center;">
            St. Clare College of Caloocan
        </p>

        <div class="success">
            <strong>PHP is working!</strong>
            <p>
                Your server successfully executed
                this PHP diagnostic page.
            </p>
        </div>

        <h2>PHP Information</h2>

        <div class="info">
            <p>
                <strong>PHP Version:</strong>
                <?php echo htmlspecialchars(PHP_VERSION); ?>
            </p>

            <p>
                <strong>Project Directory:</strong>
                <?php echo htmlspecialchars(__DIR__); ?>
            </p>
        </div>

        <h2>UDMS File Check</h2>

        <?php
        checkFile('config.php');
        checkFile('index.php');
        checkFile('login.php');
        checkFile('register.php');
        checkFile('dashboard.php');
        checkFile('includes/header.php');
        checkFile('includes/footer.php');
        checkFile('assets/udms-logo.png');
        checkFile('assets/St. Clare College Campus Banner.png');
        ?>

        <h2>Open UDMS Pages</h2>

        <a href="index.php">
            Open Landing Page
        </a>

        <a href="login.php">
            Open Login Page
        </a>

        <a href="register.php">
            Open Registration Page
        </a>

        <h2>Next Step</h2>

        <div class="info">
            <p>
                If this diagnostic page opens but your
                landing page remains blank, the issue
                is likely in your UDMS application code.
            </p>

            <p>
                Send the error message or diagnostic
                results so the affected file can be fixed.
            </p>
        </div>

    </div>

</body>

</html>