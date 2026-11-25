<div class="auth-card">
  <h1>Login</h1>
  <p>Log in with your phone number — student, parent, or teacher.</p>
  <form method="post" action="/login">
    <input type="hidden" name="csrf_token" value="<?= e($csrf_token) ?>">
    <label for="phone">Phone number</label>
    <input id="phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" required placeholder="01XXXXXXXXX">
    <span class="field-hint" id="phone-hint">Your 11-digit phone number.</span>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required>
    <label for="role">Log in as — কী হিসেবে লগইন করছেন</label>
    <select id="role" name="role" required>
      <option value="student">Student</option>
      <option value="parent">Parent</option>
      <option value="teacher">Teacher / Admin</option>
    </select>
    <span class="field-hint">Choose the role you registered with.</span>
    <div class="form-actions"><button class="btn" type="submit">Login</button></div>
  </form>
  <p>No account yet? <a href="/register">Register</a></p>
  <p><small>Too many wrong attempts locks login for 15 minutes (5 attempts).</small></p>
</div>
