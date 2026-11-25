<h1>Register</h1>
<p>Register with your phone number. Students and parents can self-register. The first teacher becomes Super Admin; after that, only an admin can add teachers.</p>
<form method="post" action="/register">
  <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
  <label>Full name <input name="name" required></label>
  <label>Phone number <input name="phone" type="tel" required placeholder="01XXXXXXXXX (11 digits)"></label>
  <label>Password (4+ characters) <input name="password" type="password" required minlength="4"></label>
  <label>Role
    <select name="role">
      <option value="student">Student</option>
      <option value="parent">Parent</option>
      <option value="teacher">Teacher / Admin</option>
    </select>
  </label>
  <label>Class level (students) <input name="class_level" placeholder="e.g. 9-10"></label>
  <label>Linked student phone (parents only) <input name="linked_student_phone" type="tel" placeholder="Your child's phone number"></label>
  <button class="btn" type="submit">Register</button>
</form>
<p>Already registered? <a href="/login">Login</a></p>
