<?php
$yaml_content = file_get_contents('storage/api-docs/openapi.yaml');
echo "File size: " . strlen($yaml_content) . " bytes\n";
echo "First 100 chars:\n" . substr($yaml_content, 0, 100) . "\n";
echo "\n--- Checking for multipart ---\n";
echo strpos($yaml_content, 'multipart/form-data') !== false ? "✅ Found multipart/form-data\n" : "❌ Not found\n";
echo "Count: " . substr_count($yaml_content, 'multipart/form-data') . " occurrences\n";
