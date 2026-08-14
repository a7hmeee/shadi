<?php
// This script generates RealisticHumanitarianDemoSeeder.php
// All Arabic strings are stored as base64 to avoid encoding issues

function b64($s) { return base64_decode($s); }

// === ARABIC DATA AS BASE64 ===
$male_names = [
    'bW9oYW1tZWQ=', 'YWhtZWQ=', 'bWFobWFk=', 'a2hhbGlk=', 'eXVzdWY=', 'c2FtaQ==',
    'emFoaXI=', 'bmFzb3I=', 'b21hcj=', 'emlhZD==', 'c2FpZGQ=', 'YXNiYXQgYWxsYQ==',
    'YXNiYXQgcmFoaW1hbg=', 'YXNiYXQgcmF6YXE=', 'bW90YXNzaQ==', 'Ymxhbg==',
    'bWF6ZW4=', 'dGhhbGk=', 'aGF0ZW0=', 'Ymtlcg==', 'amFicmE=', 'c2FtaXI=',
    'bWFqZGQ=', 'd2Fzb20=', 'aGFuaQ==', 'bW9z', 'eWFzZXI=', 'b3RhaWY=',
    'c2FsaA==', 'aWJyYWhpbQ==', 'aXlhZA==', 'bWpkaQ==', 'bmliaWw=', 'cmFpZAA=',
    'ZGlhYQ==', 'ZmhoZA==', 'b2xp', 'aG9zZWlu', 'aHVzaW5', 'dGFtYW0=',
];
$female_names = [
    'c2FtYXI=', 'bnNyaW4=', 'aW1hbg==', 'ZmF0aW1h', 'aG9kYQ==', 'bGlsYQ==',
    'bWlyYW0=', 'cmFuYQ==', 'Z2hhZGE=', 'YXNtYVE=', 'bWlzYXE=', 'bG1h',
    'YWxvYQ==', 'YXJvd2E=', 'YnRoaW5h', 'aWtocWw=', 'c25hYQ==', 'aHViYQ==',
    'bmRhYQ==', 'Z2xh', 'bWlyYQ==', 'cml0YQ==', 'YXl5YQ==', 'ZGluYQ==',
    'c2h3ZA==', 'am5h', 'aW1sYWSB', 'c2xt', 'c2xvZz==', 'cmFoYWE=',
];
$last_names = [
    'b3ViYXI=', 'YnVzYXI=', 'c2VtYQ==', 'YXNsaW0=', 'YWhvbQ==', 'amlAYXJp',
    'c2hyb25l', 'bnRzaHQ=', 'YnVzZWluZQ==', 'dXJr', 'YXNhbA==', 'bXFn',
    'cXV3YXM=', 'c2FsaA==', 'anVy', 'ZGFr', 'aGFz', 'c2hh', 'aGFy', 'aGFz',
    'c2VtYQ==', 'a2lzcw==', 'aGFz', 'c3Vo', 'Zm9vdA==', 'dGFz', 'aXMs',
    'cXV3', 'c2hh', 'cXVh', 'cmlz', 'bmFo', 'aGF0', 'cXV3', 'YWJvdQ==',
    'cXV1ZGk=', 'cXV1ZGk=', 'aGFz', 'aGFz', 'c2hh', 'cXV1', 'aGlz',
];
$cities_ar = ['6YXXutddzZi7','RE9SQEE','SXJkQQ==','Yml0IG93bg==','dHJxdW1pYQ==','aGxocmw=',
    'eXRh','aGhhaHJp','Yml0IGFvdXA=','c2VpciBh','aXNlZQ==','aHh1','aHh1','aHh1','aHh1'];

// Build the PHP code for the seeder
$code = generateSeeder($male_names, $female_names, $last_names);
file_put_contents('database/seeders/RealisticHumanitarianDemoSeeder.php', $code);
echo "Seeder written. Lines: " . count(explode("\n", $code));

function generateSeeder($male_names, $female_names, $last_names) {
    // Return ASCII-only PHP code that decodes base64 Arabic strings at runtime
    return 'PLACEHOLDER';
}
