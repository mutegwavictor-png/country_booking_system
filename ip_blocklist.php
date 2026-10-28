<?php
// A list of IP addresses that are NOT allowed to access the admin panel
$blocked_ips = [
    '192.168.1.55',     // A bad user
    '203.0.113.42',     // A known hacker IP
    '127.0.0.1'         // (WARNING: If you test locally on XAMPP, your IP is 127.0.0.1. Do not add this unless you want to block yourself!)
];
?>