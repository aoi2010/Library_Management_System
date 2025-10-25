<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
requireLogin(); requireRole(['admin','librarian']);
$pdo = getDB();

if (isset($_GET['export']) && $_GET['export']==='inventory_csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="inventory.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['ID','Title','Author','ISBN','Category','Year','Publisher','Quantity'], ',', '"', '\\');
    // Removed duplicate header line
  foreach ($pdo->query('SELECT id,title,author,isbn,category,year,publisher,quantity FROM books ORDER BY id') as $row) {
  fputcsv($out, $row, ',', '"', '\\');
  }
  exit;
}

// New: export issued books CSV
if (isset($_GET['export']) && $_GET['export']==='issued_csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="issued_books.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Issue ID','Student','Book','Issue Date','Due Date','Return Date','Fine'], ',', '"', '\\');
  $sql = 'SELECT ib.id, s.name AS student, b.title AS book, ib.issue_date, ib.due_date, ib.return_date, ib.fine
          FROM issued_books ib
          JOIN students s ON s.id=ib.student_id
          JOIN books b ON b.id=ib.book_id
          ORDER BY ib.id';
  foreach ($pdo->query($sql) as $row) { fputcsv($out, $row, ',', '"', '\\'); }
  exit;
}

// New: export overdue CSV
if (isset($_GET['export']) && $_GET['export']==='overdue_csv') {
  header('Content-Type: text/csv');
  header('Content-Disposition: attachment; filename="overdue.csv"');
  $out = fopen('php://output', 'w');
  fputcsv($out, ['Issue ID','Student','Book','Due Date','Days Late'], ',', '"', '\\');
  $sql = "SELECT ib.id, s.name AS student, b.title AS book, ib.due_date, DATEDIFF(CURDATE(), ib.due_date) AS late
          FROM issued_books ib
          JOIN students s ON s.id=ib.student_id
          JOIN books b ON b.id=ib.book_id
          WHERE ib.return_date IS NULL AND ib.due_date < CURDATE()
          ORDER BY late DESC";
  foreach ($pdo->query($sql) as $row) { fputcsv($out, $row, ',', '"', '\\'); }
  exit;
}

// Analytics numbers for chart (real data)
$issuedOpen = (int)$pdo->query('SELECT COUNT(*) FROM issued_books WHERE return_date IS NULL')->fetchColumn();
$overdue = (int)$pdo->query('SELECT COUNT(*) FROM issued_books WHERE return_date IS NULL AND due_date < CURDATE()')->fetchColumn();
$studentsCount = (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn();
$booksCount = (int)$pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>
<div class="min-h-screen flex flex-col">
  <?php include __DIR__ . '/../includes/navbar.php'; ?>
  <main class="flex-1 max-w-7xl mx-auto px-6 py-8">
    <div class="glass glow card border border-gray-200/60 dark:border-gray-800/80 p-5 mb-6">
      <h1 class="text-2xl font-semibold">Reports</h1>
      <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Exports and analytics</p>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Inventory</h3>
        <div class="flex gap-2">
          <a href="?export=inventory_csv" class="btn btn-outline">Download CSV</a>
          <button type="button" onclick="exportInventoryPdf()" class="btn btn-primary">Download PDF</button>
        </div>
      </div>
      <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Analytics</h3>
        <canvas id="chartReports" height="120"></canvas>
      </div>
      <div class="card glow p-4 border border-gray-200 dark:border-gray-800">
        <h3 class="font-semibold mb-3">Issued / Overdue CSV</h3>
        <div class="flex gap-2">
          <a href="?export=issued_csv" class="btn btn-outline">Issued CSV</a>
          <a href="?export=overdue_csv" class="btn btn-outline">Overdue CSV</a>
        </div>
      </div>
    </div>
  </main>
  <?php include __DIR__ . '/../includes/footer.php'; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('chartReports');
  if (ctx) new Chart(ctx, {
    type:'bar',
    data:{
      labels:['Open Issues','Overdue','Students','Books'],
      datasets:[{ label:'Count', data:[<?= $issuedOpen ?>, <?= $overdue ?>, <?= $studentsCount ?>, <?= $booksCount ?>], backgroundColor:'#7C3AED' }]
    },
    options:{ responsive:true, plugins:{ legend:{ display:true } } }
  });
});
</script>
  <!-- jsPDF for PDF export -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-/LemQds0R3fC49A1uF1WslNEnoIMe4DRm7XCVvNejfozXSUhQj8Hj2kHzcM4O8U1wp6z6lX8h6bY4k9t0hUJ0w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
  async function exportInventoryPdf(){
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(14);
    doc.text('Inventory Report', 14, 16);
    doc.setFontSize(10);
  const res = await fetch('<?= htmlspecialchars(base_url(), ENT_QUOTES) ?>/pages/books_export.php?type=json');
    const data = await res.json();
    let y = 24;
    doc.text('ID  Title                                  Author                 Qty', 14, y); y+=6;
    data.slice(0,100).forEach(r => {
      const line = String(r.id).padEnd(4) + (r.title||'').slice(0,35).padEnd(38) + (r.author||'').slice(0,20).padEnd(21) + String(r.quantity||0);
      doc.text(line, 14, y);
      y+=6;
      if (y>280){ doc.addPage(); y=16; }
    });
    doc.save('inventory.pdf');
  }
  </script>
