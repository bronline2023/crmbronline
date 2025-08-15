<?php
/**
 * test_recruitment_model.php
 *
 * This file is for debugging purposes to check if recruitment_post.php functions are callable.
 * Place this file in your project_management_system root directory.
 */

// Include the main configuration file
require_once __DIR__ . '/config.php';

echo "<h1>Testing Recruitment Model Functions</h1>";

// Test if connectDB function exists
if (function_exists('connectDB')) {
    echo "<p style='color: green;'>connectDB() function is defined.</p>";
    $pdo = connectDB();
    if ($pdo) {
        echo "<p style='color: green;'>Database connection successful.</p>";
    } else {
        echo "<p style='color: red;'>Database connection FAILED.</p>";
    }
} else {
    echo "<p style='color: red;'>connectDB() function is NOT defined. Check models/db.php inclusion.</p>";
}

echo "<hr>";

// Test if getRecruitmentPostsForAdmin function exists
if (function_exists('getRecruitmentPostsForAdmin')) {
    echo "<p style='color: green;'>getRecruitmentPostsForAdmin() function is defined.</p>";
    try {
        $totalPosts = 0;
        $posts = getRecruitmentPostsForAdmin('all', '', 1, 0, $totalPosts);
        echo "<p style='color: green;'>getRecruitmentPostsForAdmin() called successfully. Total posts: " . htmlspecialchars($totalPosts) . "</p>";
        if (!empty($posts)) {
            echo "<p style='color: green;'>Fetched " . count($posts) . " post(s).</p>";
            echo "<pre>" . htmlspecialchars(print_r($posts[0], true)) . "</pre>"; // Show first post
        } else {
            echo "<p style='color: orange;'>No posts found, or empty array returned.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error calling getRecruitmentPostsForAdmin(): " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>getRecruitmentPostsForAdmin() function is NOT defined. Check models/recruitment/recruitment_post.php inclusion.</p>";
}

echo "<hr>";

// Test if getEarningPerApprovedPost function exists
if (function_exists('getEarningPerApprovedPost')) {
    echo "<p style='color: green;'>getEarningPerApprovedPost() function is defined.</p>";
    try {
        $earning = getEarningPerApprovedPost();
        echo "<p style='color: green;'>Earning per approved post: " . htmlspecialchars($earning) . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error calling getEarningPerApprovedPost(): " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color: red;'>getEarningPerApprovedPost() function is NOT defined.</p>";
}

echo "<hr>";

// Test if getApprovalStatusBadgeColor function exists
if (function_exists('getApprovalStatusBadgeColor')) {
    echo "<p style='color: green;'>getApprovalStatusBadgeColor() function is defined.</p>";
    echo "<p>Pending: <span style='background-color: " . getApprovalStatusBadgeColor('pending') . ";'>Test</span></p>";
    echo "<p>Approved: <span style='background-color: " . getApprovalStatusBadgeColor('approved') . ";'>Test</span></p>";
} else {
    echo "<p style='color: red;'>getApprovalStatusBadgeColor() function is NOT defined.</p>";
}

?>
