<?php
/**
 * This is a PHP Telegram bot designed to display the original sender's information
 * from a forwarded message. It's super useful for finding out who originally sent a message!
 *
 * How to Use (It's simpler than you think!):
 * 1. Get a bot token from BotFather in Telegram. This is your bot's unique key.
 * 2. Replace 'YOUR_BOT_TOKEN' in the code below with your actual token. Don't share it!
 * 3. Upload this PHP file (e.g., bot.php) to your web server. Make sure it supports PHP.
 * 4. Set up your bot's webhook using the URL of your PHP file. This tells Telegram
 * where to send updates for your bot.
 * Example: https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=<YOUR_SERVER_URL>/bot.php
 * (e.g., https://api.telegram.org/bot123456:ABC-DEF1234ghIkl-zyx57W2E1uROr9u/setWebhook?url=https://yourdomain.com/bot.php)
 * 5. Forward messages to your bot, and it will magically reveal the sender's info!
 */

// Define your bot token here. This is absolutely critical for your bot to work!
// You get this token from BotFather when you create your bot.
$botToken = '8195277387:AAEva6qz9x9kKzTySK6y8StG0dOM-Gq6B_I'; // <<< IMPORTANT: Replace 'YOUR_BOT_TOKEN' with your actual bot token!

// Construct the base URL for the Telegram Bot API. All requests will go through here.
$telegramApiUrl = 'https://api.telegram.org/bot' . $botToken . '/';

// Get the raw JSON input sent by Telegram to your webhook.
// This input contains all the details about the message and its sender.
$update = json_decode(file_get_contents('php://input'), true);

// For debugging purposes: You can uncomment the line below to log the raw incoming data.
// This is super helpful if something isn't working as expected!
// file_put_contents('telegram_log.txt', print_r($update, true), FILE_APPEND);

// Check if a message was received AND if that message was forwarded.
// We're specifically looking for the 'forward_from' field to identify the original sender.
if (isset($update['message']['forward_from_chat'])) {
    // 📡 فوروارد از کانال یا گروه (public)
    $forwardChat = $update['message']['forward_from_chat'];
    $chatId = $update['message']['chat']['id'];

    $chatType = $forwardChat['type']; // channel, supergroup
    $chatTitle = $forwardChat['title'] ?? 'No title';
    $chatUsername = isset($forwardChat['username']) ? '@' . $forwardChat['username'] : 'No username';
    $chatNumericId = $forwardChat['id'];

    $text = "📥 Message was forwarded from a <b>$chatType</b>:\n\n";
    $text .= "🆔 <b>ID:</b> <code>$chatNumericId</code>\n";
    $text .= "📛 <b>Title:</b> $chatTitle\n";
    $text .= "🔗 <b>Username:</b> $chatUsername\n";
    $text .= "📂 <b>Type:</b> $chatType";

    sendMessage($chatId, $text);

} elseif (isset($update['message']['forward_from'])) {
    // 👤 فوروارد از یک کاربر (شخصی)
    $forwardUser = $update['message']['forward_from'];
    $chatId = $update['message']['chat']['id'];

    $firstName = $forwardUser['first_name'] ?? '';
    $lastName = $forwardUser['last_name'] ?? '';
    $username = isset($forwardUser['username']) ? '@' . $forwardUser['username'] : 'No username';
    $userId = $forwardUser['id'];

    $text = "👤 Message was forwarded from a user:\n\n";
    $text .= "🆔 <b>User ID:</b> <code>$userId</code>\n";
    $text .= "👥 <b>Name:</b> $firstName $lastName\n";
    $text .= "🔗 <b>Username:</b> $username";

    sendMessage($chatId, $text);

} else {
    // ⛔ فوروارد نشده یا اطلاعات قابل بازیابی نیست (مثلاً گروه خصوصی)
    $chatId = $update['message']['chat']['id'];
    sendMessage($chatId, "⚠️ Sorry! I couldn't detect the original sender.\nMaybe the message was forwarded from a <b>private group</b> or a <b>hidden user</b>.\n\nTry forwarding from a public channel or user!");
}


/**
 * A helper function to send messages back to the user.
 * This function encapsulates the logic for making the API call to Telegram.
 * @param int $chatId The ID of the chat where the message should be sent.
 * @param string $text The text content of the message to be sent.
 */
function sendMessage($chatId, $text) {
    global $telegramApiUrl; // Access the globally defined Telegram API URL.

    // Prepare the parameters for the sendMessage API call.
    $parameters = [
        'chat_id' => $chatId, // The recipient's chat ID.
        'text' => $text,     // The message text.
        'parse_mode' => 'HTML', // This allows us to use basic HTML formatting in our messages!
    ];

    // Construct the full URL for the sendMessage API endpoint with all parameters.
    $url = $telegramApiUrl . 'sendMessage?' . http_build_query($parameters);

    // Make the actual HTTP GET request to the Telegram API to send the message.
    // We also check if the request was successful.
    $response = file_get_contents($url);
    if ($response === FALSE) {
        // If there's an error sending the message, log it for debugging.
        // This is crucial for troubleshooting!
        error_log("Error sending message to Telegram. URL: " . $url);
    }
}
