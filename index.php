<?php
require 'config.php';
$doctors = $pdo->query('SELECT * FROM doctors ORDER BY id')->fetchAll();
$recent  = $pdo->query('SELECT name, rating, message FROM feedback ORDER BY created_at DESC LIMIT 5')->fetchAll();
$flash   = get_flash();
$startPage = $_GET['page'] ?? 'home';
if (!in_array($startPage, ['home','doctors','booking','feedback'])) $startPage = 'home';
$icons = ['Cardiologist'=>'🫀','Neurologist'=>'🧠','Pediatrician'=>'👶','Dentist'=>'🦷'];
function stars($r) { $f = (int)round($r); return str_repeat('★', $f) . str_repeat('☆', 5 - $f); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>EddyCare - Doctor Appointment Booking</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <div class="container header-content">
    <div class="logo"><h1>🏥 EddyCare</h1></div>
    <nav>
      <ul>
        <li><a href="#" class="nav-link" data-page="home">Home</a></li>
        <li><a href="#" class="nav-link" data-page="doctors">Doctors</a></li>
        <li><a href="#" class="nav-link" data-page="booking">Book Appointment</a></li>
        <li><a href="#" class="nav-link" data-page="feedback">Feedback</a></li>
        <?php if (is_admin()): ?>
          <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
          <li><a href="logout.php">Logout</a></li>
        <?php elseif (is_logged_in()): ?>
          <li><a href="my_appointments.php">My Appointments</a></li>
          <li><a href="logout.php">Logout (<?= e(explode(' ', $_SESSION['name'])[0]) ?>)</a></li>
        <?php else: ?>
          <li><a href="login.php">Login</a></li>
          <li><a href="register.php">Register</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<?php if ($flash): ?>
  <div class="container"><div class="alert alert-<?= e($flash['type']) ?>" style="margin-top:1rem"><?= $flash['msg'] /* built from escaped values */ ?></div></div>
<?php endif; ?>

<!-- HOME -->
<section id="home" class="page">
  <div class="hero"><div class="container">
    <h1>Find &amp; Book Appointments with Top Doctors</h1>
    <p>Experience seamless healthcare with our modern booking system. Connect with specialized doctors and schedule appointments at your convenience.</p>
    <a href="#" class="btn nav-link" data-page="doctors">Find Doctors</a>
  </div></div>
  <div class="container">
    <div class="card-3d">
      <h2>Why Choose EddyCare?</h2>
      <div class="grid">
        <div><h3>Easy Booking</h3><p>Book appointments in just a few clicks with our intuitive interface.</p></div>
        <div><h3>Verified Doctors</h3><p>All our doctors are thoroughly verified and highly qualified.</p></div>
        <div><h3>24/7 Support</h3><p>Our support team is available round the clock to assist you.</p></div>
      </div>
    </div>
    <div class="card-3d">
      <h2>Our Specialties</h2>
      <div class="grid">
        <?php foreach ($doctors as $d): ?>
          <div style="text-align:center;padding:1rem"><div style="font-size:3rem"><?= $icons[$d['specialty']] ?? '🩺' ?></div><h3><?= e($d['specialty']) ?></h3></div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- DOCTORS -->
<section id="doctors" class="page">
  <div class="container">
    <h1 style="margin-bottom:1.5rem">Our Specialist Doctors</h1>
    <?php foreach ($doctors as $d): ?>
    <div class="card-3d"><div class="doctor-profile">
      <div class="doctor-image">
        <?php if ($d['image'] && file_exists(__DIR__ . '/img/' . $d['image'])): ?>
          <img src="img/<?= e($d['image']) ?>" alt="<?= e($d['name']) ?>">
        <?php else: ?>🩺<?php endif; ?>
      </div>
      <div class="doctor-info">
        <h2><?= e($d['name']) ?></h2>
        <div class="doctor-specialty"><?= e($d['specialty']) ?></div>
        <div class="rating"><div class="stars"><?= stars($d['rating']) ?></div><span><?= e($d['rating']) ?> (<?= e($d['reviews']) ?> reviews)</span></div>
        <p class="doctor-bio"><?= e($d['bio']) ?></p>
        <a href="#" class="btn nav-link" data-page="booking" data-doctor="<?= (int)$d['id'] ?>">Book Appointment</a>
      </div>
    </div></div>
    <?php endforeach; ?>
  </div>
</section>

<!-- BOOKING -->
<section id="booking" class="page">
  <div class="container">
    <h1 style="margin-bottom:1.5rem">Book Your Appointment</h1>
    <?php if (!is_logged_in()): ?>
      <div class="alert alert-info">Please <a href="login.php">log in</a> or <a href="register.php">create an account</a> to book an appointment.</div>
    <?php elseif (is_admin()): ?>
      <div class="alert alert-info">You are logged in as admin. Use the <a href="admin/dashboard.php">dashboard</a> to manage appointments.</div>
    <?php else: ?>
    <form action="book.php" method="post" id="booking-form">
      <div class="card-3d">
        <h2>Select Doctor</h2>
        <div class="form-group">
          <select class="form-control" name="doctor_id" id="doctor-select" required>
            <option value="">Select a doctor</option>
            <?php foreach ($doctors as $d): ?>
              <option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?> - <?= e($d['specialty']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="card-3d">
        <h2>Available Timings</h2>
        <div class="form-group">
          <label for="appointment-date">Appointment Date</label>
          <input type="date" class="form-control" name="appointment_date" id="appointment-date" required>
        </div>
        <h3>Available Time Slots</h3>
        <div class="timings-grid" id="slots">
          <?php foreach (all_slots() as $s): ?><div class="time-slot" data-time="<?= e($s) ?>"><?= e($s) ?></div><?php endforeach; ?>
        </div>
        <input type="hidden" name="appointment_time" id="appointment-time">
      </div>
      <div class="card-3d">
        <h2>Your Information</h2>
        <div class="form-group"><label>Full Name</label><input class="form-control" value="<?= e($_SESSION['name']) ?>" readonly></div>
        <div class="form-group"><label>Email Address</label><input class="form-control" value="<?= e($_SESSION['email']) ?>" readonly></div>
        <div class="form-group"><label>Additional Notes (Optional)</label><textarea class="form-control" name="notes" placeholder="Any specific concerns or notes for the doctor"></textarea></div>
        <button type="submit" class="btn">Confirm Appointment</button>
      </div>
    </form>
    <?php endif; ?>
    <div class="card-3d">
      <h2>For Customer care services</h2>
      <div class="contact-info">
        <div>
          <div class="contact-item"><div class="contact-icon">📞</div><div><strong>Phone</strong><br>+256 780549182<br>+256 706978597</div></div>
          <div class="contact-item"><div class="contact-icon">✉️</div><div><strong>Email</strong><br>eddycare@gmail.com</div></div>
        </div>
        <div>
          <div class="contact-item"><div class="contact-icon">🏢</div><div><strong>Address</strong><br>123 Eddy Plaza, Kampala City, Plot No 24</div></div>
          <div class="contact-item"><div class="contact-icon">🕒</div><div><strong>Hospital Hours</strong><br>Mon-Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 2:00 PM</div></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEEDBACK -->
<section id="feedback" class="page">
  <div class="container">
    <h1 style="margin-bottom:1.5rem">Share Your Experience</h1>
    <div class="card-3d">
      <h2>Submit Feedback</h2>
      <p>We value your opinion. Please share your experience with our services to help us improve.</p>
      <form action="feedback_submit.php" method="post" style="margin-top:1rem">
        <div class="form-group"><label>Your Name</label>
          <input class="form-control" name="name" value="<?= is_logged_in() ? e($_SESSION['name']) : '' ?>" required></div>
        <div class="form-group"><label>Doctor (Optional)</label>
          <select class="form-control" name="doctor_id">
            <option value="">Select a doctor</option>
            <?php foreach ($doctors as $d): ?><option value="<?= (int)$d['id'] ?>"><?= e($d['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="form-group"><label>Overall Rating</label>
          <div class="rating-input">
            <?php for ($i = 5; $i >= 1; $i--): ?>
              <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" <?= $i == 5 ? 'required' : '' ?>><label for="star<?= $i ?>">★</label>
            <?php endfor; ?>
          </div></div>
        <div class="form-group"><label>Your Feedback</label>
          <textarea class="form-control" name="message" placeholder="Please share your experience with us..." required></textarea></div>
        <button type="submit" class="btn">Submit Feedback</button>
      </form>
    </div>
    <div class="card-3d">
      <h2>Recent Patient Feedback</h2>
      <div style="display:grid;gap:1.5rem;margin-top:1.5rem">
        <?php if (!$recent): ?><p>No feedback yet. Be the first!</p><?php endif; ?>
        <?php foreach ($recent as $f): ?>
          <div style="padding:1rem;border-left:4px solid var(--primary);background:var(--secondary);border-radius:0 10px 10px 0">
            <div style="display:flex;justify-content:space-between;margin-bottom:.5rem"><strong><?= e($f['name']) ?></strong><span class="stars"><?= stars($f['rating']) ?></span></div>
            <p>"<?= e($f['message']) ?>"</p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<footer><div class="container"><div class="footer-content">
  <div class="footer-section"><h2>EddyCare</h2><p>Your trusted partner in healthcare. We connect patients with top medical professionals for seamless appointment booking.</p></div>
  <div class="footer-section"><h3>Contact Us</h3><ul><li>📞 +256 780549182</li><li>✉️ info@eddycare.com</li><li>🏢 123 Eddy Plaza</li></ul></div>
</div></div></footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const navLinks = document.querySelectorAll('.nav-link');
  const pages = document.querySelectorAll('.page');

  function show(pageId) {
    pages.forEach(p => p.classList.toggle('active', p.id === pageId));
    navLinks.forEach(l => l.classList.toggle('active', l.dataset.page === pageId && !l.classList.contains('btn')));
    window.scrollTo(0, 0);
  }
  navLinks.forEach(link => link.addEventListener('click', function (e) {
    e.preventDefault();
    show(this.dataset.page);
    if (this.dataset.doctor) {
      const sel = document.getElementById('doctor-select');
      if (sel) { sel.value = this.dataset.doctor; loadSlots(); }
    }
  }));
  show(<?= json_encode($startPage) ?>);

  const dateInput = document.getElementById('appointment-date');
  const doctorSel = document.getElementById('doctor-select');
  const timeInput = document.getElementById('appointment-time');
  const slotEls = document.querySelectorAll('.time-slot');

  if (dateInput) {
    dateInput.min = new Date().toISOString().split('T')[0];
    slotEls.forEach(slot => slot.addEventListener('click', function () {
      if (this.classList.contains('taken')) return;
      slotEls.forEach(s => s.classList.remove('selected'));
      this.classList.add('selected');
      timeInput.value = this.dataset.time;
    }));
    dateInput.addEventListener('change', loadSlots);
    doctorSel.addEventListener('change', loadSlots);

    document.getElementById('booking-form').addEventListener('submit', function (e) {
      if (!timeInput.value) { e.preventDefault(); alert('Please select a time slot.'); }
    });
  }

  function loadSlots() {
    if (!doctorSel.value || !dateInput.value) return;
    timeInput.value = '';
    slotEls.forEach(s => s.classList.remove('selected', 'taken'));
    fetch('slots.php?doctor_id=' + encodeURIComponent(doctorSel.value) + '&date=' + encodeURIComponent(dateInput.value))
      .then(r => r.json())
      .then(taken => slotEls.forEach(s => { if (taken.includes(s.dataset.time)) s.classList.add('taken'); }));
  }
});
</script>
</body>
</html>
