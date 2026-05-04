<?php
/**
 * Security helper functions
 */

function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function escape($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}