<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'Director' && $_SESSION['role'] != 'Staff')) {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit();
}

$role = isset($_POST['role']) ? $_POST['role'] : '';

if ($role === 'director') {
    ?>
    <form id="userCreationForm" action="process_create_user.php" method="POST">
      <input type="hidden" name="user_role" value="Director">
      
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" class="form-control" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control">
      </div>
      
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control">
      </div>
      
      <div class="d-grid gap-2">
        <button type="submit" class="btn-submit">
          <i class="fas fa-user-plus me-2"></i>Create Director Account
        </button>
      </div>
    </form>
    <?php
}
elseif ($role === 'staff') {
    // Fetch departments for dropdown
    $departments = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    ?>
    <form id="userCreationForm" action="process_create_user.php" method="POST">
      <input type="hidden" name="user_role" value="Staff">
      
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="fullname" class="form-control" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control">
      </div>
      
      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control">
      </div>
      
      <div class="mb-3">
        <label class="form-label">Department</label>
        <select name="department_id" class="form-select" required>
          <option value="">Select Department</option>
          <?php while($dept = $departments->fetch_assoc()): ?>
            <option value="<?php echo $dept['department_id']; ?>">
              <?php echo htmlspecialchars($dept['department_name']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      
      <div class="d-grid gap-2">
        <button type="submit" class="btn-submit">
          <i class="fas fa-user-plus me-2"></i>Create Staff Account
        </button>
      </div>
    </form>
    <?php
}
elseif ($role === 'student') {
    // Fetch courses and departments
    $courses = $conn->query("SELECT course_id, course_name FROM courses ORDER BY course_name");
    $departments = $conn->query("SELECT department_id, department_name FROM departments ORDER BY department_name");
    ?>
    <form id="userCreationForm" action="process_create_user.php" method="POST">
      <input type="hidden" name="user_role" value="Student">
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Registration Number</label>
          <input type="text" name="regno" class="form-control" required>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="fullname" class="form-control" required>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Date of Birth</label>
          <input type="date" name="dob" class="form-control" required>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" required>
        </div>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control">
      </div>
      
      <div class="mb-3">
        <label class="form-label">District</label>
        <input type="text" name="district" class="form-control" required>
      </div>
      
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Course</label>
          <select name="course_id" class="form-select" required>
            <option value="">Select Course</option>
            <?php while($course = $courses->fetch_assoc()): ?>
              <option value="<?php echo $course['course_id']; ?>">
                <?php echo htmlspecialchars($course['course_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        
        <div class="col-md-6 mb-3">
          <label class="form-label">Department</label>
          <select name="department_id" class="form-select" required>
            <option value="">Select Department</option>
            <?php while($dept = $departments->fetch_assoc()): ?>
              <option value="<?php echo $dept['department_id']; ?>">
                <?php echo htmlspecialchars($dept['department_name']); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>
      
      <div class="mb-3">
        <label class="form-label">Year of Study</label>
        <select name="year_of_study" class="form-select" required>
          <option value="">Select Year</option>
          <option value="1">Year 1</option>
          <option value="2">Year 2</option>
          <option value="3">Year 3</option>
          <option value="4">Year 4</option>
        </select>
      </div>
      
      <div class="d-grid gap-2">
        <button type="submit" class="btn-submit">
          <i class="fas fa-user-plus me-2"></i>Create Student Account
        </button>
      </div>
    </form>
    <?php
}
else {
    echo '<div class="alert alert-danger">Invalid role selected</div>';
}

$conn->close();
?>