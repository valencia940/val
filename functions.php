<?php
// ============================================================
// functions.php - Shared Functions for Campus Management System
// ============================================================

// ---------- GENERAL HELPERS ----------

function formatCurrency(float $amount): string {
    return 'R' . number_format($amount, 2);
}

function printSectionHeader(string $title): void {
    echo "<div class='section-header'><h2>$title</h2></div>";
}

function printAlert(string $message, string $type = 'info'): void {
    echo "<div class='alert alert-$type'>$message</div>";
}

// ---------- PARKING FUNCTIONS ----------

function isEligibleForPermit(int $age): bool {
    return $age >= 18;
}

function getPermitPrice(string $type): float {
    $prices = [
        'Student' => PERMIT_STUDENT,
        'Staff'   => PERMIT_STAFF,
        'Visitor' => PERMIT_VISITOR,
    ];
    return $prices[$type] ?? 0.0;
}

function issuePermit(array &$permits, string $name, int $age, string $type): string {
    if (!isEligibleForPermit($age)) {
        return "DENIED: $name (Age $age) is under 18 and cannot receive a permit.";
    }
    $totalIssued = array_sum(array_column($permits, 'count'));
    if ($totalIssued >= MAX_PARKING_CAPACITY) {
        return "DENIED: Parking capacity of " . MAX_PARKING_CAPACITY . " has been reached.";
    }
    if (!isset($permits[$type])) {
        $permits[$type] = ['count' => 0, 'revenue' => 0.0, 'holders' => []];
    }
    $permits[$type]['count']++;
    $permits[$type]['revenue'] += getPermitPrice($type);
    $permits[$type]['holders'][] = $name;
    return "ISSUED: $type permit to $name for " . formatCurrency(getPermitPrice($type));
}

function getParkingSummary(array $permits): array {
    $totalRevenue = 0.0;
    $totalPermits = 0;
    foreach ($permits as $data) {
        $totalRevenue += $data['revenue'];
        $totalPermits += $data['count'];
    }
    return ['total_revenue' => $totalRevenue, 'total_permits' => $totalPermits];
}

// ---------- LIBRARY FUNCTIONS ----------

function calculateFine(string $category, int $daysLate): float {
    if ($daysLate <= 0) return 0.0;
    $rates = [
        'Textbook'      => FINE_TEXTBOOK,
        'Journal'       => FINE_JOURNAL,
        'Reference Book'=> FINE_REFERENCE,
    ];
    $rate = $rates[$category] ?? 0.0;
    return $rate * $daysLate;
}

function borrowBook(array &$users, string $userId, string $bookTitle, string $category, string $borrowDate): string {
    if (!isset($users[$userId])) {
        return "ERROR: User $userId not found.";
    }
    $outstandingFine = $users[$userId]['outstanding_fine'];
    if ($outstandingFine > MAX_FINE_LIMIT) {
        return "DENIED: {$users[$userId]['name']} has an outstanding fine of " .
               formatCurrency($outstandingFine) . " (limit: " . formatCurrency(MAX_FINE_LIMIT) . "). Please settle before borrowing.";
    }
    $users[$userId]['borrowed_books'][] = [
        'title'       => $bookTitle,
        'category'    => $category,
        'borrow_date' => $borrowDate,
        'return_date' => null,
        'fine'        => 0.0,
    ];
    return "SUCCESS: {$users[$userId]['name']} borrowed \"$bookTitle\" ($category) on $borrowDate.";
}

function returnBook(array &$users, string $userId, string $bookTitle, string $returnDate, int $allowedDays = 14): string {
    if (!isset($users[$userId])) {
        return "ERROR: User $userId not found.";
    }
    foreach ($users[$userId]['borrowed_books'] as &$book) {
        if ($book['title'] === $bookTitle && $book['return_date'] === null) {
            $book['return_date'] = $returnDate;
            $borrowDate  = new DateTime($book['borrow_date']);
            $retDate     = new DateTime($returnDate);
            $daysHeld    = (int)$borrowDate->diff($retDate)->days;
            $daysLate    = max(0, $daysHeld - $allowedDays);
            $fine        = calculateFine($book['category'], $daysLate);
            $book['fine'] = $fine;
            $users[$userId]['outstanding_fine'] += $fine;
            $msg = "RETURNED: \"{$bookTitle}\" by {$users[$userId]['name']}. Days held: $daysHeld.";
            if ($fine > 0) {
                $msg .= " Late by $daysLate day(s). Fine: " . formatCurrency($fine) . ".";
            } else {
                $msg .= " Returned on time. No fine.";
            }
            return $msg;
        }
    }
    return "ERROR: Book \"$bookTitle\" not found in {$users[$userId]['name']}'s borrowed list.";
}

function printUserSummary(array $users): void {
    echo "<table class='data-table'>";
    echo "<thead><tr>
            <th>User ID</th><th>Name</th><th>Books Borrowed</th>
            <th>Books Returned</th><th>Outstanding Fine</th>
          </tr></thead><tbody>";
    foreach ($users as $id => $user) {
        $borrowed  = count($user['borrowed_books']);
        $returned  = count(array_filter($user['borrowed_books'], fn($b) => $b['return_date'] !== null));
        $fine      = formatCurrency($user['outstanding_fine']);
        $fineClass = $user['outstanding_fine'] > MAX_FINE_LIMIT ? 'text-danger' : '';
        echo "<tr>
                <td>$id</td>
                <td>{$user['name']}</td>
                <td>$borrowed</td>
                <td>$returned</td>
                <td class='$fineClass'>$fine</td>
              </tr>";
    }
    echo "</tbody></table>";
}

// ---------- PERFORMANCE FUNCTIONS ----------

function validateMark(mixed $mark): bool {
    return is_numeric($mark) && $mark >= 0 && $mark <= 100;
}

function calculateAverage(array $marks): float {
    $valid = array_filter($marks, 'validateMark');
    if (empty($valid)) return 0.0;
    return array_sum($valid) / count($valid);
}

function assignResult(float $average): string {
    if ($average >= 75) return 'Distinction';
    if ($average >= 50) return 'Pass';
    return 'Fail';
}

function getResultBadgeClass(string $result): string {
    return match($result) {
        'Distinction' => 'badge-distinction',
        'Pass'        => 'badge-pass',
        default       => 'badge-fail',
    };
}

function findTopStudent(array $students): string {
    $top = '';
    $topAvg = -1;
    foreach ($students as $name => $data) {
        if ($data['average'] > $topAvg) {
            $topAvg = $data['average'];
            $top    = $name;
        }
    }
    return $top;
}

function generateClassStats(array $students): array {
    $averages = array_column($students, 'average');
    return [
        'highest'       => max($averages),
        'lowest'        => min($averages),
        'class_average' => array_sum($averages) / count($averages),
    ];
}
