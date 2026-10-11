<?php
// admin.php
session_start();
require_once 'db.php';

// Simple Authentication
if (isset($_POST['login'])) {
    if ($_POST['password'] === 'admin123') {
        $_SESSION['admin_logged'] = true;
    } else {
        $error = "Invalid Password!";
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Mighty Church</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-800 p-8 rounded-2xl max-w-md w-full border border-slate-700 shadow-2xl">
        <h2 class="text-2xl font-bold text-amber-500 mb-6 text-center">Admin Portal Login</h2>
        <?php if (isset($error)): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded mb-4 text-sm text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Password</label>
                <input type="password" name="password" required class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3 text-white focus:outline-none focus:border-amber-500">
            </div>
            <button type="submit" name="login" class="w-full bg-amber-500 text-slate-900 font-bold py-3 rounded-lg hover:bg-amber-400 transition">Enter Admin Panel</button>
        </form>
    </div>
</body>
</html>
<?php 
exit; 
endif;

// Fetch Submissions
$gift_cards = $conn->query("SELECT * FROM gift_cards ORDER BY id DESC");
$proofs = $conn->query("SELECT * FROM payment_proofs ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mighty Church - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8 border-b border-slate-800 pb-4">
            <div>
                <h1 class="text-3xl font-bold text-amber-500">Mighty Church Admin Portal</h1>
                <p class="text-slate-400 text-sm">Review Gift Cards & Payment Verification Screenshots</p>
            </div>
            <a href="?logout=1" class="bg-red-600/20 border border-red-500 text-red-400 hover:bg-red-600 hover:text-white px-4 py-2 rounded-lg transition font-medium text-sm">Logout</a>
        </div>

        <!-- Gift Cards Table -->
        <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 mb-10 shadow-xl">
            <h2 class="text-xl font-bold text-amber-400 mb-4 flex items-center gap-2">
                🎁 Gift Card Submissions (For Kids' Clothing)
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                            <th class="p-3">ID</th>
                            <th class="p-3">Donor Email</th>
                            <th class="p-3">Card Type</th>
                            <th class="p-3">Code / PIN</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Proof Image</th>
                            <th class="p-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700 text-sm">
                        <?php if ($gift_cards->num_rows > 0): ?>
                            <?php while ($row = $gift_cards->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-700/50">
                                    <td class="p-3 font-mono text-slate-400">#<?= $row['id'] ?></td>
                                    <td class="p-3 font-medium"><?= htmlspecialchars($row['donor_email']) ?></td>
                                    <td class="p-3">
                                        <span class="bg-slate-900 px-2.5 py-1 rounded-full text-xs font-semibold text-amber-400 border border-slate-700">
                                            <?= htmlspecialchars($row['card_type']) ?>
                                        </span>
                                    </td>
                                    <td class="p-3 font-mono text-emerald-400">
                                        <div>Code: <?= htmlspecialchars($row['card_code']) ?></div>
                                        <?php if ($row['card_pin']): ?>
                                            <div class="text-xs text-slate-400">PIN: <?= htmlspecialchars($row['card_pin']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 font-semibold text-white">$<?= number_format($row['amount'], 2) ?></td>
                                    <td class="p-3">
                                        <?php if ($row['image_path']): ?>
                                            <a href="<?= htmlspecialchars($row['image_path']) ?>" target="_blank" class="text-blue-400 hover:underline flex items-center gap-1 text-xs">
                                                View Screenshot
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-500 text-xs">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-3 text-slate-400 text-xs"><?= $row['created_at'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="p-4 text-center text-slate-500">No gift card entries found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Proofs Table -->
        <div class="bg-slate-800 rounded-2xl p-6 border border-slate-700 shadow-xl">
            <h2 class="text-xl font-bold text-amber-400 mb-4 flex items-center gap-2">
                💳 Direct Payment Proof Screenshots (Zelle, Cash App, Chime, etc.)
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 text-xs uppercase">
                            <th class="p-3">Receipt #</th>
                            <th class="p-3">Donor</th>
                            <th class="p-3">Method</th>
                            <th class="p-3">Amount</th>
                            <th class="p-3">Screenshot Proof</th>
                            <th class="p-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700 text-sm">
                        <?php if ($proofs->num_rows > 0): ?>
                            <?php while ($row = $proofs->fetch_assoc()): ?>
                                <tr class="hover:bg-slate-700/50">
                                    <td class="p-3 font-mono text-amber-400"><?= htmlspecialchars($row['receipt_number']) ?></td>
                                    <td class="p-3">
                                        <div class="font-medium"><?= htmlspecialchars($row['donor_name']) ?></div>
                                        <div class="text-xs text-slate-400"><?= htmlspecialchars($row['donor_email']) ?></div>
                                    </td>
                                    <td class="p-3 font-semibold text-slate-200"><?= htmlspecialchars($row['payment_method']) ?></td>
                                    <td class="p-3 font-bold text-emerald-400">$<?= number_format($row['amount'], 2) ?></td>
                                    <td class="p-3">
                                        <a href="<?= htmlspecialchars($row['proof_image']) ?>" target="_blank" class="text-blue-400 hover:underline text-xs">
                                            View Uploaded Proof
                                        </a>
                                    </td>
                                    <td class="p-3 text-slate-400 text-xs"><?= $row['created_at'] ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="p-4 text-center text-slate-500">No payment proofs uploaded yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>