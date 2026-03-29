<?php
require 'config.php';

// Redirect to dashboard if already logged in
if (defined('NC_USER')) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';
    
    if (!empty($user) && !empty($pass)) {
        // Test credentials against Nextcloud WebDAV
        $url = rtrim(NC_SERVER, '/') . '/remote.php/dav/';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $pass);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PROPFIND");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Depth: 0']); 

        // --- ADDED DEBUGGING ---
        // We need to capture the raw response and any cURL errors
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch); 
        curl_close($ch);

        // 207 Multi-Status or 200 OK means successful authentication
        if ($httpcode === 207 || $httpcode === 200) {
            $_SESSION['NC_USER'] = $user;
            $_SESSION['NC_PASS'] = $pass;
            header("Location: index.php");
            exit;
        } else {
            // Build a highly detailed error message
            $error = "<strong>Login Failed.</strong><br>HTTP Code: $httpcode";
            
            if ($curl_error) {
                $error .= "<br>cURL Error: " . htmlspecialchars($curl_error);
            }
            if ($response) {
                // Show a snippet of the Nextcloud response to sPHP_CLI_SERVER_WORKERS=10 php -S localhost:8000ee if it's an XML error or an HTML login page redirect
                $cleanResponse = substr(strip_tags($response), 0, 150);
                $error .= "<br>Response Preview: " . htmlspecialchars($cleanResponse);
            }
        }
    } else {
        $error = 'Please fill in both fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nextcloud Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen relative overflow-hidden">
    
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-[#2a366b] rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl opacity-30 animate-blob animation-delay-2000"></div>

    <div class="w-full max-w-md p-8 bg-white/80 backdrop-blur-xl rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.08)] border border-white relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-[#2a366b] rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg text-white">
                <i class="fas fa-cloud text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Welcome Back</h1>
            <p class="text-sm text-gray-500 mt-2">Sign in to your Nextcloud workspace</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-600 rounded-xl text-sm font-semibold flex items-center gap-3">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">Nextcloud Username</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="username" required class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-[#2a366b] focus:ring-1 focus:ring-[#2a366b] bg-gray-50/50 focus:bg-white transition-all text-sm" placeholder="Enter your username">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-600 mb-2">App Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" name="password" required class="w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:border-[#2a366b] focus:ring-1 focus:ring-[#2a366b] bg-gray-50/50 focus:bg-white transition-all text-sm" placeholder="••••••••">
                </div>
                <p class="text-xs text-gray-400 mt-2 text-right">Use an App Password for better security.</p>
            </div>

            <button type="submit" class="w-full py-3.5 bg-[#2a366b] text-white rounded-xl font-bold text-sm hover:bg-opacity-90 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 mt-4">
                Sign In
            </button>
        </form>
    </div>
</body>
</html>