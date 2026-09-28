<?php
session_start();
require "config.php";

// Admin Reply Save Cheyyum Logic
if (isset($_POST['send_reply'])) {
    $msg_id = intval($_POST['msg_id']);
    $reply_text = htmlspecialchars(trim($_POST['reply']));

    if (!empty($reply_text)) {
        $stmt = $conn->prepare("UPDATE contact_messages SET reply = ? WHERE id = ?");
        $stmt->bind_param("si", $reply_text, $msg_id);
        $stmt->execute();
        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM contact_messages ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Messages - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background: #1B263B; color: #ffffff; min-height: 100vh; padding: 40px 5%; }
        .container { max-width: 1200px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: rgba(13, 19, 29, 0.8); padding: 20px 30px; border-radius: 16px; border: 1px solid rgba(169, 162, 39, 0.3); }
        .page-header h2 { font-family: 'Cinzel', serif; color: #A9A227; font-size: 26px; }
        .btn-back { background: #A9A227; color: #1B263B; text-decoration: none; padding: 8px 18px; border-radius: 20px; font-weight: 700; font-size: 13px; }
        .table-card { background: rgba(13, 19, 29, 0.75); backdrop-filter: blur(15px); border-radius: 20px; border: 1px solid rgba(169, 162, 39, 0.3); box-shadow: 0 15px 35px rgba(0,0,0,0.5); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #A9A227; color: #1B263B; padding: 16px 20px; font-weight: 700; font-size: 14px; text-transform: uppercase; }
        td { padding: 16px 20px; border-bottom: 1px solid rgba(169, 162, 39, 0.15); color: #cbd5e1; font-size: 14px; vertical-align: top; }
        tr:hover { background: rgba(169, 162, 39, 0.08); }
        .badge-id { background: rgba(169, 162, 39, 0.2); color: #A9A227; padding: 4px 10px; border-radius: 12px; font-weight: 600; font-size: 12px; }
        .email-link { color: #A9A227; text-decoration: none; font-weight: 500; }
        .email-link:hover { text-decoration: underline; }
        .reply-box { display: flex; gap: 8px; margin-top: 8px; }
        .reply-input { background: rgba(255,255,255,0.08); border: 1px solid rgba(169,162,39,0.4); color: #fff; padding: 6px 12px; border-radius: 8px; outline: none; font-size: 13px; flex: 1; }
        .btn-reply { background: #A9A227; color: #1B263B; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 12px; }
        .reply-text { background: rgba(169,162,39,0.15); border-left: 3px solid #A9A227; padding: 6px 10px; border-radius: 4px; margin-top: 6px; color: #fff; font-size: 13px; }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h2><i class="fa-solid fa-envelope-open-text"></i> Customer Messages</h2>
        <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message & Admin Reply</th>
                    <th>Received Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge-id">#<?= $row['id'] ?></span></td>
                            <td style="font-weight: 600; color: #fff;"><?= htmlspecialchars($row['name']) ?></td>
                            <td>
                                <!-- Click to send Email direct-aah -->
                                <a class="email-link" href="mailto:<?= htmlspecialchars($row['email']) ?>?subject=Reply%20from%20Charr%20Furnitures">
                                    <i class="fa-solid fa-envelope"></i> <?= htmlspecialchars($row['email']) ?>
                                </a>
                            </td>
                            <td>
                                <strong>Message:</strong>
                                <div><?= htmlspecialchars($row['message']) ?></div>

                                <!-- Admin Reply Area -->
                                <?php if (!empty($row['reply'])): ?>
                                    <div class="reply-text">
                                        <strong><i class="fa-solid fa-reply"></i> Admin Reply:</strong> <?= htmlspecialchars($row['reply']) ?>
                                    </div>
                                <?php else: ?>
                                    <form method="POST" class="reply-box">
                                        <input type="hidden" name="msg_id" value="<?= $row['id'] ?>">
                                        <input type="text" name="reply" class="reply-input" placeholder="Type your reply..." required>
                                        <button type="submit" name="send_reply" class="btn-reply">Send</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px;"><?= date('d M Y, h:i A', strtotime($row['created_at'])) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #94a3b8;">
                            No messages received yet.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>