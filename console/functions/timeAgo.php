<?php
function timeAgo($timestamp) {
    $current_time = time();
    $time_difference = $current_time - $timestamp;
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);
  
    if ($seconds <= 60) {
      return 'just now';
    } elseif ($minutes <= 60) {
      if ($minutes == 1) {
        return 'A minute ago';
      } else {
        return "$minutes minutes ago";
      }
    } elseif ($hours <= 24) {
      if ($hours == 1) {
        return 'An hour ago';
      } else {
        return "$hours hours ago";
      }
    } elseif ($days <= 7) {
      if ($days == 1) {
        return 'A day ago';
      } else {
        return "$days days ago";
      }
    } elseif ($weeks <= 4.3) {
      if ($weeks == 1) {
        return 'A week ago';
      } else {
        return "$weeks weeks ago";
      }
    } elseif ($months <= 12) {
      if ($months == 1) {
        return 'A month ago';
      } else {
        return "$months months ago";
      }
    } else {
      if ($years == 1) {
        return 'A year ago';
      } else {
        return "$years years ago";
      }
    }
}
  