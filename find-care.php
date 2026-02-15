<?php
// --- 1. Database Connection ---
$servername = "localhost";
$username   = "root";         
$password   = "";             
$dbname     = "care_directory"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variable to avoid "undefined" errors
$ai_answer = "";

// --- 2. Handle Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_location = $conn->real_escape_string($_POST['location']);
    $user_symptoms = $conn->real_escape_string($_POST['symptoms']);
    $user_age      = htmlspecialchars($_POST['age']);
    $user_sex      = htmlspecialchars($_POST['sex']);
    $user_gender   = htmlspecialchars($_POST['gender_identity']);
    $user_pref_gender = htmlspecialchars($_POST['practitioner_gender']);
    $user_radius   = htmlspecialchars($_POST['radius']);

    // --- 3. Geocode the User's Address ---
    $user_lat = null;
    $user_lng = null;
    
    // Use a free geocoding service to convert address to coordinates
    $geocode_url = "https://nominatim.openstreetmap.org/search?format=json&q=" . urlencode($user_location);
    
    $ch_geo = curl_init($geocode_url);
    curl_setopt($ch_geo, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_geo, CURLOPT_USERAGENT, 'HealthcareFinderApp/1.0');
    curl_setopt($ch_geo, CURLOPT_TIMEOUT, 10);
    $geo_response = curl_exec($ch_geo);
    curl_close($ch_geo);
    
    $geo_data = json_decode($geo_response, true);
    if (!empty($geo_data) && isset($geo_data[0]['lat']) && isset($geo_data[0]['lon'])) {
        $user_lat = floatval($geo_data[0]['lat']);
        $user_lng = floatval($geo_data[0]['lon']);
    }
    
    // --- 4. Query Providers ---
    $provider_context = "";
    
    if ($user_lat && $user_lng) {
        // Get all providers - we'll mention the radius to the AI
        $sql = "SELECT business_name, services, contact_info FROM providers";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $provider_context .= "AFFILIATE: " . $row["business_name"] . "\n";
                $provider_context .= "SERVICES: " . $row["services"] . "\n";
                $provider_context .= "CONTACT: " . $row["contact_info"] . "\n\n";
            }
        } else {
            $provider_context = "No providers found in our network.";
        }
    } else {
        $provider_context = "Could not find location '" . htmlspecialchars($user_location) . "'. Please enter a valid address or city.";
    }

    // --- 4. Prepare the Gemini API Call ---
    $apiKey = "AIzaSyBuafqLFga6DOXZZ7YvotNx4vKOVOyeFi0"; 
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    // Build patient demographics
    $demographics = "Age: $user_age";
    if ($user_sex !== 'select') $demographics .= ", Sex: $user_sex";
    if ($user_gender !== 'select') $demographics .= ", Gender Identity: $user_gender";
    if ($user_pref_gender !== 'noPreference') $demographics .= ", Prefers $user_pref_gender practitioner";

    $prompt = "You are a medical concierge helping a patient find care. Address the patient directly (use 'you'). Do not include any intro text - start directly with the sections.

PATIENT INFO:
$demographics
Location: $user_location (within $user_radius km)
Symptoms: $user_symptoms

AVAILABLE AFFILIATES:
$provider_context

INSTRUCTIONS: 
Be brief and direct. Format EXACTLY like this. CRITICAL: Put the clinic name on its own line, followed by a blank line, then the description. This applies to BOTH top recommendation AND other options:

TOP RECOMMENDATION

[Clinic Name]

Specializes in [relevant service]. Good fit for your [symptom].

Address: [Full street address]
Phone: [Phone number]
Email: [Email]
Website: [Website URL]
Google Maps: https://www.google.com/maps/search/?api=1&query=[URL encoded address]

---

OTHER OPTIONS

[Clinic Name]

Also offers [relevant service].

Address: [Full street address]
Phone: [Phone number]
Email: [Email]
Website: [Website URL]
Google Maps: https://www.google.com/maps/search/?api=1&query=[URL encoded address]

[Clinic Name]

Available for [service].

Address: [Full street address]
Phone: [Phone number]
Email: [Email]
Website: [Website URL]
Google Maps: https://www.google.com/maps/search/?api=1&query=[URL encoded address]

---

IMPORTANT

This is a referral service for ancillary care providers and is not a substitute for professional medical advice or specialist care. Please contact the clinic directly for an appointment or immediate care. For urgent medical concerns, call 911 or go to your nearest emergency room.";

    $data = [
        "contents" => [["parts" => [["text" => $prompt]]]]
    ];

    // --- 5. Execute cURL ---
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // FIXED: Check if API call succeeded
    if ($http_code == 200) {
        $response_data = json_decode($response, true);
        if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_answer = $response_data['candidates'][0]['content']['parts'][0]['text'];
        } else {
            $ai_answer = "Unable to load recommendations. Please check your API key.";
        }
    } else {
        $ai_answer = "API Error (HTTP $http_code). Please try again.";
    }

    // --- 6. Display the Results Page ---
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Your Care Match Results</title>
        <link rel="stylesheet" href="style.css">
        <style>
            body {
                padding: 20px;
        
            }
            .results-content {
                max-width: 800px;
                margin: 0 auto;
                background-color: white;
                padding: 30px;
                border-radius: 20px;
                border: 5px solid #0274b3;
                height: auto;
            }
        </style>
    </head>
    <body>
        <div class="results-content">
            <h1>Your Care Matches</h1>
            
            <?php
            // Replace section headers with proper HTML headings
            $formatted = $ai_answer;
            $formatted = str_replace('TOP RECOMMENDATION', '<h2>Top Recommendation</h2>', $formatted);
            $formatted = str_replace('OTHER OPTIONS', '<h2>Other Options</h2>', $formatted);
            $formatted = str_replace('IMPORTANT DISCLAIMER', '<h3>Important</h3>', $formatted);
            $formatted = str_replace('IMPORTANT', '<h3>Important</h3>', $formatted);
            $formatted = str_replace('---', '<hr>', $formatted);
            
            // Convert URLs to clickable links with target="_blank"
            // Match websites (www.example.com or example.com)
            $formatted = preg_replace(
                '/(Website: )((?:www\.)?[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,})/',
                '$1<a href="http://$2" target="_blank">$2</a>',
                $formatted
            );
            
            // Match Google Maps links
            $formatted = preg_replace(
                '/(Google Maps: )(https:\/\/[^\s]+)/',
                '$1<a href="$2" target="_blank">View on Google Maps</a>',
                $formatted
            );
            
            // Match email addresses
            $formatted = preg_replace(
                '/(Email: )([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/',
                '$1<a href="mailto:$2">$2</a>',
                $formatted
            );
            
            // Convert ALL clinic names to h3 headers
            // Strategy: Look for lines containing clinic keywords that are NOT contact/description lines
            $lines = explode("\n", $formatted);
            $output_lines = [];
            
            for ($i = 0; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                
                // Skip empty lines and already-tagged lines
                if (empty($line) || strpos($line, '<') === 0) {
                    $output_lines[] = $lines[$i];
                    continue;
                }
                
                // Skip lines that are clearly contact info or descriptions
                if (preg_match('/^(Address|Phone|Email|Website|Google|Specializes|Also|Available|Good|Offers|Provides):/i', $line)) {
                    $output_lines[] = $lines[$i];
                    continue;
                }
                
                // Skip description sentences (start with description words but no colon)
                if (preg_match('/^(Specializes|Also|Available|Good|Offers|Provides)\s/i', $line)) {
                    $output_lines[] = $lines[$i];
                    continue;
                }
                
                // If line contains healthcare keywords, make it h3
                if (preg_match('/(Clinic|Center|Centre|Medical|Care|Health|Medicine|Hospital|Urgent|Practice|Wellness|Family|Sports|Dermatology|Cardiology|Diabetes|Group|Appletree)/i', $line)) {
                    $output_lines[] = '<h3>' . $line . '</h3>';
                } else {
                    $output_lines[] = $lines[$i];
                }
            }
            
            $formatted = implode("\n", $output_lines);
            
            // Wrap in paragraphs (split by double line breaks)
            $paragraphs = explode("\n\n", $formatted);
            $output = '';
            foreach ($paragraphs as $para) {
                $para = trim($para);
                if (!empty($para)) {
                    // Don't wrap headers or hr in <p> tags
                    if (strpos($para, '<h2>') === 0 || strpos($para, '<h3>') === 0 || strpos($para, '<hr>') === 0) {
                        $output .= $para;
                    } else {
                        $output .= '<p>' . nl2br($para) . '</p>';
                    }
                }
            }
            
            echo $output;
            ?>
            
            <a href="index.php" id="findCareLink">← New Search</a>
        </div>
    </body>
    </html>
    <?php
} else {
    // If someone tries to visit find-care.php directly without submitting the form
    header("Location: index.php");
    exit();
}

$conn->close();
?>