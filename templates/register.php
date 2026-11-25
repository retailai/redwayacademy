<div class="auth-card">
  <h1>Register</h1>
  <p>Register with your phone number. Students and parents can self-register. The first teacher becomes Super Admin; after that, only an admin can add teachers.</p>
  <form method="post" action="/register">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <label for="name">Full name</label>
    <input id="name" name="name" autocomplete="name" required>
    <label for="phone">Phone number</label>
    <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="01XXXXXXXXX (11 digits)">
    <span class="field-hint">11 digits, starting with 01.</span>
    <label for="password">Password (4+ characters)</label>
    <input id="password" name="password" type="password" autocomplete="new-password" required minlength="4">
    <label for="role">Role</label>
    <select id="role" name="role">
      <option value="student">Student</option>
      <option value="parent">Parent</option>
      <option value="teacher">Teacher / Admin</option>
    </select>
    <label for="class_level">Class level (students)</label>
    <input id="class_level" name="class_level" placeholder="e.g. 9-10">
    <label for="linked_student_phone">Linked student phone (parents only)</label>
    <input id="linked_student_phone" name="linked_student_phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="Your child's phone number">
    <div class="form-actions"><button class="btn" type="submit">Register</button></div>
  </form>
  <p>Already registered? <a href="/login">Login</a></p>
</div>
