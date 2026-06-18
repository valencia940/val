# Campus Management System – User Guide
## Internet Programming 621 Assignment

**Institution:** Richfield Graduate Institute of Technology  
**Module:** Internet Programming 621

---

## Project Structure

```
campus_cms/
├── index.php        → Main menu / home page
├── parking.php      → Parking Permit Module (30 marks)
├── library.php      → Library Borrowing & Fine Module (40 marks)
├── performance.php  → Student Performance Module (30 marks)
├── functions.php    → Shared functions (all modules import this)
└── README.md        → This user guide
```

---

## How to Run (XAMPP / localhost)

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache**.
2. Copy the entire `campus_cms/` folder into your XAMPP `htdocs` directory:
   - **Windows:** `C:\xampp\htdocs\campus_cms\`
   - **Mac/Linux:** `/Applications/XAMPP/htdocs/campus_cms/`
3. Open your browser and navigate to:
   ```
   http://localhost/campus_cms/index.php
   ```
4. Use the top navigation bar to switch between modules.

**PHP version required:** 8.0 or higher (uses named arguments, match expressions, arrow functions).

---

## Module Descriptions

### 1.1 Parking Permit Module (`parking.php`) – 30 Marks

**What it does:**
- Processes a list of permit applications using a `foreach` loop.
- Enforces: age ≥ 18, parking capacity ≤ 50 bays.
- Issues permits for **Student (R450)**, **Staff (R750)**, and **Visitor (R100)** types.
- Prices and capacity are stored as PHP **constants** (`PERMIT_STUDENT`, `PERMIT_STAFF`, `PERMIT_VISITOR`, `MAX_PARKING_CAPACITY`).
- Permit records are stored in **associative arrays** (`$permits`).
- Displays a full **summary** table: count, revenue per category, total revenue.
- Shows a **capacity bar** indicating how many bays are in use.

**Key functions used:** `issuePermit()`, `isEligibleForPermit()`, `getPermitPrice()`, `getParkingSummary()`

---

### 1.2 Library Borrowing & Fine Module (`library.php`) – 40 Marks

**What it does:**
- Manages book loans for multiple users stored in **nested associative arrays** (`$users`).
- Supports three book categories: **Textbook (R5/day)**, **Journal (R3/day)**, **Reference Book (R10/day)**.
- Tracks borrow date and return date; calculates late days and fines automatically.
- Blocks users with outstanding fines **above R200** from borrowing new books.
- Displays borrow log, return log, and a full user summary.

**Standard loan period:** 14 days (defined as constant `LOAN_DAYS`).

**Key functions used:**
- `calculateFine(category, daysLate)` – Returns fine amount.
- `borrowBook(users, userId, title, category, date)` – Issues a book (with fine check).
- `returnBook(users, userId, title, returnDate, allowedDays)` – Returns book and calculates fine.
- `printUserSummary(users)` – Renders HTML summary table of all users.

---

### 1.3 Student Performance Module (`performance.php`) – 30 Marks

**What it does:**
- Accepts **6 students**, each with **6 marks** (some marks are intentionally invalid to test validation).
- Validates each mark using `validateMark()` – skips non-numeric and out-of-range (0–100) values.
- Calculates each student's **average** and assigns a **result**:
  - **Distinction:** ≥ 75%
  - **Pass:** 50% – 74%
  - **Fail:** < 50%
- Identifies the **top-performing student** using `findTopStudent()`.
- Generates **class statistics** via `generateClassStats()`: highest avg, lowest avg, class avg.
- Invalid marks are displayed in red with strikethrough and excluded from calculations.

**Key functions used:** `validateMark()`, `calculateAverage()`, `assignResult()`, `findTopStudent()`, `generateClassStats()`

---

## Constants Reference (`functions.php` / each module)

| Constant              | Value    | Description                          |
|-----------------------|----------|--------------------------------------|
| `PERMIT_STUDENT`      | R450.00  | Student parking permit price         |
| `PERMIT_STAFF`        | R750.00  | Staff parking permit price           |
| `PERMIT_VISITOR`      | R100.00  | Visitor parking permit price         |
| `MAX_PARKING_CAPACITY`| 50       | Maximum number of parking bays       |
| `FINE_TEXTBOOK`       | R5.00    | Textbook late return fine per day    |
| `FINE_JOURNAL`        | R3.00    | Journal late return fine per day     |
| `FINE_REFERENCE`      | R10.00   | Reference book fine per day          |
| `MAX_FINE_LIMIT`      | R200.00  | Max outstanding fine before block    |
| `LOAN_DAYS`           | 14       | Standard library loan period (days)  |

---

## Academic Integrity

All code in this project is original work. No code has been copied from any external source.


## Developers
- Reabetswe Mosenyi
- Nkateko Nkuna
- LEBOHANG MUNYAI
- KATLEGO MOSENYI
- THAPELO PHOSHOKO

Some of the books and examples used in this project were inspired by programming, coding and business management resources commonly found at Richfield libraries.
